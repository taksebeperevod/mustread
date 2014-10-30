<?php

namespace Msnre\Parser;

/**
 * @author Sergey Bondar
 */
class MainParser
{
    use Alarm;

    /**
     * @var RuWikiParser
     */
    protected $ruWiki;
    /**
     * @var EnWikiParser
     */
    protected $enWiki;

    /**
     * @constructor
     */
    public function __construct() {
        $this->ruWiki = new RuWikiParser();
        $this->enWiki = new EnWikiParser();
    }

    /**
     * @return array
     */
    public function getHugo()
    {
        $hugo = array();
        $ruHugo = $this->ruWiki->getHugo();

        $enHugo = $this->enWiki->getHugo($ruHugo['categories']);


        foreach ($enHugo as $en) {
            foreach ($ruHugo['books'] as $ru) {
                if ($ru['year'] == $en['year'] AND $ru['category'] == $en['category']) {
                    var_dump($en);
                    var_dump($ru);

                    die();

                }
            }
        }

        return [
            'books' => $hugo,
            'categories' => $ruHugo['categories']
        ];
    }

}

