<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
class Cache
{
    /**
     * @var string
     */
    protected $path = '/../json/';
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var mixed
     */
    protected $tmp;

    /**
     * @param string
     * @param callable
     */
    public function __construct($name = null, callable $function = null) {
        $this->filename = $name ? (__DIR__ . $this->path . $name . '.json') : null;
        if (!$name) {
            $this->tmp = $function();
        } elseif (!$this->isSaved()) {
            $data = $function();
            $this->save($data);
        }
    }

    /**
     * @param string
     * @return bool
     */
    public function isSaved() {
        if (!$this->filename) {
            return false;
        }
        return file_exists($this->filename);
    }

    /**
     * @param mixed
     */
    public function save($data) {
        if (!$this->filename) {
            return;
        }

        $f = fopen($this->filename, 'w');
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        fwrite($f, $json);
        fclose($f);
    }

    /**
     * @return mixed
     */
    public function get() {
        if (!$this->filename) {
            return $this->tmp;
        }

        $json = file_get_contents($this->filename);
        return json_decode($json);
    }

}

