<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
class Cache
{
    use Saveable;

    /**
     * @var array ONLY DEBUG
     */
    protected $skipCaches = [
        'prometheus'
    ];

    /**
     * @var string
     */
    protected $path = '/../json/';
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param string
     * @param callable
     */
    public function __construct($name = null, callable $function = null) {
        foreach ($this->skipCaches as $skip) {
            if (preg_match('/' . $skip . '/', $name)) {
                $name = null;
            }
        }

        $this->setFilename($name);

        if (!$name) {
            $data = $function();
            $this->setData($data);
        } elseif (!$this->isSaved()) {
            $data = $function();
            $this->saveData($data);
        }
    }

}

