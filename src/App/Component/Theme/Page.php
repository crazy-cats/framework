<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Theme;

use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\Component\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Component\Theme;
use CrazyCat\Framework\App\Io\Http\Url;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Page extends \CrazyCat\Framework\App\Data\DataObject
{
    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Component\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    private $request;

    /**
     * @var \CrazyCat\Framework\App\Component\Theme
     */
    private $theme;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Url
     */
    private $url;

    /**
     * @var array|null
     */
    private $layout;

    /**
     * @var string[]
     */
    private $sectionsHtml;

    /**
     * @var Block[]
     */
    private $blocks = [];

    /**
     * @var array
     */
    private $cssInfo = ['files' => [], 'links' => []];

    public function __construct(
        Config $config,
        Url $url,
        ModuleManager $moduleManager,
        ObjectManager $objectManager,
        Request $request,
        Theme $theme
    ) {
        parent::__construct();

        $this->config = $config;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->theme = $theme;
        $this->url = $url;
    }

    /**
     * Layout B cover layout A
     *
     * @param array $layoutA
     * @param array $layoutB
     * @return array
     */
    private function mergeLayoutBlocks(array $layoutA, array $layoutB)
    {
        foreach ($layoutA as $sectionName => &$blockGroup) {
            if (isset($layoutB[$sectionName])) {
                $blockGroup = array_merge($blockGroup, $layoutB[$sectionName]);
                unset($layoutB[$sectionName]);
            }
        }
        return array_merge($layoutA, $layoutB);
    }

    /**
     * Layout B cover layout A
     *
     * @param array $layoutA
     * @param array $layoutB
     * @return array
     */
    private function mergeLayout(array $layoutA, array $layoutB)
    {
        $blocksA = empty($layoutA['blocks']) ? [] : $layoutA['blocks'];
        $blocksB = empty($layoutB['blocks']) ? [] : $layoutB['blocks'];

        $cssA = empty($layoutA['css']) ? [] : $layoutA['css'];
        $cssB = empty($layoutB['css']) ? [] : $layoutB['css'];

        return [
            'template' => empty($layoutB['template']) ? (empty($layoutA['template']) ? '1column' : $layoutA['template']) : $layoutB['template'],
            'css'      => array_merge($cssA, $cssB),
            'blocks'   => $this->mergeLayoutBlocks($blocksA, $blocksB)
        ];
    }

    /**
     * @param array $blocksLayout
     * @return array
     * @throws \ReflectionException
     */
    private function prepareBlocks(array $blocksLayout)
    {
        $blocks = [];
        foreach ($blocksLayout as $blockName => $blockInfo) {
            if (isset($this->blocks[$blockName])) {
                throw new \Exception(sprintf('Block name %s exists.', $blockName));
            }
            $data = isset($blockInfo['data']) ? $blockInfo['data'] : [];
            $data['name'] = $blockName;
            $data['children'] = empty($blockInfo['children']) ? [] : $this->prepareBlocks($blockInfo['children']);
            $this->blocks[$blockName] = $this->objectManager->create(
                $blockInfo['class'],
                ['data' => $data]
            );
            $blocks[] = $this->blocks[$blockName];
        }
        return $blocks;
    }

    /**
     * @param array $cssLayout
     * @return void
     */
    private function prepareCssScripts(array $cssLayout)
    {
        foreach ($cssLayout as $css) {
            $this->cssInfo['files'][] = $this->theme->getStaticPath($css);
            $this->cssInfo['links'][] = $this->theme->getStaticUrl($css);
        }
    }

    /**
     * @param string $layoutName
     * @param string $areaCode
     * @return \CrazyCat\Framework\App\Component\Module|null
     */
    private function getLayoutModule($layoutName, $areaCode)
    {
        foreach ($this->moduleManager->getEnabledModules() as $module) {
            if (isset($module['config']['routes'][$areaCode])) {
                if (strpos($layoutName, $module['config']['routes'][$areaCode] . '_') === 0) {
                    return $module;
                }
            }
        }
    }

    /**
     * @param string $blockName
     * @return Block|null
     */
    public function getBlock($blockName)
    {
        return $this->blocks[$blockName] ?? null;
    }

    /**
     * @param string $layoutName
     * @return array
     */
    public function getLayoutFromFile($layoutName)
    {
        $layoutFile = $this->theme->getData('dir') . DS . 'view' . DS . 'layouts' . DS . $layoutName . '.php';
        if (is_file($layoutFile) && ($layout = require $layoutFile) && is_array($layout)) {
            return $layout;
        }

        $areaCode = $this->theme->getData('config')['area'];
        if (($module = $this->getLayoutModule($layoutName, $areaCode))) {
            $layoutFile = $module->getData('dir') .
                DS . 'view' . DS . $areaCode . DS . 'layouts' . DS . $layoutName . '.php';
            if (is_file($layoutFile) && ($layout = require $layoutFile) && is_array($layout)) {
                return $layout;
            }
        }

        return [];
    }

    /**
     * @param array $layout
     * @return $this
     */
    public function setLayout(array $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getThemeUrl($path)
    {
        return $this->theme->getStaticUrl($path);
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getLayoutName($separator = '_')
    {
        return $this->request->getFullPath($separator);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getCssScripts()
    {
        if ($this->config->getValue($this->theme->getData('config')['area'])['merge_css']) {
            return '';
        } else {
            $scripts = '';
            foreach ($this->cssInfo['links'] as $cssLink) {
                $scripts .= '<link rel="stylesheet" type="text/css" media="all" href="' . $cssLink . "\" />\n";
            }
            return $scripts;
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->url->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->url->getCurrentUrl();
    }

    /**
     * @param string $path
     * @return string
     * @throws \Exception
     */
    public function getUrl($path)
    {
        return $this->url->getUrl($path);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function toHtml()
    {
        $layout = $this->mergeLayout(
            $this->getLayoutFromFile('default'),
            $this->getLayoutFromFile($this->getLayoutName())
        );
        if ($this->layout !== null) {
            $layout = $this->mergeLayout($layout, $this->layout);
        }
        $this->prepareCssScripts($layout['css']);
        $this->prepareBlocks($layout['blocks']);

        ob_start();
        $templateFile = $this->theme->getData('dir') . DS . 'view/templates/pages' . DS . $layout['template'] . '.php';
        if (is_file($templateFile)) {
            include $templateFile;
        } else {
            throw new \Exception(sprintf('Template file `%s` does not exist.', $templateFile));
        }
        return ob_get_clean();
    }
}
