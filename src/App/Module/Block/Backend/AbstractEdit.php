<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Block\Backend;

/**
 * @category CrazyCat
 * @package CrazyCat\Core
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractEdit extends \CrazyCat\Framework\App\Module\Block\AbstractBlock {

    /**
     * field types
     */
    const FIELD_TYPE_HIDDEN = 'hidden';
    const FIELD_TYPE_MULTISELECT = 'multiselect';
    const FIELD_TYPE_PASSWORD = 'password';
    const FIELD_TYPE_SELECT = 'select';
    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_TEXTAREA = 'textarea';

    protected $template = 'CrazyCat\Core::edit';

    /**
     * @return \CrazyCat\Framework\App\Module\Model\AbstractModel
     */
    public function getModel()
    {
        return $this->registry->registry( 'current_model' );
    }

    /**
     * Return array structure is like:
     * [
     *     [
     *         'name' => string,
     *         'label' => string,
     *         'type' => string,
     *         'options' => array
     *     ]
     * ]
     *
     * @return array
     */
    abstract public function getFields();

    /**
     * @return string
     */
    abstract public function getActionUrl();
}
