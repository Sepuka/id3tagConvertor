<?php
namespace main;

require 'autoloader.php';

class encodingDetector
{
    private $availableEncodings = [
        'cp1251'
    ];

    public function __construct($filePath)
    {
        $repository = new id3tagRepository($filePath);

        $id3tag = new id3tag($repository);
        $this->printVariations($id3tag->getTitleFrame());
    }

    private function printTitle($title, $encoding)
    {
        print sprintf("TITLE: '%s'; ENC: '%s'\n",
            $title, $encoding);
    }

    private function printVariations(array $titleFrame)
    {
        $this->printTitle($titleFrame[id3tag::CONTENT_FRAME_KEY], 'default');

        foreach($this->availableEncodings as $encoding) {
            $content = iconv($encoding, 'UTF-8', $titleFrame[id3tag::CONTENT_FRAME_KEY]);
            $this->printTitle($content, $encoding);
        }
    }
}
