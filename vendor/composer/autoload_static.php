<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf1552c32ef89e605f996c57917f41a3f
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'ArrayPress\\Google\\Timezone\\' => 27,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ArrayPress\\Google\\Timezone\\' => 
        array (
            0 => __DIR__ . '/..' . '/arraypress/google-timezone/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf1552c32ef89e605f996c57917f41a3f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf1552c32ef89e605f996c57917f41a3f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf1552c32ef89e605f996c57917f41a3f::$classMap;

        }, null, ClassLoader::class);
    }
}
