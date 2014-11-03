<?php

namespace Msnre\Parser;

use Msnre\Parser\Helper\Cache;
use Msnre\Parser\Helper\Authors;
use Msnre\Parser\Helper\Alarm;

use Msnre\Parser\Parser;

/**
 * @author Sergey Bondar
 */
class ParserManager
{
    use Alarm;

    /**
     * @var Authors
     */
    protected $authors;
    /**
     * @var Parser\WikiRuParser
     */
    protected $ruWiki;
    /**
     * @var Parser\WikiEnParser
     */
    protected $enWiki;

    /**
     * @constructor
     */
    public function __construct() {
        $this->authors = new Authors();
        $this->ruWiki = new Parser\WikiRuParser();
        $this->enWiki = new Parser\WikiEnParser();
    }

    /**
     * @return mixed
     */
    public function getBooks() {
        $self = $this;
        $cache = new Cache('hugo', function() use ($self) {
                return $self->getHugo();
            });
        $hugo = $cache->get();

        $self = $this;
        $cache = new Cache('nebula', function() use ($self) {
                return $self->getNebula();
            });
        $nebula = $cache->get();

        return $nebula;
    }

    /**
     * @return array
     */
    public function getNebula()
    {
        $self = $this;

        //TODO Category
        $categories = [
            1 => ['en' => 'Novel', 'ru' => 'Роман'],
            2 => ['en' => 'Novella', 'ru' => 'Повесть'],
            3 => ['en' => 'Novellette', 'ru' => 'Короткая повесть'],
            4 => ['en' => 'Short story', 'ru' => 'Рассказ']
        ];

        $cache = new Cache('nebulaRu', function() use ($self, $categories) {
                return $self->ruWiki->getNebula($categories);
            });
        $ruBooks = $cache->get();

        $cache = new Cache('nebulaEn', function() use ($self, $categories) {
                return $self->enWiki->getNebula($categories);
            });
        $enBooks = $cache->get();

        return (object) [
            'link' => [
                'ru' => 'http://ru.wikipedia.org/wiki/%D0%9D%D0%B5%D0%B1%D1%8C%D1%8E%D0%BB%D0%B0',
                'en' => 'http://en.wikipedia.org/wiki/Nebula_Award'
            ],
            'books' => $ruBooks,
            'categories' => $categories
        ];
    }

    /**
     * @return array
     */
    public function getHugo()
    {
        $self = $this;

        $cache = new Cache('hugoRu', function() use ($self) {
                return $self->ruWiki->getHugo();
            });
        $ruBooks = $cache->get();

        $categories = $ruBooks->categories;
        $cache = new Cache('hugoEn', function() use ($self, $categories) {
                return $self->enWiki->getHugo($categories);
            });
        $enBooks = $cache->get();

        $books = $this->mergeBooksByTitle($ruBooks, $enBooks);

        return (object) [
            'link' => [
                'ru' => 'http://ru.wikipedia.org/wiki/%D0%A5%D1%8C%D1%8E%D0%B3%D0%BE',
                'en' => 'http://en.wikipedia.org/wiki/Hugo_Award'
            ],
            'books' => $books,
            'categories' => $ruBooks->categories
        ];
    }

    /**
     * @param array
     * @param array
     * @return array
     */
    public function mergeBooksByTitle($ruBooks, $enBooks)
    {
        $books = [];

        foreach ($enBooks as $enKey => $en) {
            foreach ($ruBooks->books as $ruKey => $ru) {
                if ($ru->year != $en->year) {
                    continue;
                }

                if($this->isSameBook($ru->en, $en)) {
                    $book = (object) [
                        'isWinner' => $en->isWinner,
                        'year' => intval($en->year),
                        //RU wiki is SUCKS
                        'category' => intval($en->category),
                        'ru' => (object) $ru->ru,
                        'en' => (object) [
                            'name' => $en->name,
                            'author' => $en->author,
                            'publisher' => $en->publisher
                        ]
                    ];

                    $books[] = $book;

                    unset($enBooks[$enKey]);
                    unset($ruBooks->books[$ruKey]);
                }
            }
        }

        $this->authors->fixAuthors($books);
        $this->authors->collectAuthors($books);

        foreach($enBooks as $en) {
            $books[] = (object) [
                'isWinner' => $en->isWinner,
                'year' => intval($en->year),
                //RU wiki is SUCKS - 2014 & 1939
                'category' => intval($en->category),
                'ru' => (object) [
                    'name' => null,
                    //FIXME move to whole books preparing
                    'author' => $this->authors->getRussianAuthor($en->author),
                ],
                'en' => (object) [
                    'name' => $en->name,
                    'author' => $en->author
                ]
            ];
        };

        return $books;
    }

    //TODO Books
    /**
     * @param string
     * @return string
     */
    protected function prepareBookTitle($title) {
        $title = str_replace('...', '', $title);
        $title = str_replace('«', '', $title);
        $title = str_replace('»', '', $title);
        $title = str_replace('’', '', $title);
        $title = str_replace('\'', '', $title);
        $title = str_replace('ö', 'e', $title);
        $title = str_replace('é', 'e', $title);
        $title = str_replace('&amp;', '&', $title);
        $title = preg_replace('/^A /', '', $title);
        $title = preg_replace('/^The /', '', $title);
        $title = preg_replace('/series$/', '', $title);
        $title = preg_replace('/^([^:]+):.+$/', '$1', $title);
        $title = preg_replace('/s$/', '', $title);
        $title = preg_replace('/[^A-Za-z0-9 ]/', '', $title);
        $title = strtolower($title);
        return trim($title);
    }

    /**
     * @param array
     * @param array
     * @return bool
     */
    protected function isSameBook($a, $b) {
        $aName = $this->prepareBookTitle($a->name);
        $bName = $this->prepareBookTitle($b->name);

        if( $aName == $bName ) {
            return true;
        }

        $aAuthor = $this->prepareBookTitle($a->author);
        $bAuthor = $this->prepareBookTitle($b->author);
        if ($aAuthor != $bAuthor) {
            return false;
        }

        $aParts = explode(' ', $aName);
        if (count($aParts) == 1) {
            return false;
        }
        $aFirst = $aParts[0];
        $aLast = $aParts[count($aParts) - 1];

        $bParts = explode(' ', $bName);
        $bFirst = $bParts[0];
        $bLast = $bParts[count($bParts) - 1];

        if ($aFirst == $bFirst) {
            return true;
            //echo $aFirst . " " . $bFirst . "! " . $aName . "({$a->author})" . "=" . $bName . "({$b->author})" . "<br><br>";
        }

        if ($aLast == $bLast) {
            return true;
            //echo $aLast . " " . $bLast . "! " . $aName  . "({$a->author})" . "=" . $bName . "({$b->author})"  . "<br><br>";
        }
        return false;
    }

}

