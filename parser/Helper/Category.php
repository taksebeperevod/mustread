<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
class Category
{
    /** @const */
    const NOVEL = 1;
    /** @const */
    const NOVELLA = 2;
    /** @const */
    const NOVELLETTE = 3;
    /** @const */
    const SHORT_STORY = 4;

    /**
     * @var string
     */
    protected static $map = [
        self::NOVEL => ['en' => 'Novel', 'ru' => 'Роман'],
        self::NOVELLA => ['en' => 'Novella', 'ru' => 'Повесть'],
        self::NOVELLETTE => ['en' => 'Novellette', 'ru' => 'Короткая повесть'],
        //self::SHORT_STORY => ['en' => 'Short story', 'ru' => 'Рассказ']
    ];

    /**
     * @return string
     */
    public static function getCategories() {
        return self::$map;
    }
}

