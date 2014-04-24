<?php
namespace main;

require 'autoloader.php';

class main
{
    const DETECT_MODE = 'd';
    const CONVERT_MODE = 'c';
    const PATH_TO_FILE = 'f';

    public function __construct(array $parameters)
    {
        try {
            $this->handle($parameters);
        } catch (\Exception $ex) {
            $this->help($ex->getMessage());
        }
    }

    private function handle($parameters)
    {
        if (array_key_exists(self::PATH_TO_FILE, $parameters)) {
            $filePath = $parameters[self::PATH_TO_FILE];
        } else {
            throw new \InvalidArgumentException('Path to file is empty.');
        }

        if (array_key_exists(self::DETECT_MODE, $parameters)) {
            new encodingDetector($filePath);
        } elseif (array_key_exists(self::CONVERT_MODE, $parameters)) {
            $encoding = $parameters[self::CONVERT_MODE];
            $fixer = new encodingFixer($filePath, $encoding);
            $fixer->fix();
        } else {
            throw new \InvalidArgumentException('Unknown command.');
        }
    }

    private function help($error = null)
    {
        if ($error) {
            print "Error: {$error}\n";
        }

        print "-d \t - detect encoding ID3 tags action\n";
        print "-c \t - convert encoding ID3 tags action\n";
        print "-f \t - path to file\n";
        print "Example for detect: php ./main.php -d -f=/path/to/file.mp3\n";
        print "Example for fix: php ./main.php -c=cp1251 -f=/path/to/file.mp3\n";
    }
}

$parameters = getopt('dc:f:');
new main($parameters);