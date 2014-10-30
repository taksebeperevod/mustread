<?php

namespace Msnre\Parser;

/**
 * @author Sergey Bondar
 */
class ParserQueue
{
    use Alarm;

    /**
     * @var Authors
     */
    protected $authors;
    /**
     * @var WikiRuParser
     */
    protected $ruWiki;
    /**
     * @var WikiEnParser
     */
    protected $enWiki;

    /**
     * @constructor
     */
    public function __construct() {
        $this->authors = new Authors();
        $this->ruWiki = new WikiRuParser();
        $this->enWiki = new WikiEnParser();
    }

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
        $aName = $this->prepareBookTitle($a['name']);
        $bName = $this->prepareBookTitle($b['name']);

        if( $aName == $bName ) {
            return true;
        }

        $aAuthor = $this->prepareBookTitle($a['author']);
        $bAuthor = $this->prepareBookTitle($b['author']);
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
            //echo $aFirst . " " . $bFirst . "! " . $aName . "({$a['author']})" . "=" . $bName . "({$b['author']})" . "<br><br>";
        }

        if ($aLast == $bLast) {
            return true;
            //echo $aLast . " " . $bLast . "! " . $aName  . "({$a['author']})" . "=" . $bName . "({$b['author']})"  . "<br><br>";
        }
        return false;
    }

    /**
     * @return array
     */
    public function getHugo()
    {
        $hugo = array();

        $ruHugo = $this->ruWiki->getHugo();

        $enHugo = $this->enWiki->getHugo($ruHugo['categories']);

        foreach ($enHugo as $enKey => $en) {
            foreach ($ruHugo['books'] as $ruKey => $ru) {
                if ($ru['year'] != $en['year']) {
                    continue;
                }

                if($this->isSameBook($ru['en'], $en)) {
                    $book = [
                        'isWinner' => $en['isWinner'],
                        'year' => intval($en['year']),
                        //RU wiki is SUCKS
                        'category' => intval($en['category']),
                        'ru' => $ru['ru'],
                        'en' => [
                            'name' => $en['name'],
                            'author' => $en['author'],
                            'publisher' => $en['publisher']
                        ]
                    ];

                    $hugo[] = $book;

                    unset($enHugo[$enKey]);
                    unset($ruHugo['books'][$ruKey]);
                }
            }
        }

        $this->authors->fixAuthors($hugo);
        $this->authors->collectAuthors($hugo);

        foreach($enHugo as $en) {
            $hugo[] = [
                'isWinner' => $en['isWinner'],
                'year' => intval($en['year']),
                //RU wiki is SUCKS - 2014 & 1939
                'category' => intval($en['category']),
                'ru' => [
                    'name' => null,
                    'author' => $this->authors->getRussianAuthor($en['author']),
                ],
                'en' => [
                    'name' => $en['name'],
                    'author' => $en['author']
                ]
            ];
        };

        return [
            'books' => $hugo,
            'categories' => $ruHugo['categories']
        ];
    }

}

