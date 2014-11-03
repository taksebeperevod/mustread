<?php

namespace Msnre\Parser\Parser;

use Symfony\Component\DomCrawler\Crawler;
use SleepingOwl\Apist\Apist;

use Msnre\Parser\Helper\Alarm;

/**
 * @author Sergey Bondar
 */
abstract class AbstractParser extends Apist
{
    use Alarm;

    /**
     * @const
     */
    const DEBUG = true;

    /**
     * @param array
     * @constructor
     */
    public function __construct($options = []) {
        $this->setSuppressExceptions(false);
        parent::__construct($options);
    }

    /**
     * @param string
     * @return string
     */
    protected function cleanParenthis($text) {
        $text = preg_replace('/\([^)]+\)/', '', $text);
        $text = trim($text);
        return $text;
    }

    /**
     * @param string
     * @return string
     */
    protected function extractParenthis($text) {
        preg_match('/\(([^)]+)\)/', $text, $match);
        return trim($match[1]);
    }

    /**
     * @param string
     * @return string
     */
    protected function stripTrim($text) {
        $text = strip_tags($text);
        $text = preg_replace('/\[\d+\]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = str_replace('’', '\'', $text);
        $text = trim($text);
        return $text;
    }

    /**
     * @param string
     * @return string
     */
    protected function trimQuotes($text) {
        $text = preg_replace('/^("|&quot;|«)(.+)("|&quot;|»)$/', '$2', $text);
        return $text;
    }

    /**
     * @param array
     * @param array
     * @param string
     * @return array
     */
    protected function mapCategoryUrls($categories, $cats, $postfix) {
        $urls = [];
        foreach ($categories as $key => $value) {
            $value = (object) $value;
            if (!isset($cats[$value->$postfix])) {
                continue;
            }
            $urls[$key] = $cats[$value->$postfix];
        }

        return $urls;
    }

}

