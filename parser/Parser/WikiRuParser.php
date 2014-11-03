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
    public function getLocus($categories)
    {
        $cats = Category::getLocusRuCategories();

        $books = [];
        foreach ($cats as $category => $sets) {
            foreach ($sets as $key => $urls) {
                foreach ($urls as $url) {
                    if ($key == 'pic') {
                        $parsed = $this->parseTableWithPictures($url, $category, ['offset' => 2, 'yearColumns' => 4]);
                        $books = array_merge($books, $parsed);
                    } else {
                        $parsed = $this->parseLocusTables($url, $category);
                        $books = array_merge($books, $parsed);
                    }
                }
            }
        }

        return $books;
    }

    /**
     * @param string
     * @param int
     * @return array
     */
    public function parseLocusTables($url, $category)
    {
        $year = null;
        $parsed = $this->get($url, [
            'award' => Apist::filter('.wikitable')->each([
                    'books' => Apist::filter('tr')->each(function (Crawler $node, $i) use ($category, $year) {
                            static $headType;
                            $head = $node->children()->eq(1)->text();
                            if ($head == 'Лауреат') {
                                $headType = 1;
                                return null;
                            } else if ($head == 'Роман') {
                                $headType = 2;
                                return null;
                            }

                            $enAuthor = null;
                            $enName = null;
                            $ruName = null;
                            $ruAuthor = null;

                            if ($headType == 1) {
                                $year = $node->children()->eq(0)->text();

                                $text = $node->children()->eq(1)->text();
                                $text = explode(', ', $text);

                                $name = $text[0];
                                $author = $text[1];
                            } else {
                                $offset = 0;
                                if ($node->children()->count() == 3) {
                                    $year = $node->children()->eq(0)->text();
                                    $offset = 1;
                                }

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

                            if (!preg_match('/[А-Яа-я]/u', $name) AND preg_match('/[A-Za-z]/', $name)) {
                                $enName = $name;
                                $ruAuthor = null;
                            } else {
                                $ruName = $name;
                            }
                            if ($ruName AND !preg_match('/[А-Яа-я]/u', $ruName) AND !preg_match('/[A-Za-z]/', $ruName)) {
                                $enName = $ruName;
                            }

                            if (!preg_match('/[А-Яа-я]/u', $author) AND preg_match('/[A-Za-z]/', $author)) {
                                $enAuthor = $author;
                                $ruAuthor = null;
                            } else {
                                $ruAuthor = $author;
                            }

                            return [
                                'ru' => [
                                    'author' => $ruAuthor,
                                    'name' => $ruName,
                                ],
                                'en' => [
                                    'author' => $enAuthor,
                                    'name' => $enName
                                ],
                                'year' => $year,
                                'category' => $category,
                                'isWinner' => true
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
            $value['ru'] = (object) $value['ru'];
            $value['en'] = (object) $value['en'];
            $parsed[$key] = (object) $value;
        }

        return $parsed;
    }

    /**
     * @param array
     * @return array
     */
    public function getClarke($categories)
    {
        $cats = Category::getClarkeRuCategories();

        $urls = $this->mapCategoryUrls($categories, $cats, 'ru');
        $books = [];

        foreach ($urls as $category => $url) {
            //RU Wiki is sucks
            $parsed = $this->parseTableWithPictures($url, $category, ['offset' => 3, 'yearColumns' => 6]);
            $books = array_merge($books, $parsed);
        }

        return $books;
    }

    /**
     * @param array
     * @return array
     */
    public function getNebula($categories)
    {
        $cats = Category::getNebulaRuCategories();

        $urls = $this->mapCategoryUrls($categories, $cats, 'ru');
        $books = [];

        foreach ($urls as $category => $url) {
            //RU Wiki is sucks
            if ($category == Category::ID_NOVEL) {
                $offsetRule = 2;
                $yearColumns = 4;
            } else {
                $offsetRule = 4;
                $yearColumns = 6;
            }

            $parsed = $this->parseTableWithPictures($url, $category, ['offset' => $offsetRule, 'yearColumns' => $yearColumns]);
            $books = array_merge($books, $parsed);
        }

        return $books;
    }

    /**
     * @param string
     * @param int
     * @param array
     * @return array
     */
    protected function parseTableWithPictures($url, $category, $specialRules) {

        $parsed = $this->get($url, [
            'award' => Apist::filter('.wikitable')->each([
                'books' => Apist::filter('tr')->each(function (Crawler $node, $i) use ($category, $specialRules) {
                        $td = $node->children();

                        $offset = ($td->count() == $specialRules['offset']) ? 0 : 2;
                        $yearColumns = $specialRules['yearColumns'];

                        $ruAuthor = $td->eq($offset);
                        $ruName = $td->eq($offset + 1);

                        if (trim($ruAuthor->text()) == 'Победители и финалисты') {
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

                        //obsolete en/ru notes
                        if ($ruAuthor->children()->count() == 1) {
                            $ruAuthor = $ruAuthor->children()->eq(0);
                            if (!$ruAuthor->attr('href')) {
                                $ruAuthor = $ruAuthor->children()->eq(0);
                            }
                        }

                        $ruAuthor = $this->stripTrim($ruAuthor->html());
                        //no co-authors!
                        $ruAuthor = preg_replace('/,.+$/', '', $ruAuthor);
                        $ruAuthor = $this->trimQuotes($ruAuthor);

                        $enAuthor = null;
                        $enName = null;

                        if (!preg_match('/[А-Яа-я]/u', $ruAuthor) ) {
                            $enAuthor = $ruAuthor;
                            $ruAuthor = null;
                        }

                        //obsolete en/ru notes
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
                        if ($ruName AND !preg_match('/[А-Яа-я]/u', $ruName) AND !preg_match('/[A-Za-z]/', $ruName)) {
                            $enName = $ruName;
                        }

                        return [
                            'ru' => [
                                'author' => $ruAuthor,
                                'name' => $ruName,
                            ],
                            'en' => [
                                'author' => $enAuthor,
                                'name' => $enName
                            ],
                            'year' => $year,
                            'category' => $category,
                            'isWinner' => $style && preg_match('/background/', $style)
                        ];
                })
            ])
        ]);

        $books = $this->prepareTableWithPictures($parsed);

        return $books;
    }

    /**
     * @param array
     * @return array
     */
    protected function prepareTableWithPictures($collection) {
        $result = [];
        foreach($collection['award'] as $h) {
            foreach ($h['books'] as $book) {
                if (!$book) {
                    continue;
                }

                $book['ru'] = (object) $book['ru'];
                $book['en'] = (object) $book['en'];
                $book = (object) $book;

                $result[] = $book;
            }
        }

        return $result;
    }
    
    /**
     * @param array
     * @return array
     */
    public function getHugo($categories)
    {
        $settings = Category::getHugoRuCategories();
        $categories = $settings['categories'];

        $parsed = $this->get($settings['url'], [
            'hugo' => Apist::filter('table.standard')->each([
                'genres' => Apist::filter('table.standard tr')->eq(0)->children()->each([
                        'name' => Apist::filter('*')->text()
                ]),
                //'years' => Apist::filter('table.standard tr')->each(Apist::filter('th')->text()),
                'awards' => Apist::filter('table.standard tr')->each([
                        'books' => Apist::filter('td')->each(function(Crawler $node, $i) use ($categories) {
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

                                $category = $categories[$i + 1];

                                $book = [
                                    'nomination' => $category,
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
                //TODO special description?
                'description' => $this->extractParenthis($genre)
            ];
        }

        foreach ($hugo['awards'] as $award) {
            foreach ($award['books'] as $rawBook) {
                if (!$rawBook) {
                    continue;
                }

                //skip short stories
                if (Category::isSkipped($rawBook['nomination'])) {
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
            'category' => Category::ID_NOVEL,
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

