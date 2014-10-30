<?php

namespace Msnre\Parser;

use Symfony\Component\DomCrawler\Crawler;
use SleepingOwl\Apist\Apist;

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
        $text = trim($text);
        return $text;
    }

    /**
     * @param string
     * @return string
     */
    protected function trimQuotes($text) {
        $text = preg_replace('/^("|&quot;)(.+)("|&quot;)$/', '$2', $text);
        return $text;
    }

    /**
     * @param mixed
     * @param string
     */
    protected function recursiveAlarmIssue($value, $key = null, $source = null) {
        if (!self::DEBUG) {
            return;
        }
        if (is_array($value)) {
            foreach ($value as $currentKey => $o) {
                $newKey = $key . ' ' . $currentKey;
                $this->recursiveAlarmIssue($o, $newKey, $source ?: $value);
            }
            return;
        }

        if (empty($value)) {
            $this->alarmIssue('empty ' . $key, $source);
        }

        if (preg_match('/\n/', $value)) {
            $this->alarmIssue('break line ' . $key, $value, $source);
        }

        if (preg_match('/[()\[\]]/', $value)) {
            $this->alarmIssue('parenthis ' . $key, $value, $source);
        }

//        if (preg_match('/name/', $key) AND preg_match('/[^A-Za-z,.:-!?]/', $value)) {
//            $this->alarmIssue('comm! ' . $key, $value, $source);
//        }

        if (preg_match('/name/', $key) AND strlen($value) < 3) {
            $this->alarmIssue('too short ' . $key, $value, $source);
        }
    }

    /**
     * @param string
     * @param mixed
     * @param mixed
     */
    protected function alarmIssue($message, $a, $b = null) {
        if (!self::DEBUG) {
            return;
        }
        echo '<h1>' . $message . ' </h1>';
        var_dump($a);
        if ($b) {
            var_dump($b);
        }
    }

}

