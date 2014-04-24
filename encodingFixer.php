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

    public function fix()
    {
        $titleFrame = $this->id3tag->getTitleFrame();
        $title = iconv($this->correctEncoding, 'UTF-16', $titleFrame[id3tag::CONTENT_FRAME_KEY]);
        $this->id3tag->setTitleFrame($title);
        $this->id3tag->flush();
    }
}
