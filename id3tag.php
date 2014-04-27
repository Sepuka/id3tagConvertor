<?php
namespace main;

class id3tag
{
    const CONTENT_FRAME_KEY = 'content';
    const TITLE_FRAME_ID = 'TIT2';

    /** @var id3tagRepository */
    private $repository;
    private $tags = [];

    public function __construct(id3tagRepository $id3tagRepository)
    {
        $this->repository = $id3tagRepository;

        $this->fillTag();
    }

    public function flush($encodingFlag)
    {
        $frames = '';

        foreach($this->tags as $frameId => $data) {
            $frameSize = pack('N', strlen($data[self::CONTENT_FRAME_KEY]));
            $data[self::CONTENT_FRAME_KEY] = $this->packFrameContent($data[self::CONTENT_FRAME_KEY], $encodingFlag);
            $frames .= $frameId . $frameSize . $data['flags'] . $data[self::CONTENT_FRAME_KEY];
        }

        $this->repository->save($frames);
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

    public function setTitleFrame($title)
    {
        $this->tags[self::TITLE_FRAME_ID][self::CONTENT_FRAME_KEY] = $title;
    }

    public function isUnSynchronisation()
    {
        $flags = $this->repository->getFlags();
        $unSynchronisationFlag = 1 << 7;
        return $flags & $unSynchronisationFlag;
    }

    public function isExtendedHeader()
    {
        $flags = $this->repository->getFlags();
        $ExtendedHeader = 1 << 6;
        return $flags & $ExtendedHeader;
    }

    public function isExperimentalIndicator()
    {
        $flags = $this->repository->getFlags();
        $ExperimentalIndicator = 1 << 6;
        return $flags & $ExperimentalIndicator;
    }

    private function packFrameContent($frameContent, $encodingFlag)
    {
        $result = '';

        for ($i = 0; $i < mb_strlen($frameContent); ++$i) {
            // I don't know why i do it
            if ($i == 0) {
                $result .= pack('h', $encodingFlag);
                continue;
            }
            $result .= pack('H*', dechex(ord($frameContent[$i])));
        }

        return $result;
    }

    private function isValidFrameId($frameId)
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
        $this->repository->offsetToTag();

        while (true) {
            $frameId = $this->repository->getCurrentFrameId();
            if (! $this->isValidFrameId($frameId)) {
                break;
            }
            $frameSize = $this->repository->getCurrentFrameSize();
            $frameFlags = $this->repository->getCurrentFrameFlags();
            $content = $this->repository->getCurrentFrameContent($frameSize);
            $this->addFrame($frameId, $frameSize, $frameFlags, $content);
        }
    }
}
