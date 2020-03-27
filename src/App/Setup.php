<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Component\Setup as ComponentSetup;
use CrazyCat\Framework\App\Data\DataObject;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Setup
{
    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    public function __construct(Area $area)
    {
        $this->area = $area;
    }

    /**
     * @param array|string $settings
     * @param string       $path
     */
    private function getInputSettings(&$settings, $path = '')
    {
        if (is_array($settings)) {
            foreach ($settings as $field => &$childSettings) {
                $this->getInputSettings($childSettings, $path ? ($path . '/' . $field) : $field);
            }
        } elseif ($settings === null) {
            echo sprintf('Please set %s: ', $path);

            $input = fgets(STDIN);
            if (($input = trim($input)) != '') {
                $settings = $input;
            } else {
                $this->getInputSettings($settings, $path);
            }
        }
    }

    /**
     * @return void
     */
    private function setupFromCli()
    {
        echo "\nFollow the wizard to complete minimum configuration which will store at `app/config/env.php`.\n\n";

        $envSettins = [
            'global' => [
                'cache' => [
                    'type' => 'files'
                ],
                'session' => [
                    'type' => 'files'
                ],
                'db' => [
                    'default' => [
                        'type' => 'mysql',
                        'host' => null,
                        'username' => null,
                        'password' => null,
                        'database' => null,
                        'prefix' => ''
                    ]
                ],
                'lang' => 'en_US',
                'production_mode' => false
            ],
            'api' => [
                'token' => md5(date('Y-m-d H:i:s') . uniqid())
            ],
            'backend' => [
                'route' => null,
                'lang' => 'en_US',
                'theme' => 'default',
                'merge_css' => false,
                'cookies' => [
                    'duration' => 3600
                ]
            ],
            'frontend' => [
                'lang' => 'en_US',
                'theme' => 'default',
                'merge_css' => false,
                'cookies' => [
                    'duration' => 3600
                ]
            ]
        ];
        $this->getInputSettings($envSettins);
        file_put_contents(Config::FILE, sprintf("<?php\nreturn %s;", (new DataObject())->toString($envSettins)));

        echo "\n";
    }

    /**
     * @return void
     */
    private function setupFromHttp()
    {
        exit('Please run `index.php` in CLI to complete the setup.');
    }

    /**
     * @return void
     */
    public function launch()
    {
        foreach ([Config::DIR, ComponentSetup::DIR_APP_MODULES, ComponentSetup::DIR_APP_THEMES] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        if ($this->area->isCli()) {
            $this->setupFromCli();
        } else {
            $this->setupFromHttp();
        }
    }
}
