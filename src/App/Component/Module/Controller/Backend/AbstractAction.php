<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Backend;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractViewAction {

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Session\Backend
     */
    protected $session;

    public function __construct( Context $context )
    {
        parent::__construct( $context );

        $this->session = $context->getSession();
    }

}
