<?php

namespace Msnre\Parser;

/**
 * @author Sergey Bondar
 */
trait Alarm
{

    /**
     * @const
     */
    protected $debug = true;


    /**
     * @param mixed $value
     * @param string $key
     * @param mixed $source
     */
    protected function recursiveAlarmIssue($value, $key = null, $source = null) {
        if (!$this->debug) {
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
     * @param string $message
     * @param mixed $a
     * @param mixed $b
     */
    protected function alarmIssue($message, $a, $b = null) {
        if (!$this->debug) {
            return;
        }
        echo '<h1>' . $message . ' </h1>';
        var_dump($a);
        if ($b) {
            var_dump($b);
        }
    }

}

