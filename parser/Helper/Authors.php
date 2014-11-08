<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
class Authors
{
    use Alarm;

    use Saveable;

    //TODO 'Gregory D. Bear' => 'Грег Бир', БЕАР
    //'James Corey' => 'Джеймс Кори', 'Daniel Abraham' => 'Дэниел Абрахам', (один и тот же человек? https://www.google.ru/url?sa=t&rct=j&q=&esrc=s&source=web&cd=13&cad=rja&uact=8&ved=0CFAQFjAM&url=http%3A%2F%2Fimhonet.ru%2Fperson%2F224015%2Frole%2F100%2F&ei=bpJeVM3tEsStPJ3ugfgF&usg=AFQjCNF1iEyCWotK5gwjcsbfNjMMBt1_Gw&sig2=R2RcCr_P8Ym7yfEO83_Dlg)
    /**
     * @var array
     */
    protected $mapped = [
        'Ann Leckie' => 'Энн Леки',
        'Kameron Hurley' => 'Камерон Херли',
        'Phillip Mann' => 'Филипп Манн',
        'James Smythe' => 'Джеймс Смайт',
        'Ramez Naam' => 'Рамез Наам'
    ];

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
        $tmp = [];
        foreach ($this->mapped as $key => $value) {
            if (!$key || !$value) {
                continue;
            }
            $tmp[$key] = $value;
        }
        $this->mapped = $tmp;
        $this->mapped = array_merge($this->mapped, $savedMap);
    }

    /**
     */
    public function save() {
        foreach ($this->mapped as $en => $ru) {
            if (preg_match('/[^A-Za-zé, . -]/u', $en)) {
                var_dump($en, $this->mapped);
                throw new \Exception('Something wrong with authors!');
            }
            if (preg_match('/[^А-Яа-яЁёЙй. -]/u', $ru)) {
                var_dump($ru, preg_replace('/[А-Яа-яЁёЙй.   -]/u', '', $ru), $this->mapped);
                foreach ($this->mapped as $e => $r) {
                    echo '"' . $e . '" => "'.$r.'"<br>';
                }
                throw new \Exception('Something wrong with authors!');
            }
        }
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

            $enA = $h->en->author;
            if ($enA) {
                $enA = str_replace('ł', 'l', $enA);
                $enA = $this->removeSecondNameEn($enA);
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
        if ($ruA == 'Иен Макдональд') {
            return 'Йен Макдональд';
        }
        if ($ruA == 'Жаклин Кари') {
            return 'Жаклин Кэри';
        }
        if ($ruA == 'Ким Стенли Робинсон') {
            return 'Ким Стэнли Робинсон';
        }
        if ($ruA == 'Вернор Виндж') {
            return 'Вернон Виндж';
        }
        if ($ruA == 'К. У. Джетер') {
            $ruA = 'Кевин Джетер';
        }
        if ($ruA == 'Пат Мёрфи') {
            $ruA = 'Пат Мерфи';
        }
        if ($ruA == 'Джон Кортней Гримвуд') {
            $ruA = 'Джон Кортни Гримвуд';
        }
        //Ru Wiki is sucks Пол. Дж. Макоули
        //Д. Дж.
        return preg_replace('/([А-Я][а-я]+)\.?\s[А-Я][а-я]?\.\s([А-Я][а-я]+)/u', '$1 $2', $ruA);
    }

    /**
     * @param string
     * @return mixed
     */
    public function removeSecondNameEn($enA) {
        if ($enA == 'China Mieville') {
            $enA = 'China Miéville';
        }
        if ($enA == 'K. W. Jeter') {
            $enA = 'Kevin Jeter';
        }
        if ($enA == 'Gregory D. Bear') {
            $enA = 'Greg Bear';
        }
        if ($enA == 'Iain M. Banks') {
            $enA = 'Iain Banks';
        }
        //Ru Wiki is sucks Пол. Дж. Макоули
        //Д. Дж.
        //$enA = preg_replace('/^[A-Z]\.\s([A-Z][a-z]+)/u', '$1', $enA);
        return preg_replace('/([A-Z][a-z]+)\s[A-Z]\.\s([A-Z][a-z]+)/u', '$1 $2', $enA);
    }
}

