<?php

namespace Msnre\Parser;

use Symfony\Component\DomCrawler\Crawler;
use SleepingOwl\Apist\Apist;

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
                                $en = array();

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

