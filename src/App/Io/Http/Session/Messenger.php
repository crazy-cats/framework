<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http\Session;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Messenger extends AbstractSession
{
    public const NAME = 'messenger';

    /**
     * Message types
     */
    public const TYPE_NOTICE = 'notice';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_ERROR = 'error';

    /**
     * @param string $message
     * @param string $type
     * @return $this
     */
    public function addMessage($message, $type = self::TYPE_NOTICE)
    {
        $messages = $this->storage->getData($type) ?: [];
        $messages[] = $message;
        $this->storage->setData($type, $messages);
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function addSuccess($message)
    {
        $this->addMessage($message, self::TYPE_SUCCESS);
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function addError($message)
    {
        $this->addMessage($message, self::TYPE_ERROR);
        return $this;
    }

    /**
     * @param bool $clear
     * @return mixed
     */
    public function getMessages($clear = false)
    {
        $messages = $this->storage->getData();
        if ($clear) {
            $this->storage->clearData();
        }
        return $messages;
    }
}
