<?php

namespace Msnre\Parser\Parser;

use Symfony\Component\DomCrawler\Crawler;
use SleepingOwl\Apist\Apist;

use Msnre\Parser\Helper\Category;

/**
 * @author Sergey Bondar
 */
class WikiRuParser extends AbstractParser
{
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return 'http://ru.wikipedia.org';
    }

    /**
     * @param array
     * @return array
     */
    public function getNebula($categories)
    {
        $cats = [
            'Роман' => '/wiki/Премия_«Небьюла»_за_лучший_роман',
            'Повесть' => '/wiki/Премия_«Небьюла»_за_лучшую_повесть',
            'Короткая повесть' => '/wiki/Премия_«Небьюла»_за_лучшую_короткую_повесть',
            //'Рассказ' => '/wiki/Премия_«Небьюла»_за_лучший_рассказ'
        ];

        $urls = $this->mapCategoryUrls($categories, $cats, 'ru');
        $books = [];

        foreach ($urls as $category => $url) {
            $parsed = $this->getNebulaCategory($url, $category);
            $books = array_merge($books, $parsed);
        }

        return $books;
    }

    /**
     * @param string
     * @param int
     * @return array
     */
    protected function getNebulaCategory($url, $category) {

        $parsed = $this->get($url, [
            'award' => Apist::filter('.wikitable')->each([
                'books' => Apist::filter('tr')->each(function (Crawler $node, $i) use ($category) {
                        $td = $node->children();
                        if ($category == 1) {
                            $offset = ($td->count() == 2) ? 0 : 2;
                            $yearColumns = 4;
                        } else {
                            $offset = ($td->count() == 4) ? 0: 2;
                            $yearColumns = 6;
                        }

                        $author = $td->eq($offset);
                        $ruName = $td->eq($offset + 1);

                        if (trim($author->text()) == 'Победители и финалисты') {
                            return null;
                        }

                        $currentKey = 0;
                        $possibleYears = $node->previousAll();
                        $yearTd = $td;
                        while ($yearTd->count() != $yearColumns) {
                            $yearTd = $possibleYears->eq($currentKey)->children();
                            $currentKey++;
                        }

                        $year = $yearTd->children()->eq(0)->text();
                        $year = $this->stripTrim($year);

                        $style = $ruName->attr('style');

                        $author = $this->stripTrim($author->html());
                        //no co-authors!
                        $author = preg_replace('/,.+$/', '', $author);
                        $author = $this->trimQuotes($author);

                        $enName = null;

                        if ($ruName->children()->count()) {
                            $ruName = $ruName->children()->eq(0);
                            if ($ruName->children()->count()) {
                                $ruName = $ruName->children()->eq(0);
                            }
                        }

                        $ruName = $this->stripTrim($ruName->html());
                        $ruName = str_replace('»/«', '', $ruName);
                        $ruName = $this->trimQuotes($ruName);
                        if (!preg_match('/[А-Яа-я]/u', $ruName) AND preg_match('/[A-Za-z]/', $ruName)) {
                            $enName = $ruName;
                            $ruName = null;
                        }
                        if (preg_match('/^[A-Za-z]+$/', $ruName)) {
                            $enName = $ruName;
                        }

                        return [
                            'ru' => [
                                'author' => $author,
                                'name' => $ruName,
                            ],
                            'en' => [
                                'author' => null,
                                'name' => $enName
                            ],
                            'year' => $year,
                            'category' => $category,
                            'isWinner' => $style && preg_match('/background/', $style)
                        ];
                })
            ])
        ]);

        $books = $this->prepareNebula($parsed);

        return $books;
    }

    /**
     * @param array
     * @return array
     */
    protected function prepareNebula($collection) {
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
    
    /**
     * @return array
     */
    public function getHugo()
    {
        $parsed = $this->get('/wiki/%D0%A5%D1%8C%D1%8E%D0%B3%D0%BE', [
            'hugo' => Apist::filter('table.standard')->each([
                'genres' => Apist::filter('table.standard tr')->eq(0)->children()->each([
                        'name' => Apist::filter('*')->text()
                ]),
                //'years' => Apist::filter('table.standard tr')->each(Apist::filter('th')->text()),
                'awards' => Apist::filter('table.standard tr')->each([
                        'books' => Apist::filter('td')->each(function(Crawler $node, $i) {
                                //nomination genre
                                //var_dump($node->parents()->parents()->filter('tr')->eq(0)->filter('th')->eq($i)->text());die();
                                $possibleYears = $node->parents()->previousAll();
                                $currentKey = 0;
                                do {
                                    $year = $possibleYears->eq($currentKey)->text();
                                    $currentKey++;
                                } while (!preg_match('/^\d+/', $year));

                                $year = $this->stripTrim($this->cleanParenthis($year));

                                $row = $this->cleanParenthis( $node->html() );

                                $row = str_replace('Арлекин!', 'Арлекин!»', $row);
                                $row = str_replace('Frank Riley,', '', $row);

                                $row = explode("<br>", $row);
                                $en = [];

                                if (isset($row[2])) {
                                    if (isset($row[3]) && $row[3]) {
                                        $en = [$row[2], $row[3]];
                                    } else {
                                        $row[2] = str_replace(', Jr.', ' Jr.', $row[2]);
                                        $row[2] = str_replace('Delany, Samuel', 'Samuel Delany', $row[2]);
                                        $en = explode(",", $row[2], 2);
                                    }
                                }

                                $book = [
                                    'nomination' => $i + 1,
                                    'year' => $year,
                                    'ru' => [
                                        'author' => $this->stripTrim($row[0]),
                                        'name' => (isset($row[1]) AND $this->stripTrim($row[1]) != '\'') ? $this->stripTrim($row[1]) : null
                                    ],
                                    'en' => [
                                        'author' => isset($en[0]) ? $this->stripTrim($en[0] ) : null,
                                        'name' => isset($en[1]) ? $this->stripTrim($en[1]) : null,
                                    ]
                                ];

                                if (!$book['ru']['author'] && !$book['ru']['name']) {
                                    return null;
                                }

                                return $book;
                            })
                    ]
                ),
            ])
        ]);

        return $this->prepareHugo($parsed);
    }

    /**
     * @param array
     * @return array
     */
    protected function prepareHugo($hugo) {
        $result = [
            'categories' => [],
            'books' => []
        ];
        $hugo = $hugo['hugo'][0];

        foreach ($hugo['genres'] as $i => $genre) {
            $genre = $genre['name'];
            $vars = $this->cleanParenthis($genre);
            $vars = explode('/', $vars);

            $result['categories'][$i+1] = [
                'ru' => $this->stripTrim($vars[0]),
                'en' => $this->stripTrim($vars[1]),
                'description' => $this->extractParenthis($genre)
            ];
        }

        foreach ($hugo['awards'] as $award) {
            foreach ($award['books'] as $rawBook) {
                if (!$rawBook) {
                    continue;
                }

                //skip short stories
                if ($rawBook['nomination'] == Category::SHORT_STORY) {
                    continue;
                }

                //$this->recursiveAlarmIssue($rawBook);

                //RU wiki is sucks
                if ($rawBook['en']['name'] == 'They’d Rather Be Right') {
                    $rawBook['en']['name'] = 'The Forever Machine';
                }
                if ($rawBook['en']['name'] == '… And Call Me Conrad') {
                    $rawBook['en']['name'] = 'This Immortal';
                }


                $book = [
                    'category' => $rawBook['nomination'],
                    'year' => $rawBook['year'],
                    'ru' => $rawBook['ru'],
                    'en' => $rawBook['en']
                ];

                $result['books'][] = $book;
            }
        }

        //RU wiki is sucks
        $result['books'][] = [
            'category' => 1,
            'year' => 2010,
            'ru' => [
              'name' => 'Чайна Мьевилль',
              'author' => 'Город и город'
            ],
            'en' => [
                'name' => 'The City & the City',
                'author' => 'China Miéville'
            ]
        ];

        return $result;
    }
}

