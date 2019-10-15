<?php
namespace BoxesHeadless;

class Thumbnail {

    private $formats = [];
    private $always = [
        "&aro",
        "&skip-original",
        "&srgb",
        "&save-as=jpg"
    ];

    public function __construct($config) {

        $this->mos_cimage = $config['moscimage_path'];
        $this->setFormats();
    }

    public function get($src, $size="full") {

        $always = implode($this->always);
        return $this->mos_cimage."?&src=".$src.$this->formats[$size].$always;
    }

    public function addFormat($key, $value) {

        $this->formats = array_merge($this->formats, [$key => $value]);
    }

    private function setFormats() {

        $this->formats = [
            'full' => "",
            'square_50' => "&w=50&h=50&crop-to-fit",
            'square_100' => "&w=100&h=100&crop-to-fit",
            'square_150' => "&w=150&h=150&crop-to-fit",
            'square_200' => "&w=200&h=200&crop-to-fit",
            'auto_w_200' => "&width=200",
        ];

    }
}