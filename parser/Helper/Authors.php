<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
class Authors
{
    use Alarm;

    /**
     * @var array
     */
    protected $map = [
//        'Ann Leckie'=> 'Энн Леки',
//        'T. H. White' => 'Теренс Уайт',
//        'Charles Stross' => 'Чарльз Стросс',
//        'John W. Campbell' => 'Джон Кэмпбелл',
//        'Fritz Leiber' => 'Фриц Лейбер',
//        'Mary Robinette Kowal' => 'Мэри Коваль',
//        'Clifford D. Simak' => 'Клиффорд Саймак',
//        'John Chu' => 'Джон Чу',
//        'Arthur C. Clarke' => 'Артур Кларк',
//
//        'Algis Budrys' => '',
//        'Robert Sheckley' => 'Роберт Шекли',
//        'Mark Phillips' => '',
//        'Kurt Vonnegut' => 'Курт Воннегут',
//        'Harry Harrison' => 'Гарри Гаррисон',
//        'Daniel F. Galouye' => '',
//        'James White' => '',
//        'Marion Zimmer Bradley' => '',
//        'H. Beam Piper' => '',
//        'Jean Bruller' => '',
//        'Andre Norton' => 'Андрэ Нортон',
//        'Edgar Pangborn' => '',
//        'Cordwainer Smith' => '',
//        'Edward E. Smith' => '',
//        'Randall Garrett' => '',
//        'James H. Schmitz' => '',
//        'Thomas Burnett Swann' => '',
//        'Piers Anthony' => '',
//        'Chester Anderson' => 'Честер Андерсон',
//        'Alexei Panshin' => 'Алексей Паншин',
//        'Norman Spinrad' => '',
//        'Wilson Tucker' => '',
//        'Christopher Priest' => '',
//        'Tom Reamy' => '',
//        'Patricia A. McKillip' => '',
//        'Thomas Disch' => '',
//        'Gene Wolfe' => '',
//        'Julian May' => '',
//        'John Crowley' => '',
//        'Donald Kingsbury' => '',
//        'R. A. MacAvoy' => 'Роберт МакАвой',
//        'David R. Palmer' => '',
//        'Bob Shaw' => '',
//        'L. Ron Hubbard' => 'Рон Хаббард',
//        'Sheri S. Tepper' => '',
//        'Michael P. Kube-McDowell' => '',
//        'Emma Bull' => '',
//        'John Barnes' => '',
//        'Michael Bishop' => '',
//        'James K. Morrow' => '',
//        'Stephen Baxter' => '',
//        'Elizabeth Moon' => '',
//        'Walter Jon Williams' => '',
//        'Mary Doria Russell' => '',
//        'Ken MacLeod' => '',
//        'Nalo Hopkinson' => '',
//        'Iain M. Banks' => 'Иэн Бэнкс',
//        'Naomi Novik' => '',
//        'Michael F. Flynn' => '',
//        'Cory Doctorow' => 'Кори Доктороу',
//        'Cherie Priest' => '',
//        'Mira Grant' => '',
//        'N. K. Jemisin' => '',
//        'James S. A. Corey' => '',
//        'Saladin Ahmed' => '',
//        'Larry Correia' => '',
//        'Robert Jordan' => '',
//        'Edgar Rice Burroughs' => 'Эдгар Берроуз',
//        'E. E. Smith' => '',
//        'C. S. Lewis' => '',
//        'A. E. van Vogt' => 'Альфред ван Вогт',
//        'Edmond Hamilton' => '',
//        'Dean McLaughlin' => '',
//        'Dean Koontz' => '',
//        'Gardner Dozois' => '',
//        'Jerry Pournelle' => '',
//        'Richard Cowper' => '',
//        'Gregory Benford' => '',
//        'Keith Laumer' => '',
//        'Ted Reynolds' => '',
//        'Hilbert Schenck' => '',
//        'Phyllis Eisenstein' => '',
//        'Joseph H. Delaney' => '',
//        'John Kessel' => '',
//        'Charles L. Harness' => '',
//        'Bradley Denton' => '',
//        'Megan Lindholm' => '',
//        'Judith Moffett' => '',
//        'Pat Murphy' => '',
//        'Jonathan Carroll' => '',
//        'G. David Nordley' => '',
//        'Jack Cady' => '',
//        'Brian Stableford' => '',
//        'Jack McDevitt' => '',
//        'Jerry Oltion' => '',
//        'Mary Rosenblum' => '',
//        'Adam-Troy Castro' => '',
//        'Paul Levinson' => '',
//        'Catherine Asaro' => '',
//        'Ian R. MacLeod' => '',
//        'Kage Baker' => '',
//        'Brenda Clough' => '',
//        'Andy Duncan' => '',
//        'Jack Dann' => '',
//        'Richard Chwedyk' => '',
//        'Paul Di Filippo' => '',
//        'Charles Coleman Finlay' => '',
//        'Pat Forde' => '',
//        'Michael A. Burstein' => '',
//        'Paul Melko' => '',
//        'William Shunn' => '',
//        'Benjamin Rosenbaum' => '',
//        'James Morrow' => '',
//        'Rachel Swirsky' => '',
//        'Elizabeth Hand' => '',
//        'Alastair Reynolds' => '',
//        'Carolyn Ives Gilman' => '',
//        'Catherynne M. Valente' => '',
//        'Aliette de Bodard' => '',
//        'Jay Lake' => '',
//        'Dan Wells' => '',
//        'Brad R. Torgersen' => '',
//        'Ayn Rand' => '',
//        'H. L. Gold' => '',
//        'John Wyndham' => '',
//        'Henry Kuttner' => '',
//        'A. Bertram Chandler' => '',
//        'Richard S. Shaver' => '',
//        'Pauline Ashwell' => '',
//        'Zenna Henderson' => '',
//        'C.M. Kornbluth' => '',
//        'Katherine MacLean' => '',
//        'Rog Phillips' => '',
//        'Robert M., Green, Jr.' => '',
//        'Hayden Howard' => '',
//        'Richard Wilson' => '',
//        'William Rotsler' => '',
//        'Richard A. Lupoff' => '',
//        'William Walling' => '',
//        'Carter Scholz' => '',
//        'Dean Ing' => '',
//        'Keith Roberts' => '',
//        'Michael Shea' => '',
//        'Howard Waldrop' => '',
//        'Edward Bryant' => '',
//        'Parke Godwin' => '',
//        'S. P. Somtow' => '',
//        'Ian Watson' => '',
//        'Eric Vinicoff' => '',
//        'Bruce McAllister' => '',
//        'Steven Gould' => '',
//        'Neal Barrett, Jr.' => '',
//        'Dafydd ab Hugh' => '',
//        'Martha Soukup' => '',
//        'Pamela Sargent' => '',
//        'Susan Shwartz' => '',
//        'Barry N. Malzberg' => '',
//        'William Barton' => '',
//        'James Alan Gardner' => '',
//        'William Sanders' => '',
//        'Ellen Klages' => '',
//        'Eleanor Arnason' => '',
//        'Jan Jensen' => '',
//        'Tom Purdom' => '',
//        'Stanley Schmidt' => '',
//        'Shane Tourtellotte' => '',
//        'Gregory Frost' => '',
//        'Jeffrey Ford' => '',
//        'Christopher Rowe' => '',
//        'Geoff Ryman' => '',
//        'Daniel Abraham' => '',
//        'David Moles' => '',
//        'Nicola Griffith' => '',
//        'Paul Cornell' => '',
//        'Eugie Foster' => '',
//        'Sean McMullen' => '',
//        'Eric James Stone' => '',
//        'Thomas Olde Heuvelt' => '',
//        'Seanan McGuire' => '',
//        'Theodore Beale' => '',
//        'Robert E. Howard' => '',
//        'C. L. Moore' => '',
//        'Fredric Brown' => '',
//        'Lester del Rey' => '',
//        'Lewis Padgett' => '',
//        'Theodore Cogswell' => '',
//        'Anton Lee Baker' => '',
//        'J. F. Bone' => '',
//        'C. M. Kornbluth' => '',
//        'Stanley Mullen' => '',
//        'Manly Wade Wellman' => '',
//        'Ralph Williams' => '',
//        'Lloyd Biggle, Jr.' => '',
//        'Mack Reynolds' => '',
//        'Gary Jennings' => '',
//        'Rick Raphael' => '',
//        'Robert F. Young' => '',
//        'Raymond F. Jones' => '',
//        'Richard McKenna' => '',
//        'Fred Saberhagen' => '',
//        'Terry Carr' => '',
//        'Betsy Curtis' => '',
//        'Ben Bova' => '',
//        'Stephen Tall' => '',
//        'P. J. Plauger' => '',
//        'Charles L. Grant' => '',
//        'Susan C. Petry' => '',
//        'Jeff Duntemann' => '',
//        'Somtow Sucharitkul' => '',
//        'George Guthridge' => '',
//        'William F. Wu' => '',
//        'Lee Killough' => '',
//        'Nancy Springer' => '',
//        'David S. Garnett' => '',
//        'Karen Joy Fowler' => '',
//        'Lisa Goldstein' => '',
//        'Eileen Gunn' => '',
//        'W. R. Thompson' => '',
//        'Nicholas A. DiChario' => '',
//        'Bridget McKenna' => '',
//        'M. Shayne Bell' => '',
//        'Esther Friesner' => '',
//        'Tony Daniel' => '',
//        'Molly Gloss' => '',
//        'Dominic Green' => '',
//        'Margo Lanagan' => '',
//        'Lawrence M. Schoen' => '',
//        'Carrie Vaughn' => '',
//        'E. Lily Yu' => '',
//        'Nancy Fulda' => '',
//        'Sofia Samatar' => '',
//        'L. Sprague de Camp' => '',
//        'Richard Matheson' => '',
//        'A. J. Deutsch' => '',
//        'Reginald Bretnor' => '',
//        'Jerome Bixby' => '',
    ];

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
        return $this->map;
    }

    /**
     * @param string
     * @return string|null
     */
    public function getRussianAuthor($enAuthor) {
        $enAuthor = $this->removeSecondNameEn($enAuthor);
        if (!isset($this->map[$enAuthor]) || !$this->map[$enAuthor]) {
            return null;
        }

        return $this->map[$enAuthor];
    }

    /**
     * @param string
     * @return string|null
     */
    public function getEnglishAuthor($ruAuthor) {
        $ruAuthor = $this->removeSecondName($ruAuthor);

        foreach ($this->map as $en => $ru) {
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
            if (!isset($this->map[$key])) {
                $this->map[$key] = $value;
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
            if (!$h->ru->author) {
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
            if (!isset($this->map[$key])) {
                $this->map[$key] = $value;
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

