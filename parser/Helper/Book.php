<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
trait Book
{
    /**
     * @param string
     * @return string
     */
    protected static function prepare($title) {
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
    public static function isSameBook($a, $b) {
        $aName = self::prepare($a->name);
        $bName = self::prepare($b->name);

        if( $aName == $bName ) {
            return true;
        }

        $aAuthor = self::prepare($a->author);
        $bAuthor = self::prepare($b->author);
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

