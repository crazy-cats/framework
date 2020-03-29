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
     * @return string
     */
    private function logError($message)
    {
        $this->logger->log($message, sprintf('errors/%s/%s.log', date('Y-m'), date('Y-m-d')));

        return $message;
    }

    /**
     * @param string $errNo
     * @param string $errStr
     * @param string $errFile
     * @param string $errLine
     */
    private function processCliError($errNo, $errStr, $errFile, $errLine)
    {
        echo $this->logError(sprintf("\nMeet error on line %s of file %s:\n%s\n\n", $errLine, $errFile, $errStr));
    }

    /**
     * @param string $errNo
     * @param string $errStr
     * @param string $errFile
     * @param string $errLine
     * @throws \ReflectionException
     */
    private function processHttpError($errNo, $errStr, $errFile, $errLine)
    {
        $httpRequest = $this->objectManager->get(HttpRequest::class);
        if ($this->area->getCode() == Area::CODE_API
            || $httpRequest->getParam(HttpRequest::AJAX_PARAM)
        ) {
            try {
                throw new \Exception(sprintf("Meet error on line %s of file %s:\n%s", $errLine, $errFile, $errStr));
            } catch (\Exception $e) {
                $httpResponse = $this->objectManager->get(HttpResponse::class);
                $httpResponse->setType(HttpResponse::TYPE_JSON)
                    ->setData(
                        [
                            'error'   => true,
                            'message' => $errStr,
                            'trace'   => $e->getMessage() . "\n" . $e->getTraceAsString()
                        ]
                    )
                    ->send();
                exit;
            }
        } else {
            try {
                throw new \Exception(sprintf("Meet error on line %s of file %s:\n%s", $errLine, $errFile, $errStr));
            } catch (\Exception $e) {
                echo sprintf('<pre>%s</pre>', $this->logError($e->getMessage() . "\n" . $e->getTraceAsString() . "\n"));
            }
        }
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
        return $this->area->isCli() ?
            $this->processCliError($errNo, $errStr, $errFile, $errLine) :
            $this->processHttpError($errNo, $errStr, $errFile, $errLine);
    }
}
