<?php
namespace main;

require 'autoloader.php';

class main
{
    const FILE_EXTENSION = 'mp3';
    const DEFAULT_RESULT_ENCODING = 'UTF-8';
    const DETECT_MODE_SHORT_PARAMETER = 'd';
    const CONVERT_MODE_SHORT_PARAMETER = 'c';
    const PATH_TO_FILE_SHORT_PARAMETER = 'f';
    const END_ENCODING_LONG_PARAMETER = 'to-encoding';

    private $availableEncodings = [
        'UTF-8' => 0x03,
        'UTF-16' => 0x01
    ];
    private $selected_encoding;

    public function __construct(array $parameters)
    {
        try {
            $this->handle($parameters);
        } catch (\Exception $ex) {
            $this->help($ex->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getSelectedEncoding()
    {
        return $this->selected_encoding;
    }

    public function getEncodingFlag()
    {
        return $this->availableEncodings[$this->getSelectedEncoding()];
    }

    private function handle($parameters)
    {
        $filePaths = $this->getFilePaths($parameters);
        $this->selected_encoding = $this->selectResultEncoding($parameters);

        if (array_key_exists(self::DETECT_MODE_SHORT_PARAMETER, $parameters)) {
            foreach($filePaths as $filePath) {
                new encodingDetector($filePath);
            }
        } elseif (array_key_exists(self::CONVERT_MODE_SHORT_PARAMETER, $parameters)) {
            $encoding = $parameters[self::CONVERT_MODE_SHORT_PARAMETER];
            foreach($filePaths as $filePath) {
                $fixer = new encodingFixer($filePath, $encoding);
                $fixer->fix($this->getSelectedEncoding(), $this->getEncodingFlag());
            }
        } else {
            throw new \InvalidArgumentException('Unknown command.');
        }
    }

    /**
     * @param array $parameters
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getFilePaths(array $parameters)
    {
        if (! array_key_exists(self::PATH_TO_FILE_SHORT_PARAMETER, $parameters)) {
            throw new \InvalidArgumentException('Path to files is empty.');
        }

        $path = $parameters[self::PATH_TO_FILE_SHORT_PARAMETER];
        if (is_file($path)) {
            return [
                $parameters[self::PATH_TO_FILE_SHORT_PARAMETER]
            ];
        } elseif (is_dir($path)) {
            return glob(sprintf('%s/*%s', $path, self::FILE_EXTENSION));
        } else {
            throw new \InvalidArgumentException('Path must be a file or dir.');
        }
    }

    private function selectResultEncoding(array $parameters)
    {
        if (array_key_exists(self::END_ENCODING_LONG_PARAMETER, $parameters)) {
            if (array_key_exists($parameters[self::END_ENCODING_LONG_PARAMETER], $this->availableEncodings)) {
                return $parameters[self::END_ENCODING_LONG_PARAMETER];
            } else {
                throw new InvalidArgumentException(sprintf('Unknown encoding "%s"! Available encodings "%s"',
                    $parameters[self::END_ENCODING_LONG_PARAMETER], implode(',', $this->availableEncodings)
                ));
            }
        }

        return self::DEFAULT_RESULT_ENCODING;
    }

    private function help($error = null)
    {
        if ($error) {
            print "Error: {$error}\n\n";
        }

        print "-d \t - detect encoding ID3 tags action\n";
        print "-c \t - convert encoding ID3 tags action\n";
        print "-f \t - path to file\n";
        print str_repeat('=', 50) . "\n";
        print "--to-encoding \t - convert to specified encoding. UTF-8 default.\n";
        print "\n";
        print "Example for detect: php ./main.php -d -f=/path/to/file.mp3\n";
        print "Example for fix: php ./main.php -c=cp1251 -f=/path/to/file.mp3\n";
    }
}

$parameters = getopt('dc:f:', ['to-encoding::']);
new main($parameters);