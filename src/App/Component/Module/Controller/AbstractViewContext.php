<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractViewContext extends AbstractContext
{
    /**
     * @var \CrazyCat\Framework\App\Io\Http\Cookies
     */
    protected $cookies;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Session\Messenger
     */
    protected $messenger;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    /**
     * @var \CrazyCat\Framework\App\Registry
     */
    protected $registry;

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

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Component\Language\Translator $translator,
        \CrazyCat\Framework\App\Component\Theme\Manager $themeManager,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\Io\Http\Cookies $cookies,
        \CrazyCat\Framework\App\Io\Http\Request $request,
        \CrazyCat\Framework\App\Io\Http\Session\AbstractSession $session,
        \CrazyCat\Framework\App\Io\Http\Session\Messenger $messenger,
        \CrazyCat\Framework\App\Io\Http\Url $url,
        \CrazyCat\Framework\App\Logger $logger,
        \CrazyCat\Framework\App\ObjectManager $objectManager,
        \CrazyCat\Framework\App\Registry $registry
    ) {
        parent::__construct($area, $config, $eventManager, $request, $logger, $objectManager);

        $this->cookies = $cookies;
        $this->messenger = $messenger;
        $this->registry = $registry;
        $this->session = $session;
        $this->themeManager = $themeManager;
        $this->translator = $translator;
        $this->url = $url;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Cookies
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Session\Messenger
     */
    public function getMessenger()
    {
        return $this->messenger;
    }

    /**
     * @return \CrazyCat\Framework\App\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Session\AbstractSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Theme\Manager
     */
    public function getThemeManager()
    {
        return $this->themeManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Language\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Url
     */
    public function getUrl()
    {
        return $this->url;
    }
}
