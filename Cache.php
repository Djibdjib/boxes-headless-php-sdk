<?php

namespace BoxesHeadless;

class Cache
{
    public $dirname = null;
    public $duration = 2;

    private $buffer = false;

    public function __construct($dirname, $duration)
    {
        $this->dirname = $dirname;
        $this->duration = $duration;
    }

    public function write($filename, $content)
    {
        if (!file_exists($this->dirname))
            mkdir($this->dirname);

        return file_put_contents($this->dirname . '/' . md5($filename) . '.json', $content, LOCK_EX);
    }

    public function read($filename, $isArray = false)
    {
        $filename = $this->dirname . '/' . md5($filename) . '.json';
        if (file_exists($filename)) {

            $lifetime = (time() - filemtime($filename)) / 60;

            if ($lifetime > $this->duration)
                return false;

            return file_get_contents($filename, $isArray);
        }
        return false;
    }

    public function delete($filename)
    {
        $filename = $this->dirname . '/' . md5($filename) . '.json';
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function clear()
    {
        if ($this->dirname !== null) {

            $files = glob($this->dirname . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    public function start($cachename)
    {
        if ($content = $this->read($cachename)) {
            echo $content;
            $this->buffer = false;
            return true;
        }
        ob_start();
        $this->buffer =  $cachename;
    }

    public function end()
    {
        if (!$this->buffer)
            return false;

        $content = ob_get_clean();
        echo $content;
        $this->write($this->buffer, $content);
    }
}
