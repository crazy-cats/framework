<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Frontend;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractViewAction {

    /**
     * @var \CrazyCat\Framework\App\Session\Frontend
     */
    protected $session;

    public function __construct( Context $context )
    {
        parent::__construct( $context );

        $this->session = $context->getSession();
    }

}
