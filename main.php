<?php
namespace main;

require 'autoloader.php';

class main
{
    const FILE_EXTENSION = 'mp3';
    const DEFAULT_RESULT_ENCODING = 'UTF-8';
    const DETECT_MODE_SHORT_OPTION = 'd';
    const CONVERT_MODE_SHORT_PARAMETER = 'c';
    const PATH_TO_FILE_SHORT_PARAMETER = 'f';
    const RESULT_ENCODING_LONG_PARAMETER = 'to-encoding';

    private $availableEncodings = [
        'UTF-8' => 0x03,
        'UTF-16' => 0x01
    ];

    private $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
        try {
            $this->handle();
        } catch (\Exception $ex) {
            $this->help($ex->getMessage());
        }
    }
    private function handle()
    {
        $filePaths = $this->getFilePaths();

        if ($this->isDetectEncodingMode()) {
            foreach($filePaths as $filePath) {
                new encodingDetector($filePath);
            }
        } elseif ($this->isConvertEncodingMode()) {
            $sourceEncoding = $this->getSourceEncoding();
            $resultEncoding = $this->getSelectResultEncoding();

            foreach($filePaths as $filePath) {
                $fixer = new encodingFixer(
                    $filePath,
                    $resultEncoding,
                    $this->availableEncodings[$resultEncoding],
                    $sourceEncoding
                );
                $fixer->fix();
            }
        } else {
            throw new \InvalidArgumentException('Unknown command.');
        }
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getFilePaths()
    {
        if (! array_key_exists(self::PATH_TO_FILE_SHORT_PARAMETER, $this->parameters)) {
            throw new \InvalidArgumentException('Path to files is empty.');
        }

        $path = $this->parameters[self::PATH_TO_FILE_SHORT_PARAMETER];
        if (is_file($path)) {
            return [
                $this->parameters[self::PATH_TO_FILE_SHORT_PARAMETER]
            ];
        } elseif (is_dir($path)) {
            return glob(sprintf('%s/*%s', $path, self::FILE_EXTENSION));
        } else {
            throw new \InvalidArgumentException('Path must be a file or dir.');
        }
    }

    private function getSelectResultEncoding()
    {
        if (array_key_exists(self::RESULT_ENCODING_LONG_PARAMETER, $this->parameters)) {
            if (array_key_exists($this->parameters[self::RESULT_ENCODING_LONG_PARAMETER], $this->availableEncodings)) {
                return $this->parameters[self::RESULT_ENCODING_LONG_PARAMETER];
            } else {
                throw new InvalidArgumentException(sprintf('Unknown encoding "%s"! Available encodings "%s"',
                    $this->parameters[self::RESULT_ENCODING_LONG_PARAMETER], implode(',', $this->availableEncodings)
                ));
            }
        }

        return self::DEFAULT_RESULT_ENCODING;
    }

    private function getSourceEncoding()
    {
        $sourceEncoding = $this->parameters[self::CONVERT_MODE_SHORT_PARAMETER];
        # :TODO: check available encodings?
        return $sourceEncoding;
    }

    private function isDetectEncodingMode()
    {
        return (array_key_exists(self::DETECT_MODE_SHORT_OPTION, $this->parameters));
    }

    private function isConvertEncodingMode()
    {
        return (array_key_exists(self::CONVERT_MODE_SHORT_PARAMETER, $this->parameters));
    }

    private function help($error = null)
    {
        if ($error) {
            print "Error: {$error}\n\n";
        }

        print "-d \t - OPTION detect encoding ID3 tags action\n";
        print "-c \t - PARAMETER convert encoding ID3 tags action\n";
        print "-f \t - PARAMETER path to file\n";
        print str_repeat('=', 50) . "\n";
        print "--to-encoding \t - PARAMETER convert to specified encoding. UTF-8 default.\n";
        print "\n";
        print "Example for detect: php ./main.php -d -f=/path/to/file.mp3\n";
        print "Example for fix: php ./main.php -c=cp1251 -f=/path/to/file.mp3\n";
    }
}

$parameters = getopt('dc:f:', ['to-encoding::']);
new main($parameters);