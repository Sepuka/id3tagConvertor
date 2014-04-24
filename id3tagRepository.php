<?php
namespace main;

class id3tagRepository
{
    const ID3TAG_HEADER_LENGTH = 10;
    const FRAME_ID_LENGTH = 4;

    private $resource;

    public function __construct($filePath) {

        if (! $this->isReadableFile($filePath)) {
            throw new \RuntimeException("Enable to read '{$filePath}' file.");
        }

        $resource = fopen($filePath, 'rb');

        if ($resource === false) {
            $error = error_get_last();
            throw new \RuntimeException(sprintf('Failed to read file "%s". Error "%s"',
                $filePath, $error['message']));
        }

        $this->resource = $resource;
    }

    public function __destruct()
    {
        if ($this->resource) {
            fclose($this->resource);
        }
    }

    public function save($frames)
    {
        rewind($this->resource);
        $id3tagHeader = fread($this->resource, 6) . pack('L', decbin(strlen($frames)));
        $music = $this->getMusic();
        file_put_contents('/tmp/music.mp3', $id3tagHeader . $frames . $music);
    }

    public function getMajorVersion()
    {
        $this->setOffset(3);
        return base_convert(bin2hex(fread($this->resource, 1)), 16, 10);
    }

    /**
     * @return int
     */
    public function getTagSize()
    {
        $this->setOffset(6);
        $data = fread($this->resource, 4);
        return base_convert(bin2hex($data), 16, 10);
    }

    public function getFlags()
    {
        $this->setOffset(5);
        return fread($this->resource, 1);
    }

    public function offsetToTag()
    {
        $this->setOffset(self::ID3TAG_HEADER_LENGTH);
    }

    public function getCurrentFrameId()
    {
        return fread($this->resource, self::FRAME_ID_LENGTH);
    }

    public function getCurrentFrameSize()
    {
        return base_convert(bin2hex(fread($this->resource, 4)), 16, 10);
    }

    public function getCurrentFrameFlags()
    {
        return fread($this->resource, 2);
    }

    public function getCurrentFrameContent($contentLength)
    {
        return fread($this->resource, $contentLength);
    }

    /**
     * @param string $filePath
     * @return bool
     */
    private function isReadableFile($filePath)
    {
        return (is_file($filePath) && is_readable($filePath));
    }

    private function setOffset($offset)
    {
        fseek($this->resource, $offset);
    }

    private function getMusicOffset()
    {
        return self::ID3TAG_HEADER_LENGTH + $this->getTagSize();
    }

    private function getMusic()
    {
        $this->setOffset($this->getMusicOffset());
        $music = '';
        while(!feof($this->resource)) {
            $music .= fread($this->resource, 1024);
        }

        return $music;
    }
}
