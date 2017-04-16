<?php


function autoload($className)
{
 	$fileName  =  __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';
    if (is_file($fileName)) {
        require $fileName;
        return true;
    }
    return false;
}

spl_autoload_register('autoload');