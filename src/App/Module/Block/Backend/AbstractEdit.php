<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Block\Backend;

use CrazyCat\Core\Block\Form\Renderer\Hidden as HiddenRenderer;
use CrazyCat\Core\Block\Form\Renderer\Password as PasswordRenderer;
use CrazyCat\Core\Block\Form\Renderer\Select as SelectRenderer;
use CrazyCat\Core\Block\Form\Renderer\Text as TextRenderer;
use CrazyCat\Core\Block\Form\Renderer\Textarea as TextareaRenderer;

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
     * @vra \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    public function __construct( Context $context, array $data = [] )
    {
        parent::__construct( $context, $data );

        $this->objectManager = $context->getObjectManager();
    }

    /**
     * @return \CrazyCat\Framework\App\Module\Model\AbstractModel
     */
    public function getModel()
    {
        return $this->registry->registry( 'current_model' );
    }

    /**
     * @param array $field
     * @param \CrazyCat\Framework\Data\Object $model
     * @return string
     */
    public function renderField( $field )
    {
        if ( isset( $field['renderer'] ) ) {
            $renderer = $this->objectManager->create( $field['renderer'] );
        }

        switch ( $field['type'] ) {

            case self::FIELD_TYPE_HIDDEN :
                $renderer = $this->objectManager->create( HiddenRenderer::class );
                break;

            case self::FIELD_TYPE_PASSWORD :
                $renderer = $this->objectManager->create( PasswordRenderer::class );
                break;

            case self::FIELD_TYPE_SELECT :
                $renderer = $this->objectManager->create( SelectRenderer::class );
                if ( isset( $field['source'] ) ) {
                    $field['options'] = $this->objectManager->create( $field['source'] )->toOptionArray();
                }
                break;

            case self::FIELD_TYPE_MULTISELECT :
                $renderer = $this->objectManager->create( SelectRenderer::class )->setIsMultiple( true );
                if ( isset( $field['source'] ) ) {
                    $field['options'] = $this->objectManager->create( $field['source'] )->toOptionArray();
                }
                break;

            case self::FIELD_TYPE_TEXT :
                $renderer = $this->objectManager->create( TextRenderer::class );
                break;

            case self::FIELD_TYPE_TEXTAREA :
                $renderer = $this->objectManager->create( TextareaRenderer::class );
                break;
        }

        return $renderer->addData( [ 'field' => $field, 'value' => $this->getModel()->getData( $field['name'] ) ] )->toHtml();
    }

    /**
     * Return array structure is like:
     * [
     *     [
     *         'name' => string,
     *         'label' => string,
     *         'type' => string,
     *         'renderer' => string,
     *         'source' => string,
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
