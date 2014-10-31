<?php

namespace Msnre\Parser;

/**
 * @author Sergey Bondar
 */
class Cache
{
    /**
     * @var string
     */
    protected $path = '/json/';
    /**
     * @var string
     */
    protected $filename;

    /**
     * @param string
     * @param callable
     */
    public function __construct($name, callable $function = null) {
        $this->filename = __DIR__ . $this->path . $name . '.json';
        if (!$name || !$this->isSaved($name)) {
            $data = $function();
            $this->save($data);
        }
    }

    /**
     * @param string
     * @return bool
     */
    public function isSaved($file) {
        return file_exists($this->filename);
    }

    /**
     * @param mixed
     */
    public function save($data) {
        $f = fopen($this->filename, 'w+');
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        fwrite($f, $json);
        fclose($f);
    }

    /**
     * @return mixed
     */
    public function get() {
        $json = file_get_contents($this->filename);
        return json_decode($json);
    }

}

