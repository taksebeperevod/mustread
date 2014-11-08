<?php

namespace Msnre\Parser\Parser;

use Msnre\Parser\Helper\Category;
use Symfony\Component\DomCrawler\Crawler;
use SleepingOwl\Apist\Apist;

/**
 * @author Sergey Bondar
 */
class WikiEnParser extends AbstractParser
{
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return 'https://en.wikipedia.org';
    }

    /**
     * @param array
     * @return array
     */
    public function getLocus($categories)
    {
        $categories = Category::getLocusEnCategories();

        $books = [];
        foreach ($categories as $category => $urls) {
            $urls = (array) $urls;
            foreach ($urls as $genre => $url) {
                $parsed = $this->parseLocusTables($url, $genre, $category);
                $books = array_merge($books, $parsed);
            }
        }

        return $books;
    }

    /**
     * @param string
     * @param int
     * @param int
     * @return array
     */
    protected function parseLocusTables($url, $genre, $category) {
        $headType = null;
        $year = null;

        $parsed = $this->get($url, [
                'award' => Apist::filter('.wikitable')->eq(0)->each([
                        'books' => Apist::filter('tr')->each(function (Crawler $node, $i) use ($category, $genre, $headType, $year) {
                                static $headType;
                                $head = $node->children()->eq(1)->text();
                                if ($head == 'Winner') {
                                    $headType = 1;
                                    return null;
                                } else if (preg_match('/Novel/', $head)) {
                                    $headType = 2;
                                    return null;
                                }

                                if ($headType == 1) {
                                    $offset = 0;

                                    $year = $node->children()->eq(0)->text();
                                    $year = $this->stripTrim($year);
                                } else if ($node->children()->count() !== 2) {
                                    $offset = 1;

                                    $year = $node->children()->eq(0)->text();
                                    $year = $this->stripTrim($year);
                                } else {
                                    $offset = 0;
                                }
                                $year = $this->cleanParenthis($year);
                                $year = $this->stripTrim($year);

                                if ($headType == 1) {
                                    $td = $node->children()->eq($offset + 1);
                                    $name = $td->children()->eq(0)->text();
                                    $author = $td->children()->eq(1)->text();
                                } else {
                                    $nameEl = $node->children()->eq($offset + 0);
                                    if($nameEl->children()->count()) {
                                        $name = $nameEl->children()->eq(0)->text();
                                    } else {
                                        $name = $nameEl->text();
                                    }
                                    $name = $this->trimQuotes($name);

                                    $authorEl = $node->children()->eq($offset + 1);
                                    if($authorEl->children()->count()) {
                                        $author = $authorEl->children()->eq(0)->text();
                                    } else {
                                        $author = $authorEl->text();
                                    }
                                }

                                return [
                                    'isWinner' => true,
                                    'year' => $year,
                                    'category' => $category,
                                    'genre' => $genre,
                                    'author' => $author,
                                    'name' => $name
                                ];
                            })
            ])
        ]);

        return $this->prepareLocus($parsed);
    }

    /**
     * @param array
     * @return array
     */
    protected function prepareLocus($parsed)
    {
        $parsed = $parsed['award'][0]['books'];
        unset($parsed[0]);

        foreach ($parsed as $key => $value) {
            $parsed[$key] = (object) $value;
        }

        return $parsed;
    }

    /**
     * @param array
     * @return array
     */
    public function getHugo($categories)
    {
        $cats = Category::getHugoEnCategories();

        $urls = $this->mapCategoryUrls($categories, $cats, 'en');
        return $this->parseWinnerTableCategories($urls);
    }

    /**
     * @param array
     * @return array
     */
    public function getNebula($categories)
    {
        $cats = Category::getNebulaEnCategories();

        $urls = $this->mapCategoryUrls($categories, $cats, 'en');
        return $this->parseWinnerTableCategories($urls);
    }

    /**
     * @param array
     * @return array
     */
    public function getClarke($categories)
    {
        $cats = Category::getClarkeEnCategories();

        $urls = $this->mapCategoryUrls($categories, $cats, 'en');
        return $this->parseWinnerTableCategories($urls);
    }

    /**
     * @param array
     * @return array
     */
    protected function parseWinnerTableCategories($urls) {
        $books = [];

        foreach ($urls as $category => $url) {
            $parsed = $this->parseWinnerTable($url, $category);
            $books = array_merge($books, $parsed);
        }

        return $books;
    }

    /**
     * @param string
     * @param int
     * @return array
     */
    public function parseWinnerTable($url, $category)
    {
        $parsed = $this->get($url, [
            'award' => Apist::filter('.wikitable')->each([
                'books' => Apist::filter('tr')->each(function (Crawler $node, $i) use ($category) {
                        $style = $node->attr('style');

                        $td = $node->children();
                        $offset = $td->count() - 5;

                        if (!$td->eq(0)->children()->count()) {
                            return null;
                        }

                        $nameEl = $td->eq(2 + $offset);
                        if(!$nameEl->children()->count()) {
                            $name = $nameEl->text();
                        } else {
                            $eqKey = $nameEl->children()->count() - 1;

                            //sometimes i>span+a, sometimes span+span->a
                            if ($nameEl->children()->eq($eqKey)->children()->count() > 1) {
                                $nameEl = $nameEl->children()->eq($eqKey);
                                $eqKey = $nameEl->children()->count() - 1;
                            }

                            $name = $nameEl->children()->eq($eqKey)->text();
                        }
                        if(preg_match('/Mule/', $name)) {
                            $name = str_replace('Mule !', '', $name);
                        }
                        $name = $this->trimQuotes($this->stripTrim($name));

                        if ($td->eq(1 + $offset)->text() == '(no award)+') {
                            return null;
                        }

                        return [
                            'isWinner' => $style && preg_match('/background/', $style),
                            'year' => $td->eq(0)->children()->eq(1)->text(),
                            'category' => $category,
                            'author' => $td->eq(1 + $offset)->children()->eq(1)->text(),
                            'name' => $name,
                            'publisher' => trim(preg_replace('/!.+/', '', $td->eq(3 + $offset)->text()))
                        ];
                    }
                )
            ])
        ]);

        return $this->prepare($parsed);
    }

    /**
     * @param array
     * @return array
     */
    protected function prepare($collection) {
        $result = [];
        foreach($collection['award'] as $h) {
            foreach ($h['books'] as $book) {
                if (!$book) {
                    continue;
                }

                $result[] = (object) $book;
            }
        }

        return $result;
    }
}

