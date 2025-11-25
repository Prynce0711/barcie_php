<?php
require __DIR__ . '/../vendor/autoload.php';

echo "Starting autoload test...\n";

if (class_exists('\Composer\Autoload\ClassLoader')) {
    echo "Class Composer\\Autoload\\ClassLoader exists\n";
    $loader = new \Composer\Autoload\ClassLoader(__DIR__ . '/..');
    echo "Instantiated ClassLoader\n";
    echo "Registered loaders: " . count(\Composer\Autoload\ClassLoader::getRegisteredLoaders()) . "\n";
} else {
    echo "ClassLoader not found\n";
}

echo "Autoload test completed.\n";

?>
