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
 * @link     https://crazy-cat.cn
 */
class ErrorHandler
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
     * @return void
     * @throws \ReflectionException
     */
    private function logError($message)
    {
        $this->logger->addError($message, sprintf('errors/%s/%s.log', date('Y-m'), date('Y-m-d')));
    }

    /**
     * @param string $errNo
     * @param string $errStr
     * @param string $errFile
     * @param string $errLine
     * @throws \ReflectionException
     */
    public function process($errNo, $errStr, $errFile, $errLine)
    {
        try {
            throw new \Exception(sprintf("Meet error on line %s of file %s:\n%s", $errLine, $errFile, $errStr));
        } catch (\Exception $e) {
            $error = $e->getMessage() . "\n" . $e->getTraceAsString();
            $this->logError($error);

            if ($this->area->isCli()) {
                echo $error;
            } else {
                $httpRequest = $this->objectManager->get(HttpRequest::class);
                if ($this->area->getCode() == Area::CODE_API
                    || $httpRequest->getParam(HttpRequest::AJAX_PARAM)
                ) {
                    $httpResponse = $this->objectManager->get(HttpResponse::class);
                    $httpResponse->setType(HttpResponse::TYPE_JSON)
                        ->setData(['error' => true, 'message' => $errStr, 'trace' => $error])
                        ->send();
                    exit;
                } else {
                    echo '<pre>' . $error . '</pre>';
                }
            }
        }
    }
}
