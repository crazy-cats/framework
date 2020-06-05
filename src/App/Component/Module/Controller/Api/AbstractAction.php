<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller\Api;

use CrazyCat\Framework\App\Io\Http\Response;
use CrazyCat\Framework\App\Data\DataObject;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Component\Module\Controller\AbstractAction
{
    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->response = $context->getResponse();
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function run()
    {
        $this->beforeRun();

        if (!($auth = $this->request->getHeader('authorization'))) {
            throw new \Exception('You do not have permission to access the resource.');
        }
        $verifyObj = new DataObject(['token_validated' => false]);
        foreach (preg_split('/\s*,\s*/', $auth) as $authStr) {
            list($type, $token) = array_pad(preg_split('/\s+/', $authStr), 2, null);
            if ($type == 'Bearer') {
                $this->eventManager->dispatch('verify_api_token', ['token' => $token, 'verify_object' => $verifyObj]);
                break;
            }
        }
        if (!$verifyObj->getData('token_validated')) {
            throw new \Exception('You do not have permission to access the resource.');
        }
        $this->execute();

        $this->afterRun();

        $this->response->setType(Response::TYPE_JSON)->send();
    }

    /**
     * @return void
     */
    abstract protected function execute();
}
