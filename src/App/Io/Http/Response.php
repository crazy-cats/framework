<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Response extends \CrazyCat\Framework\App\Io\AbstractResponse
{
    const TYPE_JSON = 'application/json';
    const TYPE_XML = 'application/xml';
    const TYPE_PAGE = 'text/html';
    const TYPE_PLAIN = 'text/plain';
    const TYPE_REDIRECT = 'redirect';

    /**
     * @var string
     */
    protected $body;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var string
     */
    protected $type = 'text/plain';

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setRedirect($url)
    {
        $this->type = self::TYPE_REDIRECT;
        $this->data = $url;
        $this->body = null;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return void
     */
    public function send()
    {
        switch ($this->type) {
            case self::TYPE_REDIRECT:
                header('location: ' . $this->data);
                break;

            case self::TYPE_JSON:
                $this->body = json_encode($this->data);
                header('Content-Type: ' . $this->type . '; charset=utf-8');
                break;

            case self::TYPE_PLAIN:
                header('Content-Type: ' . $this->type . '; charset=utf-8');
                break;

            default:
                header('Content-Type: ' . $this->type);
                break;
        }

        echo $this->body;
    }
}
