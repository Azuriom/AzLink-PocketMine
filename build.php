<?php

error_reporting(E_ALL | E_STRICT);

if (ini_get('phar.readonly')) {
    exit('You need to enable phar creation in the php.ini by disabling "phar.readonly".'.PHP_EOL);
}

if (is_dir(__DIR__.'/vendor/pocketmine/pocketmine-mp')) {
    exit('You need to uninstall dev dependencies with "composer install --no-dev".'.PHP_EOL);
}

$description = file_get_contents(__DIR__.'/plugin.yml');
$buildDir = __DIR__.'/build';

if ($description === false) {
    exit('Unable to read plugin.yml file.'.PHP_EOL);
}

if (! is_dir($buildDir) && ! mkdir($buildDir) && ! is_dir($buildDir)) {
    exit('Unable to create the build/ directory.'.PHP_EOL);
}

preg_match('/^version: ?(.+)$/m', $description, $matches);
$pharPath = "build/AzLink-PocketMine-{$matches[1]}.phar";

if (file_exists($pharPath)) {
    unlink($pharPath);
}

$phar = new Phar($pharPath);
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->startBuffering();

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__),
);
$phar->buildFromIterator(new class($iterator) extends FilterIterator {
    public function accept(): bool
    {
        $current = $this->getInnerIterator()->current();

        if (is_dir($current)) {
            return false;
        }

        $current = substr($current, strlen(__DIR__) + 1);

        if (DIRECTORY_SEPARATOR !== '/') { // Windows uses '\\' instead of '/'
            $current = str_replace(DIRECTORY_SEPARATOR, '/', $current);
        }

        return $current === 'plugin.yml'
            || str_starts_with($current, 'src/')
            || str_starts_with($current, 'vendor/');
    }
}, __DIR__);

$phar->stopBuffering();
$phar->compressFiles(Phar::GZ);

exit('PHAR successfully created.'.PHP_EOL);
