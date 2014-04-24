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

    public function getMajorVersion()
    {
        $this->setOffset(3);
        return base_convert(bin2hex(fread($this->resource, 1)), 16, 10);
    }

    public function getTagSize()
    {
        $this->setOffset($this->resource, 6);
        $data = fread($this->resource, 4);
        return base_convert(bin2hex($data), 16, 10);
    }

    public function getFlags()
    {
        $this->setOffset($this->resource, 5);
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
}
