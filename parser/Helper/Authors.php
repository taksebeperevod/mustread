<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
class Authors
{
    use Alarm;

    use Saveable;

    /**
     * @var array
     */
    protected $mapped = [];

    /**
     * @var array
     */
    protected $unmappedEn = [];

    /**
     * @var array
     */
    protected $unmappedRu = [];

    /**
     * @constructor
     */
    public function __construct() {
        $this->setFilename('authors');
        if (!$this->isSaved()) {
            $this->saveData($this->mapped);
        }
        $savedMap = $this->getData($isAssoc = true);
        $this->mapped = $savedMap;
    }

    /**
     */
    public function save() {
        $this->saveData($this->mapped);
    }

    /**
     * @param &mixed
     * @return mixed
     */
    public function fixAuthors(&$collection) {
        foreach ($collection as $key => $h) {
            if (!$h->ru || !$h->ru->author) {
                continue;
            }
            //and clean up russian authors
            $ruA = $h->ru->author;
            if ($ruA) {
                $ruA = $this->removeSecondName($ruA);
                $collection[$key]->ru->author = $ruA;
            }

            $enA = $h->ru->author;
            if ($enA) {
                $enA = str_replace('ł', 'l', $enA);
                $collection[$key]->en->author = $enA;
            }
        }
    }

    /**
     * @return array
     */
    public function getAuthorsMap() {
        return $this->mapped;
    }

    /**
     * @param string
     * @return string|null
     */
    public function getRussianAuthor($enAuthor) {
        $enAuthor = $this->removeSecondNameEn($enAuthor);
        if (!isset($this->mapped[$enAuthor]) || !$this->mapped[$enAuthor]) {
            return null;
        }

        return $this->mapped[$enAuthor];
    }

    /**
     * @param string
     * @return string|null
     */
    public function getEnglishAuthor($ruAuthor) {
        $ruAuthor = $this->removeSecondName($ruAuthor);

        foreach ($this->mapped as $en => $ru) {
            if ($ru == $ruAuthor) {
                return $en;
            }
        }

        return null;
    }

    /**
     * @param &mixed
     * @param mixed
     */
    public function collectAuthorsByEnTitleAndPopulate(&$ru, $en) {
        $ruAuthors = [];

        foreach ($ru as $key => $ruBook) {
            if ($ruBook->en->author || !$ruBook->en->name) {
                continue;
            }
            foreach ($en as $enBook) {
                if (!$enBook->author || $ruBook->year != $enBook->year) {
                    continue;
                }
                if ($ruBook->en->name == $enBook->name) {
                    $ruA = $ruBook->ru->author;
                    $ruA = $this->removeSecondName($ruA);
                    $ru[$key]->ru->author = $ruA;

                    $enA = $enBook->author;
                    $enA = $this->removeSecondNameEn($enA);
                    $ru[$key]->en->author = $enA;

                    $ruAuthors[$enA] = $ruA;
                }
            }
        }

        foreach ($ruAuthors as $key => $value) {
            if (!isset($this->mapped[$key])) {
                $this->mapped[$key] = $value;
            }
        }
    }

    /**
     * @param mixed
     * @return mixed
     */
    public function collectAuthors($collection) {
        $ruAuthors = [];

        //use same authors
        foreach ($collection as $key => $h) {
            if (!$h->en->author && $h->ru->author) {
                $this->unmappedRu[] = $h->ru->author;
                continue;
            }
            if (!$h->ru->author && $h->en->author) {
                $this->unmappedEn[] = $h->en->author;
                continue;
            }

            //and clean up russian authors
            $enA = $h->en->author;
            $enA = $this->removeSecondNameEn($enA);

            $ruA = $h->ru->author;
            $ruA = $this->removeSecondName($ruA);

            $ruAuthors[$enA] = $ruA;
        }

        foreach ($ruAuthors as $key => $value) {
            if (!isset($this->mapped[$key])) {
                $this->mapped[$key] = $value;
            }
        }
    }

    /**
     * @param string
     * @return mixed
     */
    protected function removeSecondName($ruA) {
        //Ru Wiki is sucks Пол. Дж. Макоули
        //Д. Дж.
        return preg_replace('/([А-Я][а-я]+)\.?\s[А-Я][а-я]?\.\s([А-Я][а-я]+)/u', '$1 $2', $ruA);
    }

    /**
     * @param string
     * @return mixed
     */
    protected function removeSecondNameEn($enA) {
        //Ru Wiki is sucks Пол. Дж. Макоули
        //Д. Дж.
        //$enA = preg_replace('/^[A-Z]\.\s([A-Z][a-z]+)/u', '$1', $enA);
        return preg_replace('/([A-Z][a-z]+)\s[A-Z]\.\s([A-Z][a-z]+)/u', '$1 $2', $enA);
    }
}

