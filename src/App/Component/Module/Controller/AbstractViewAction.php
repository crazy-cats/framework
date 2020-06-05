<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller;

use CrazyCat\Framework\App\Io\Http\Response;
use CrazyCat\Framework\App\Component\Language\Translator;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractViewAction extends AbstractAction
{
    /**
     * @var \CrazyCat\Framework\App\Io\Http\Cookies
     */
    protected $cookies;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    /**
     * @var \CrazyCat\Framework\App\Registry
     */
    protected $registry;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Session\Messenger
     */
    protected $messenger;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Session\AbstractSession
     */
    protected $session;

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

    /**
     * @var array|null
     */
    protected $layout;

    /**
     * @var string|null
     */
    protected $pageTitle;

    /**
     * @var string|null
     */
    protected $metaKeywords;

    /**
     * @var string|null
     */
    protected $metaDescription;

    /**
     * @var string|null
     */
    protected $metaRobots;

    /**
     * @var bool
     */
    protected $skipRunning = false;

    public function __construct(
        AbstractViewContext $context,
        \CrazyCat\Framework\App\Io\AbstractRequest $request
    ) {
        parent::__construct($context, $request);

        $this->cookies = $context->getCookies();
        $this->messenger = $context->getMessenger();
        $this->response = $request->getResponse();
        $this->registry = $context->getRegistry();
        $this->session = $context->getSession();
        $this->themeManager = $context->getThemeManager();
        $this->translator = $context->getTranslator();
        $this->url = $context->getUrl();
    }

    /**
     * @param string $pageTitle
     * @return $this
     */
    protected function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    /**
     * @param string|array $metaKeywords
     * @return $this
     */
    protected function setMetaKeywords($metaKeywords)
    {
        if (!is_array($metaKeywords)) {
            $metaKeywords = preg_split('/,\s*/', $metaKeywords);
        }
        $this->metaKeywords = implode(', ', array_unique($metaKeywords));
        return $this;
    }

    /**
     * @param string $metaDescription
     * @return $this
     */
    protected function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    /**
     * @param string $metaRobots
     * @return $this
     */
    protected function setMetaRobots($metaRobots)
    {
        $this->metaRobots = $metaRobots;
        return $this;
    }

    /**
     * @param string $themeName
     * @return $this
     * @throws \Exception
     */
    protected function setTheme($themeName)
    {
        $this->themeManager->setCurrentTheme($themeName);
        return $this;
    }

    /**
     * @param array $layout
     * @return $this
     */
    protected function setLayout(array $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @param string $layoutFile
     * @return $this
     * @throws \Exception
     */
    protected function setLayoutFile($layoutFile)
    {
        $this->layout = $this->themeManager->getCurrentTheme()->getPage()->getLayoutFromFile($layoutFile);
        return $this;
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function render()
    {
        $page = $this->themeManager->getCurrentTheme()->getPage();

        $this->eventManager->dispatch('page_render_before', ['page' => $page, 'action' => $this]);

        if ($this->layout !== null) {
            $page->setLayout($this->layout);
        }
        if ($this->pageTitle !== null) {
            $page->setData('page_title', $this->pageTitle);
        }
        if ($this->metaKeywords !== null) {
            $page->setData('meta_keywords', $this->metaKeywords);
        }
        if ($this->metaDescription !== null) {
            $page->setData('meta_description', $this->metaDescription);
        }
        if ($this->metaRobots !== null) {
            $page->setData('meta_robots', $this->metaRobots);
        }

        $this->response->setType(Response::TYPE_PAGE)->setBody($page->toHtml());
    }

    /**
     * @param string $path
     * @param array  $params
     * @return void
     */
    public function redirect($path, $params = [])
    {
        $this->response->setType(Response::TYPE_REDIRECT)->setData($this->url->getUrl($path, $params));
    }

    /**
     * @return $this
     */
    public function skipRunning()
    {
        $this->skipRunning = true;
        return $this;
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run()
    {
        $this->beforeRun();

        if (($langCode = $this->request->getParam(Translator::REQUEST_KEY))) {
            $this->translator->setLangCode($langCode);
            $this->cookies->setData(Translator::REQUEST_KEY, $langCode);
        } elseif (($langCode = $this->cookies->getData(Translator::REQUEST_KEY))) {
            $this->translator->setLangCode($langCode);
        }

        if (!$this->skipRunning) {
            /**
             * Theme manager initialization does NOT include setting current theme.
             *     We need to do something before executing the specified view action,
             *     such as setting current theme, initializing language etc..
             */
            profile_start('Initializing themes');
            $this->themeManager->init();
            profile_end('Initializing themes');

            $this->eventManager->dispatch('themes_init_after', ['theme_manager' => $this->themeManager]);

            profile_start('Execute action');
            $this->execute();
            profile_end('Execute action');
        }

        $this->afterRun();

        $this->response->send();
    }

    /**
     * @return void
     */
    abstract protected function execute();
}
