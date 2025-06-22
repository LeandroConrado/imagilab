<?php
spl_autoload_register(function ($class) {
    $prefixes = [
        'Core\\' => __DIR__ . '/../core/',
        'App\\' => __DIR__ . '/../app/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
            }
        }
    }
});
