<?php
function __autoload($classPath)
{
    $className = end(explode('\\', $classPath));
    $fileName = sprintf('%s.php', $className);
    require $fileName;
}
