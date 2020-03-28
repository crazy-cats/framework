<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Handler;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Io\Http\Request as HttpRequest;
use CrazyCat\Framework\App\Io\Http\Response as HttpResponse;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class ExceptionHandler
{
    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Logger
     */
    private $logger;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Logger $logger,
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        $this->area = $area;
        $this->logger = $logger;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $message
     * @return string
     */
    private function logException($message)
    {
        $this->logger->log($message, sprintf('errors/%s/%s.log', date('Y-m'), date('Y-m-d')));

        return $message;
    }

    /**
     * @param \Exception $exception
     */
    private function processCliException($exception)
    {
        echo $this->logException($exception->getMessage() . "\n" . $exception->getTraceAsString());
    }

    /**
     * @param \Exception $exception
     * @throws \ReflectionException
     */
    private function processHttpException($exception)
    {
        $httpRequest = $this->objectManager->get(HttpRequest::class);
        if ($this->area->getCode() == Area::CODE_API
            || $httpRequest->getParam(HttpRequest::AJAX_PARAM)
        ) {
            $httpResponse = $this->objectManager->get(HttpResponse::class);
            $httpResponse->setType(HttpResponse::TYPE_JSON)
                ->setData(
                    ['error' => true, 'message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
                )
                ->send();
            exit;
        } else {
            echo sprintf(
                '<pre>%s</pre>',
                $this->logException($exception->getMessage() . "\n" . $exception->getTraceAsString())
            );
        }
    }

    /**
     * @param \Exception $exception
     * @throws \ReflectionException
     */
    public function process($exception)
    {
        return $this->area->isCli() ?
            $this->processCliException($exception) :
            $this->processHttpException($exception);
    }
}
