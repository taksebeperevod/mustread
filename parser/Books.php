<?php

namespace Msnre\Parser;

use Msnre\Parser\Helper\Alarm;
use Msnre\Parser\Helper\Cache;
use Msnre\Parser\Helper\Authors;

/**
 * @author Sergey Bondar
 */
class Books
{
    use Alarm;

    /**
     * @var Award
     */
    protected $manager;
    /**
     * @var Authors
     */
    protected $authors;

    /**
     * @constructor
     */
    public function __construct() {
        $this->authors = new Authors();
        $this->manager = new Award($this->authors);
    }

    /**
     * @return mixed
     */
    public function getBooks() {
        $manager = $this->manager;

        $cache = new Cache('hugo', function() use ($manager) {
                return $manager->getHugo();
            });
        $hugo = $cache->getData();

        $cache = new Cache('nebula', function() use ($manager) {
                return $manager->getNebula();
            });
        $nebula = $cache->getData();

        $cache = new Cache('clarke', function() use ($manager) {
                return $manager->getClarke();
            });
        $clarke = $cache->getData();

        $cache = new Cache('locus', function() use ($manager) {
                return $manager->getLocus();
            });
        $clarke = $cache->getData();

        $books = $clarke;

        $this->authors->save();

        return $books;
    }
}

