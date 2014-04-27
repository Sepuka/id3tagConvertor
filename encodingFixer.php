<?php
namespace main;

require 'autoloader.php';

class encodingFixer
{
    private $correctEncoding;
    /** @var id3tag */
    private $id3tag;

    public function __construct($filePath, $correctEncoding)
    {
        $repository = new id3tagRepository($filePath);

        $this->id3tag = new id3tag($repository);
        $this->correctEncoding = $correctEncoding;
    }

    /**
     * @param string $encoding
     * @param int $encodingFlag
     */
    public function fix($encoding, $encodingFlag)
    {
        $this->fixTitle($encoding);

        $this->id3tag->flush($encodingFlag);
    }

    private function fixTitle($encoding)
    {
        $titleFrame = $this->id3tag->getTitleFrame();
        $title = iconv($this->correctEncoding, $encoding, $titleFrame[id3tag::CONTENT_FRAME_KEY]);
        $this->id3tag->setTitleFrame($title);
    }
}
