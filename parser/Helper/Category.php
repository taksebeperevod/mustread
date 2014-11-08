<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
class Category
{
    /** @const */
    const ID_NOVEL = 1;
    /** @const */
    const ID_NOVELLA = 2;
    /** @const */
    const ID_NOVELLETTE = 3;
    /** @const */
    const ID_SHORT_STORY = 4;

    /** @const */
    const RU_NOVEL = 'Роман';
    /** @const */
    const RU_NOVELLA = 'Повесть';
    /** @const */
    const RU_NOVELLETTE = 'Короткая повесть';
    /** @const */
    const RU_SHORT_STORY = 'Рассказ';

    /** @const */
    const EN_NOVEL = 'Novel';
    /** @const */
    const EN_NOVELLA = 'Novella';
    /** @const */
    const EN_NOVELLETTE = 'Novellette';
    /** @const */
    const EN_SHORT_STORY = 'Short story';

    /**
     * @var string
     */
    protected static $map = [
        self::ID_NOVEL => ['en' => self::EN_NOVEL, 'ru' => self::RU_NOVEL],
        self::ID_NOVELLA => ['en' => self::EN_NOVELLA, 'ru' => self::RU_NOVELLA],
        self::ID_NOVELLETTE => ['en' => self::EN_NOVELLETTE, 'ru' => self::RU_NOVELLETTE],
        //self::SHORT_STORY => ['en' => self::EN_SHORT_STORY, 'ru' => self::RU_SHORT_STORY]
    ];

    /**
     * @return string
     */
    public static function getCategories() {
        return self::$map;
    }

    /**
     * @param int
     * @return boolean
     */
    public static function isSkipped($id) {
        return $id == self::ID_SHORT_STORY;
    }

    /**
     * @return array
     */
    public static function getLocusEnCategories() {
        return [
            self::ID_NOVEL => [
                'best' => '/wiki/Locus_Award_for_Best_Novel',
                'sci-fi' => '/wiki/Locus_Award_for_Best_Science_Fiction_Novel',
                'fantasy' => '/wiki/Locus_Award_for_Best_Fantasy_Novel',
                'debut' => '/wiki/Locus_Award_for_Best_First_Novel',
            ],
            self::ID_NOVELLA => '/wiki/Locus_Award_for_Best_Novella',
            self::ID_NOVELLETTE => '/wiki/Locus_Award_for_Best_Novelette',
            //self::ID_SHORT_STORY => '/wiki/Locus_Award_for_Best_Short_Story'
        ];
    }

    /**
     * @return array
     */
    public static function getLocusRuCategories() {
        return [
            self::ID_NOVEL => [
                'pic' => [
                    'sci-fi' => '/wiki/%D0%9F%D1%80%D0%B5%D0%BC%D0%B8%D1%8F_%C2%AB%D0%9B%D0%BE%D0%BA%D1%83%D1%81%C2%BB_%D0%B7%D0%B0_%D0%BB%D1%83%D1%87%D1%88%D0%B8%D0%B9_%D0%BD%D0%B0%D1%83%D1%87%D0%BD%D0%BE-%D1%84%D0%B0%D0%BD%D1%82%D0%B0%D1%81%D1%82%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B8%D0%B9_%D1%80%D0%BE%D0%BC%D0%B0%D0%BD',
                    'fantasy' => '/wiki/%D0%9F%D1%80%D0%B5%D0%BC%D0%B8%D1%8F_%C2%AB%D0%9B%D0%BE%D0%BA%D1%83%D1%81%C2%BB_%D0%B7%D0%B0_%D0%BB%D1%83%D1%87%D1%88%D0%B8%D0%B9_%D1%80%D0%BE%D0%BC%D0%B0%D0%BD_%D1%84%D1%8D%D0%BD%D1%82%D0%B5%D0%B7%D0%B8'
                ],
                'table' => [
                    'best' => '/wiki/%D0%9F%D1%80%D0%B5%D0%BC%D0%B8%D1%8F_%D0%9B%D0%BE%D0%BA%D1%83%D1%81_%D0%B7%D0%B0_%D0%BB%D1%83%D1%87%D1%88%D0%B8%D0%B9_%D1%80%D0%BE%D0%BC%D0%B0%D0%BD',
                    'debut' => '/wiki/%D0%9F%D1%80%D0%B5%D0%BC%D0%B8%D1%8F_%D0%9B%D0%BE%D0%BA%D1%83%D1%81_%D0%B7%D0%B0_%D0%BB%D1%83%D1%87%D1%88%D0%B8%D0%B9_%D0%B4%D0%B5%D0%B1%D1%8E%D1%82%D0%BD%D1%8B%D0%B9_%D1%80%D0%BE%D0%BC%D0%B0%D0%BD',
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getHugoEnCategories() {
        //TODO Best All-Time Series	1966	Series of works
        return [
            self::EN_NOVEL => '/wiki/Hugo_Award_for_Best_Novel',
            self::EN_NOVELLA => '/wiki/Hugo_Award_for_Best_Novella',
            self::EN_NOVELLETTE => '/wiki/Hugo_Award_for_Best_Novelette',
            //self::EN_SHORT_STORY => '/wiki/Hugo_Award_for_Best_Short_Story'
        ];
    }

    /**
     * @return array
     */
    public static function getHugoRuCategories() {
        //one page
        return array(
            'url' => '/wiki/%D0%A5%D1%8C%D1%8E%D0%B3%D0%BE',
            'categories' => [
                1 => self::ID_NOVEL,
                2 => self::ID_NOVELLA,
                3 => self::ID_NOVELLETTE,
                4 => self::ID_SHORT_STORY
            ]
        );
    }

    /**
     * @return array
     */
    public static function getNebulaEnCategories() {
        return [
            self::EN_NOVEL => '/wiki/Nebula_Award_for_Best_Novel',
            self::EN_NOVELLA => '/wiki/Nebula_Award_for_Best_Novella',
            self::EN_NOVELLETTE => '/wiki/Nebula_Award_for_Best_Novelette',
            //self::EN_SHORT_STORY => '/wiki/Nebula_Award_for_Best_Short_Story'
        ];
    }

    /**
     * @return array
     */
    public static function getNebulaRuCategories() {
        return [
            self::RU_NOVEL => '/wiki/Премия_«Небьюла»_за_лучший_роман',
            self::RU_NOVELLA => '/wiki/Премия_«Небьюла»_за_лучшую_повесть',
            self::RU_NOVELLETTE => '/wiki/Премия_«Небьюла»_за_лучшую_короткую_повесть',
            //self::RU_SHORT_STORY => '/wiki/Премия_«Небьюла»_за_лучший_рассказ'
        ];
    }

    /**
     * @return array
     */
    public static function getClarkeCategories() {
        $categories = self::getCategories();
        return [
            self::ID_NOVEL => $categories[self::ID_NOVEL]
        ];
    }

    /**
     * @return array
     */
    public static function getClarkeEnCategories() {
        return [
            self::EN_NOVEL => '/wiki/Arthur_C._Clarke_Award'
        ];
    }

    /**
     * @return array
     */
    public static function getClarkeRuCategories() {
        return [
            self::RU_NOVEL => '/wiki/Список_лауреатов_премии_Артура_Кларка'
        ];
    }
}

