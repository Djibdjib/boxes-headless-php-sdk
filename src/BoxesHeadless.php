<?php
namespace BoxesHeadless;

use BoxesHeadless\Thumbnail;
use Curl\Curl;
use Exception;

class BoxesHeadless {

    private $config = array(
        "main_url" => "https://www.boxes-headless.com",
        'thumbnails' => []
    );
    private $cache = false;

    public function __construct($config) {
        $this->set($config);
        $this->thumbnail = new Thumbnail($this->config['thumbnails']);
    }

    public function set($config) {

        $this->config = array_merge($this->config, $config);

        if(!empty($config['cache']['path']) && !empty($config['cache']['duration']))
            $this->cache = new Cache($this->config['cache']['path'], $this->config['cache']['duration']);
    }

    public function getConfig() {
        return $this->config;
    }

    public function getCache() {
        return $this->cache;
    }

    public function getProject($id)
    {
        return $this->request('project', $id);
    }

    public function getBoxe($id) {

        return $this->request('boxe', $id);
    }

    public function postBoxe($id, $send=array()) {

        return $this->request('boxe', $id, "post", $send);
    }

    private function request($type, $id, $method="get", $toSend=array()) {

        $cache_filename = serialize($id);

        $result = ($this->cache) ? json_decode($this->cache->read($cache_filename)) : false;

        if (!$result || !$this->cache) {

            $curl = new Curl();
            $curl->setDefaultJsonDecoder(true);

            if($method == "get") {

                $curl->get($this->config['main_url'] . "/api/" . $type . "/" . $id, [
                    'KEY' => $this->config['api_key']
                ]);
            }
            else {
                $curl->post($this->config['main_url'] . "/api/" . $type . "/post/" . $id . "?KEY=" . $this->config['api_key'], [
                    $toSend
                ]);
            }

            try {
                $result = $this->array_to_object($curl->response);
                if ($this->cache && $method == "get")
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