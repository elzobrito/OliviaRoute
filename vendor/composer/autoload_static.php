<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit93eb3d1575c9fe74e84c13e8a4c42cac
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'OliviaRouter\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'OliviaRouter\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit93eb3d1575c9fe74e84c13e8a4c42cac::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit93eb3d1575c9fe74e84c13e8a4c42cac::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit93eb3d1575c9fe74e84c13e8a4c42cac::$classMap;

        }, null, ClassLoader::class);
    }
}
