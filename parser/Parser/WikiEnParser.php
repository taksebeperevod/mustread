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

                $result[] = $book;
            }
        }

        return $result;
    }
}

