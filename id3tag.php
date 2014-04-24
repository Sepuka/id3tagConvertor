<?php
namespace main;

class id3tag
{
    const ID3TAG_LENGTH = 10;
    const FRAME_ID_LENGTH = 4;
    const CONTENT_FRAME_KEY = 'content';
    const TITLE_FRAME_ID = 'TIT2';

    private $resource;
    private $majorVersion;
    private $size;
    private $flags;
    private $tags = [];

    public function __construct($resource)
    {
        if (gettype($resource) != 'resource') {
            throw new \InvalidArgumentException(sprintf('Expected type "resource", got "%s" instead.',
                gettype($resource)));
        }

        $this->resource = $resource;

        $this->fillTag();
    }

    public function getSize()
    {
        if (! $this->size) {
            $this->setOffset($this->resource, 6);
            $data = fread($this->resource, 4);
            $this->size = base_convert(bin2hex($data), 16, 10);
        }

        return $this->size;
    }

    public function getMajorVersion()
    {
        if (! $this->majorVersion) {
            $this->setOffset(3);
            $this->majorVersion = base_convert(bin2hex(fread($this->resource, 1)), 16, 10);
        }

        return $this->majorVersion;
    }

    public function isUnSynchronisation()
    {
        $flags = $this->getFlags();
        $unSynchronisationFlag = 1 << 7;
        return $flags & $unSynchronisationFlag;
    }

    public function isExtendedHeader()
    {
        $flags = $this->getFlags();
        $ExtendedHeader = 1 << 6;
        return $flags & $ExtendedHeader;
    }

    public function isExperimentalIndicator()
    {
        $flags = $this->getFlags();
        $ExperimentalIndicator = 1 << 6;
        return $flags & $ExperimentalIndicator;
    }

    /**
     * @return mixed
     */
    public function getTitleFrame()
    {
        $title = null;

        if (array_key_exists(self::TITLE_FRAME_ID, $this->tags)) {
            $title = $this->tags[self::TITLE_FRAME_ID];
        }

        return $title;
    }

    private function getFlags()
    {
        if (! $this->flags) {
            $this->setOffset($this->resource, 5);
            $this->flags = fread($this->resource, 1);
        }

        return $this->flags;
    }

    private function setOffset($offset)
    {
        fseek($this->resource, $offset);
    }

    private function isFrameId($frameId)
    {
        return preg_match('#[A-Z0-9]{4}#', $frameId);
    }

    private function addFrame($frameId, $frameSize, $frameFlags, $content)
    {
        $this->tags[$frameId] = [
            'size' => $frameSize,
            'flags' => $frameFlags,
            self::CONTENT_FRAME_KEY => $content
        ];
    }

    private function fillTag()
    {
        $this->setOffset(self::ID3TAG_LENGTH);

        while (true) {
            $frameId = fread($this->resource, self::FRAME_ID_LENGTH);
            if (! $this->isFrameId($frameId)) {
                break;
            }
            $frameSize = base_convert(bin2hex(fread($this->resource, 4)), 16, 10);
            $frameFlags = fread($this->resource, 2);
            $content = fread($this->resource, $frameSize);
            $this->addFrame($frameId, $frameSize, $frameFlags, $content);
        }
    }
}
