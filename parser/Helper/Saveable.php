<?php

namespace Msnre\Parser\Helper;

/**
 * @author Sergey Bondar
 */
trait Saveable
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
     * @return bool
     */
    public function isSaved() {
        if (!$this->getFilename()) {
            return false;
        }
        return file_exists($this->getFilename());
    }

    /**
     * @param string
     */
    public function setFilename($name) {
        $this->filename = $name ? (__DIR__ . $this->path . $name . '.json') : null;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @param mixed
     */
    public function setData($data) {
        $this->tmp = $data;
    }

    /**
     * @param mixed
     */
    public function saveData($data) {
        if (!$this->getFilename()) {
            return;
        }

        $f = fopen($this->getFilename(), 'w');
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        fwrite($f, $json);
        fclose($f);
    }

    /**
     * @param bool
     * @return mixed
     */
    public function getData($isAssoc = false) {
        if (!$this->getFilename()) {
            return $this->tmp;
        }

        $json = file_get_contents($this->getFilename());
        return json_decode($json, $isAssoc);
    }

}

