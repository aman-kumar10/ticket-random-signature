<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita44c4ecea318a9fb2326a27d071c729f
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Faker\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Faker\\' => 
        array (
            0 => __DIR__ . '/..' . '/fzaninotto/faker/src/Faker',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita44c4ecea318a9fb2326a27d071c729f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita44c4ecea318a9fb2326a27d071c729f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita44c4ecea318a9fb2326a27d071c729f::$classMap;

        }, null, ClassLoader::class);
    }
}
