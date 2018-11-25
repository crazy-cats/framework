<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Messenger extends AbstractSession {

    const NAME = 'messenger';

    /**
     * Message types
     */
    const TYPE_NOTICE = 'notice';
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';

    /**
     * @param string $message
     * @param string $type
     * @return $this
     */
    public function addMessage( $message, $type = self::TYPE_NOTICE )
    {
        $messages = $this->storage->getData( $type ) ?: [];
        $messages[] = $message;
        $this->storage->setData( $type, $messages );
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function addSuccess( $message )
    {
        $this->storage->setData( self::TYPE_SUCCESS, $message );
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function addError( $message )
    {
        $this->storage->setData( self::TYPE_ERROR, $message );
        return $this;
    }

    /**
     * @param boolean $clear
     * @return mixed
     */
    public function getMessages( $clear = false )
    {
        $messages = $this->storage->getData();
        if ( $clear ) {
            $this->storage->clearData();
        }
        return $messages;
    }

}
