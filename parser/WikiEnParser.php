<?php

namespace Msnre\Parser;

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
        //TODO Best All-Time Series	1966	Series of works
        $cats = [
            'Novel' => '/wiki/Hugo_Award_for_Best_Novel',
            'Novella' => '/wiki/Hugo_Award_for_Best_Novella',
            'Novellette' => '/wiki/Hugo_Award_for_Best_Novelette',
            'Short story' => '/wiki/Hugo_Award_for_Best_Short_Story'
        ];

        $urls = [];
        foreach ($categories as $key => $value) {
            if (!isset($cats[$value['en']])) {
                continue;
            }
            $urls[$key] = $cats[$value['en']];
        }

        $hugo = array();

        foreach ($urls as $category => $url) {
            $hugo = array_merge($hugo, $this->getHugoCategory($url, $category));
        }

        return $hugo;
    }



    /**
     * @param string
     * @param int
     * @return array
     */
    public function getHugoCategory($url, $category)
    {
        $parsed = $this->get($url, [
            'hugo' => Apist::filter('.wikitable')->each([
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
                            $name = $nameEl->children()->eq($nameEl->children()->count() - 1)->text();
                        }
                        if(preg_match('/Mule/', $name)) {
                            $name = str_replace('Mule !', '', $name);
                        }
                        $name = $this->trimQuotes($this->stripTrim($name));

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

        return $this->prepareHugo($parsed);
    }

    /**
     * @param array
     * @return array
     */
    protected function prepareHugo($hugo) {
        $result = [];
        foreach($hugo['hugo'] as $h) {
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

