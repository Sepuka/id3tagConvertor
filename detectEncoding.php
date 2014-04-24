<?php
namespace main;

require 'autoloader.php';

class detectEncoding
{
    private $availableEncodings = [
        'cp1251'
    ];

    public function __construct($filePath)
    {
        if (! $this->isReadableFile($filePath)) {
            throw new \RuntimeException("Enable to read '{$filePath}' file.");
        }

        $id3tag = $this->createId3Tag($filePath);
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

    /**
     * @param string $filePath
     * @return id3tag
     * @throws \RuntimeException
     */
    private function createId3Tag($filePath)
    {
        $resource = fopen($filePath, 'rb');

        if ($resource === false) {
            $error = error_get_last();
            throw new \RuntimeException(sprintf('Failed to read file "%s". Error "%s"',
                $filePath, $error['message']));
        }

        return new id3tag($resource);
    }

    /**
     * @param string $filePath
     * @return bool
     */
    private function isReadableFile($filePath)
    {
        return (is_file($filePath) && is_readable($filePath));
    }
}
