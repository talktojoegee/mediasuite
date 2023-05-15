<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit77c17b7bb42fd5495cd89d22cf56981b
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DirkGroenen\\Pinterest\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DirkGroenen\\Pinterest\\' => 
        array (
            0 => __DIR__ . '/..' . '/dirkgroenen/pinterest-api-php/src/Pinterest',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit77c17b7bb42fd5495cd89d22cf56981b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit77c17b7bb42fd5495cd89d22cf56981b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}