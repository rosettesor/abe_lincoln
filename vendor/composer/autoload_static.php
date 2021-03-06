<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8b520846a220fa1f266cf8cba6913c64
{
    public static $files = array (
        '45e8c92354af155465588409ef796dbc' => __DIR__ . '/../..' . '/lib/base.php',
    );

    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'Norse\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Norse\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Norse',
        ),
    );

    public static $prefixesPsr0 = array (
        'V' => 
        array (
            'Valitron' => 
            array (
                0 => __DIR__ . '/..' . '/vlucas/valitron/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8b520846a220fa1f266cf8cba6913c64::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8b520846a220fa1f266cf8cba6913c64::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit8b520846a220fa1f266cf8cba6913c64::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
