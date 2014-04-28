<?php
namespace main;

require 'autoloader.php';

class encodingFixer
{
    /** @var string */
    private $correctEncoding;
    /** @var int */
    private $encodingEncodingFlag;
    /** @var string */
    private $sourceEncoding;
    /** @var id3tag */
    private $id3tag;

    public function __construct($filePath, $correctEncoding, $correctEncodingFlag, $sourceEncoding)
    {
        $repository = new id3tagRepository($filePath);

        $this->id3tag = new id3tag($repository);
        $this->correctEncoding = $correctEncoding;
        $this->encodingEncodingFlag = $correctEncodingFlag;
        $this->sourceEncoding = $sourceEncoding;
    }

    /**
     * @param string $encoding
     * @param int $encodingFlag
     */
    public function fix()
    {
        $this->fixTitle();
        $this->fixArtist();

        $this->id3tag->flush($this->encodingEncodingFlag);
    }

    private function fixTitle()
    {
        $titleFrame = $this->id3tag->getTitleFrame();
        $title = iconv($this->sourceEncoding, $this->correctEncoding, $titleFrame[id3tag::CONTENT_FRAME_KEY]);
        $this->id3tag->setTitleFrame($title);
    }

    private function fixArtist()
    {
        $artistFrame = $this->id3tag->getArtistFrame();
        $artist = iconv($this->sourceEncoding, $this->correctEncoding, $artistFrame[id3tag::CONTENT_FRAME_KEY]);
        $this->id3tag->setArtistFrame($artist);
    }
}
