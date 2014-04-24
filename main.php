<?php
namespace main;

require 'autoloader.php';

class main
{
    const DETECT_MODE = 'd';
    const CONVERT_MODE = 'c';

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
        if (array_key_exists(self::DETECT_MODE, $parameters)) {
            $filePath = $parameters[self::DETECT_MODE];
            new detectEncoding($filePath);
        } elseif (array_key_exists(self::CONVERT_MODE, $parameters)) {

        } else {
            throw new \InvalidArgumentException('Unknown command.');
        }
    }

    private function help($error = null)
    {
        if ($error) {
            print "Error: {$error}\n";
        }

        print "-d \t - detect encoding ID3 tags\n";
        print "-c \t - convert encoding ID3 tags\n";
    }
}

$parameters = getopt('d:c:');
new main($parameters);