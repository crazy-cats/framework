<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Theme;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Block extends \CrazyCat\Framework\App\Data\DataObject
{
    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\Cache\Manager
     */
    protected $cacheFactory;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var \CrazyCat\Framework\App\Component\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\Registry
     */
    protected $registry;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var \CrazyCat\Framework\App\Component\Theme\Manager
     */
    protected $themeManager;

    /**
     * @var \CrazyCat\Framework\App\Component\Language\Translator
     */
    protected $translator;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Url
     */
    protected $url;

    public function __construct(Block\Context $context, array $data = [])
    {
        parent::__construct($data);

        $this->area = $context->getArea();
        $this->cacheFactory = $context->getCacheFactory();
        $this->eventManager = $context->getEventManager();
        $this->moduleManager = $context->getModuleManager();
        $this->registry = $context->getRegistry();
        $this->request = $context->getRequest();
        $this->themeManager = $context->getThemeManager();
        $this->translator = $context->getTranslator();
        $this->url = $context->getUrl();

        if (!empty($data['template'])) {
            $this->template = $data['template'];
        }

        $this->init();
    }

    /**
     * @return Block
     */
    protected function init()
    {
        return $this;
    }

    /**
     * @param string $template
     * @return string
     * @throws \Exception
     */
    protected function getAbsTemplatePath($template)
    {
        [$namespace, $filePath] = explode('::', $template);
        if (is_file(
            $file = $this->themeManager->getCurrentTheme()->getData('dir') .
                DS . 'view/templates/blocks' .
                DS . str_replace('\\', DS, $namespace) .
                DS . $filePath . '.php'
        )) {
            return $file;
        }
        if (($module = $this->moduleManager->getModule($namespace))) {
            $file = $module->getData('dir') .
                DS . 'view' .
                DS . $this->area->getCode() .
                DS . 'templates' . DS . $filePath . '.php';
            if (is_file($file)) {
                return $file;
            }
        }
        throw new \Exception(sprintf('Block template file %s does not exist.', $template));
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Theme\Page
     * @throws \Exception
     */
    public function getPage()
    {
        return $this->themeManager->getCurrentTheme()->getPage();
    }

    /**
     * @param string $url
     * @return string
     * @throws \Exception
     */
    public function getStaticUrl($url)
    {
        return $this->getPage()->getThemeUrl($url);
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
     * @return array
     */
    public function getChildren()
    {
        $children = $this->getData('children') ?: [];
        usort(
            $children,
            function ($a, $b) {
                $sortA = $a->hasData('sort') ? $a->getData('sort') : 9999;
                $sortB = $b->hasData('sort') ? $b->getData('sort') : 9999;
                return $sortA > $sortB ? 1 : ($sortA < $sortB ? -1 : 0);
            }
        );
        return $children;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function toHtml()
    {
        if (empty($this->template)) {
            return '';
        }

        ob_start();
        include $this->getAbsTemplatePath($this->template);
        return ob_get_clean();
    }
}
