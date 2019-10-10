<?php
namespace BoxesHeadless;

use Curl\Curl;
use Exception;

class BoxesHeadless {

    private $config = array(
        "main_url" => "https://www.boxes-headless.com"
    );
    private $cache = false;

    public function __construct($config) {
        
        $this->set($config);
    }

    public function set($config) {

        $this->config = array_merge($this->config, $config);

        if(!empty($config['cache']['path']) && !empty($config['cache']['duration']))
            $this->cache = new Cache($this->config['cache']['path'], $this->config['cache']['duration']);
    }

    public function getConfig() {
        return $this->config;
    }

    public function getProject($id)
    {
        return $this->request('project', $id);
    }

    public function getBoxe($id) {

        return $this->request('boxe', $id);
    }

    private function request($type, $id, $method="get") {

        $cache_filename = serialize($id);

        $result = ($this->cache) ? json_decode($this->cache->read($cache_filename)) : false;

        if (!$result || !$this->cache) {

            $curl = new Curl();
            $curl->setDefaultJsonDecoder(true);
            $curl->get($this->config['main_url'] . "/api/" . $type . "/" . $id, [
                'KEY' => $this->config['api_key']
            ]);

            try {
                $result = $this->array_to_object($curl->response);
                if ($this->cache)
                    $this->cache->write($cache_filename, json_encode($curl->response));
            } catch (Exception $e) {
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            }
        }
        return $result;
    }

    private function array_to_object($array)
    {
        $obj = new \stdClass;
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = $this->array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    } 
}