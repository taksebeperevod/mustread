<?php

namespace Msnre\Parser;

use Msnre\Parser\Helper\Cache;
use Msnre\Parser\Helper\Authors;
use Msnre\Parser\Helper\Alarm;
use Msnre\Parser\Helper\Category;
use Msnre\Parser\Helper\Book;

use Msnre\Parser\Parser;

/**
 * @author Sergey Bondar
 */
class Award
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
     * @param Authors $authors
     * @constructor
     */
    public function __construct(Authors $authors) {
        $this->authors = $authors;
        $this->ruWiki = new Parser\WikiRuParser();
        $this->enWiki = new Parser\WikiEnParser();
    }

    /**
     * @return array
     */
    public function getLocus()
    {
        $categories = Category::getCategories();

        $ruWiki = $this->ruWiki;
        $cache = new Cache('locusRu', function() use ($ruWiki, $categories) {
                return $ruWiki->getLocus($categories);
            });
        $ruBooks = $cache->getData();

        $enWiki = $this->enWiki;
        $cache = new Cache('locusEn', function() use ($enWiki, $categories) {
                return $enWiki->getLocus($categories);
            });
        $enBooks = $cache->getData();

        $this->authors->collectAuthorsByEnTitleAndPopulate($ruBooks, $enBooks);

        $this->authors->fixAuthors($ruBooks);
        $this->authors->collectAuthors($ruBooks);

        foreach($ruBooks as $ruKey => $ru) {
            if (!$ru->en->author) {
                $ru->en->author = $this->authors->getEnglishAuthor($ru->ru->author);
            }
        }

        $books = [];
        foreach($enBooks as $enKey => $en) {
            if($en->category == Category::ID_NOVEL) {
                continue;
            }
            if (!$en->author) {
                continue;
            }

            $books[] = (object) [
                'year' => $en->year,
                'isWinner' => $en->isWinner,
                'nomination' => $en->category,
                'genre' => $en->genre,
                'ru' => (object) [
                    'author' => $this->authors->getRussianAuthor($en->author),
                    'name' => null
                ],
                'en' => (object) [
                    'author' => $en->author,
                    'name' => $en->name
                ]
            ];
            unset($enBooks[$enKey]);
        }

        foreach($ruBooks as $ruKey => $ru) {
            if ($ru->ru->name) {
                continue;
            }

            foreach($enBooks as $enKey => $en) {
                if($en->year != $ru->year OR $en->genre != $ru->genre) {
                    continue;
                }
                if ($en->name == $ru->en->name) {
                    $books[] = (object) [
                        'year' => $ru->year,
                        'isWinner' => $en->isWinner,
                        'nomination' => $ru->category,
                        'genre' => $ru->genre,
                        'ru' => (object) [
                            'author' => $ru->ru->author,
                            'name' => $ru->ru->name
                        ],
                        'en' => (object) [
                            'author' => $en->author,
                            'name' => $en->name
                        ]
                    ];
                    unset($ruBooks[$ruKey]);
                    unset($enBooks[$enKey]);
                    continue(2);
                }
            }
        }

        foreach($ruBooks as $ruKey => $ru) {
            if (!$ru->en->name && $ru->isWinner) {
                continue;
            }
            $books[] = (object) [
                'year' => $ru->year,
                'isWinner' => $ru->isWinner,
                'nomination' => $ru->category,
                'genre' => $ru->genre,
                'ru' => (object) [
                    'author' => $ru->ru->author,
                    'name' => $ru->ru->name
                ],
                'en' => (object) [
                    'author' => $ru->en->author,
                    'name' => $ru->en->name
                ]
            ];
            unset($ruBooks[$ruKey]);
        }

        foreach($ruBooks as $ruKey => $ru) {
            foreach($enBooks as $enKey => $en) {
                if($en->year != $ru->year OR $en->genre != $ru->genre) {
                    continue;
                }

                $books[] = (object) [
                    'year' => $ru->year,
                    'isWinner' => $en->isWinner,
                    'nomination' => $ru->category,
                    'genre' => $ru->genre,
                    'ru' => (object) [
                        'author' => $ru->ru->author,
                        'name' => $ru->ru->name
                    ],
                    'en' => (object) [
                        'author' => $en->author,
                        'name' => $en->name
                    ]
                ];
                unset($ruBooks[$ruKey]);
                unset($enBooks[$enKey]);
                continue(2);
            }
        }


        return (object) [
            'title' => (object) [
                'ru' => 'Локус',
                'en' => 'Locus Award'
            ],
            'founder' => 'Locus: The magazine of the science fiction & fantasy field',
            'site' => 'http://www.locusmag.com/',
            'link' => (object) [
                'ru' => 'http://ru.wikipedia.org/wiki/Локус_(премия)',
                'en' => 'http://en.wikipedia.org/wiki/Locus_Award'
            ],
            'books' => $books,
            'categories' => Category::getClarkeCategories()
        ];
    }

    /**
     * @return array
     */
    public function getClarke()
    {
        $categories = Category::getCategories();

        $ruWiki = $this->ruWiki;
        $cache = new Cache('clarkeRu', function() use ($ruWiki, $categories) {
                return $ruWiki->getClarke($categories);
            });
        $ruBooks = $cache->getData();

        $enWiki = $this->enWiki;
        $cache = new Cache('clarkeEn', function() use ($enWiki, $categories) {
                return $enWiki->getClarke($categories);
            });
        $enBooks = $cache->getData();

        $this->authors->collectAuthorsByEnTitleAndPopulate($ruBooks, $enBooks);

        //TODO merge
        $books = [];

        $this->authors->fixAuthors($books);
        $this->authors->collectAuthors($books);

        foreach($ruBooks as $ruKey => $ru) {
            if (!$ru->en->author) {
                $ru->en->author = $this->authors->getEnglishAuthor($ru->ru->author);
            }
        }

        foreach($ruBooks as $ruKey => $ru) {
            if ($ru->ru->name) {
                continue;
            }

            foreach($enBooks as $enKey => $en) {
                if($en->year != $ru->year OR $en->category != $ru->category) {
                    continue;
                }
                if ($en->name == $ru->en->name) {
                    $books[] = (object) [
                        'year' => $ru->year,
                        'isWinner' => $en->isWinner,
                        'nomination' => $ru->category,
                        'ru' => (object) [
                            'author' => $ru->ru->author,
                            'name' => $ru->ru->name
                        ],
                        'en' => (object) [
                            'author' => $en->author,
                            'name' => $en->name
                        ]
                    ];
                    unset($ruBooks[$ruKey]);
                    unset($enBooks[$enKey]);
                    continue(2);
                }
            }
        }

        foreach($ruBooks as $ruKey => $ru) {
            foreach($enBooks as $enKey => $en) {
                if($en->year != $ru->year OR $en->category != $ru->category) {
                    continue;
                }
                $en->author = $this->authors->removeSecondNameEn($en->author);
                if ($en->author == $ru->en->author) {
                    $books[] = (object) [
                        'year' => $ru->year,
                        'isWinner' => $en->isWinner,
                        'nomination' => $ru->category,
                        'ru' => (object) [
                            'author' => $ru->ru->author,
                            'name' => $ru->ru->name
                        ],
                        'en' => (object) [
                            'author' => $en->author,
                            'name' => $en->name
                        ]
                    ];
                    unset($ruBooks[$ruKey]);
                    unset($enBooks[$enKey]);
                    continue(2);
                }
            }
        }
        foreach($enBooks as $enKey => $en) {
            $books[] = (object) [
                'year' => $en->year,
                'isWinner' => $en->isWinner,
                'nomination' => $en->category,
                'ru' => (object) [
                    'author' => $this->authors->getRussianAuthor($en->author),
                    'name' => null
                ],
                'en' => (object) [
                    'author' => $en->author,
                    'name' => $en->name
                ]
            ];
            unset($enBooks[$enKey]);
        }

//        foreach($books as $ru) {
//            echo '!' . $ru->year . ' ';
//            echo $ru->ru->author . "-" . $ru->ru->name . " ~ " . $ru->en->author . "-" . $ru->en->name;
//            echo '<br>';
//        }
//        echo '<br><br><br><br>==============<br><br><br><br>';
//        foreach($ruBooks as $ru) {
//            echo '!' . $ru->year . ' ';
//            echo $ru->ru->author . "-" . $ru->ru->name . " ~ " . $ru->en->author . "-" . $ru->en->name;
////            var_dump($ru->isWinner);
//            echo '<br>';
//        }
//        echo '<br><br><br><br>==============<br><br><br><br>';
//        foreach($enBooks as $en) {
//            echo '!' . $en->year . ' ';
//            echo $en->author . "-" . $en->name;
////            var_dump($ru->isWinner);
//            echo '<br>';
//        }
//        echo '<br><br><br><br>==============<br><br><br><br>';
//
////        die();


        return (object) [
            'title' => (object) [
                'ru' => 'Премия Артура Кларка',
                'en' => 'Arthur C. Clarke Award'
            ],
            'founder' => 'British Science Fiction Association',
            'site' => 'http://www.clarkeaward.com/',
            'link' => (object) [
                'ru' => 'http://ru.wikipedia.org/wiki/Список_лауреатов_премии_Артура_Кларка',
                'en' => 'http://en.wikipedia.org/wiki/Arthur_C._Clarke_Award'
            ],
            'books' => $books,
            'categories' => Category::getClarkeCategories()
        ];
    }

    /**
     * @return array
     */
    public function getNebula()
    {
        $categories = Category::getCategories();

        $ruWiki = $this->ruWiki;
        $cache = new Cache('nebulaRu', function() use ($ruWiki, $categories) {
                return $ruWiki->getNebula($categories);
            });
        $ruBooks = $cache->getData();

        $enWiki = $this->enWiki;
        $cache = new Cache('nebulaEn', function() use ($enWiki, $categories) {
                return $enWiki->getNebula($categories);
            });
        $enBooks = $cache->getData();

        $this->authors->collectAuthorsByEnTitleAndPopulate($ruBooks, $enBooks);

        //TODO merge
        $books = $ruBooks;

        return (object) [
            'title' => (object) [
                'ru' => 'Небьюла',
                'en' => 'Nebula Award'
            ],
            'founder' => 'Science Fiction and Fantasy Writers of America',
            'site' => 'http://www.sfwa.org/nebula-awards/',
            'link' => (object) [
                'ru' => 'http://ru.wikipedia.org/wiki/%D0%9D%D0%B5%D0%B1%D1%8C%D1%8E%D0%BB%D0%B0',
                'en' => 'http://en.wikipedia.org/wiki/Nebula_Award'
            ],
            'books' => $books,
            'categories' => $categories
        ];
    }

    /**
     * @return array
     */
    public function getHugo()
    {
        $self = $this;
        $categories = Category::getCategories();

        $cache = new Cache('hugoRu', function() use ($self, $categories) {
                return $self->ruWiki->getHugo($categories);
            });
        $ruBooks = $cache->getData();

        $cache = new Cache('hugoEn', function() use ($self, $categories) {
                return $self->enWiki->getHugo($categories);
            });
        $enBooks = $cache->getData();

        $books = $this->mergeBooksByTitle($ruBooks, $enBooks);

        return (object) [
            'title' => (object) [
                'ru' => 'Хьюго',
                'en' => 'Hugo Award'
            ],
            'founder' => 'World Science Fiction Society',
            'site' => 'http://www.thehugoawards.org/',
            'link' => (object) [
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

                if(Book::isSameBook($ru->en, $en)) {
                    if (!trim($en->name)) {
                        var_dump($ru);
                    }
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

}

