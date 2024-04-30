<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfb0a381dc9e3e606194f03ccc35a4af9
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'Opis\\Uri\\' => 9,
            'Opis\\String\\' => 12,
            'Opis\\JsonSchema\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Opis\\Uri\\' => 
        array (
            0 => __DIR__ . '/..' . '/opis/uri/src',
        ),
        'Opis\\String\\' => 
        array (
            0 => __DIR__ . '/..' . '/opis/string/src',
        ),
        'Opis\\JsonSchema\\' => 
        array (
            0 => __DIR__ . '/..' . '/opis/json-schema/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Pondermatic\\Strategy11Challenge\\Admin' => __DIR__ . '/../..' . '/includes/class-admin.php',
        'Pondermatic\\Strategy11Challenge\\CLI_Clear_Cached_Response' => __DIR__ . '/../..' . '/includes/class-cli-clear-cached-response.php',
        'Pondermatic\\Strategy11Challenge\\Challenge_API' => __DIR__ . '/../..' . '/includes/class-challenge-api.php',
        'Pondermatic\\Strategy11Challenge\\Core' => __DIR__ . '/../..' . '/includes/class-core.php',
        'Pondermatic\\Strategy11Challenge\\Data_List_Table' => __DIR__ . '/../..' . '/includes/class-data-list-table.php',
        'Pondermatic\\Strategy11Challenge\\Images' => __DIR__ . '/../..' . '/includes/class-images.php',
        'Pondermatic\\Strategy11Challenge\\Shortcode' => __DIR__ . '/../..' . '/includes/class-shortcode.php',
        'Pondermatic\\Strategy11Challenge\\View_Data' => __DIR__ . '/../..' . '/includes/class-view-data.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfb0a381dc9e3e606194f03ccc35a4af9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfb0a381dc9e3e606194f03ccc35a4af9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfb0a381dc9e3e606194f03ccc35a4af9::$classMap;

        }, null, ClassLoader::class);
    }
}
