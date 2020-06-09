<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Curl
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';

    /**
     * @param string            $method
     * @param string            $url
     * @param string|array|null $data
     * @param array             $headers
     * @return mixed
     * @throws \Exception
     */
    private function request($method, $url, $data = null, $headers = [])
    {
        $opts = [
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTP_VERSION   => 1,
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $headers
        ];

        switch ($method) {
            case self::METHOD_POST:
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $data;
                break;

            case self::METHOD_PUT:
                $opts[CURLOPT_POST] = 0;
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_POSTFIELDS] = $data;
                break;

            case self::METHOD_DELETE:
                $opts[CURLOPT_POST] = 0;
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $opts[CURLOPT_POSTFIELDS] = $data;
                break;

            case self::METHOD_GET:
                $opts[CURLOPT_POST] = 0;
                if (!empty($data)) {
                    $opts[CURLOPT_URL] .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
                }
                break;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception($error);
        }

        return $response;
    }

    /**
     * @param string            $url
     * @param string|array|null $data
     * @param array             $headers
     * @return mixed
     * @throws \Exception
     */
    public function get($url, $data = null, $headers = [])
    {
        return $this->request(self::METHOD_GET, $url, $data, $headers);
    }

    /**
     * @param string            $url
     * @param string|array|null $data
     * @param array             $headers
     * @return mixed
     * @throws \Exception
     */
    public function post($url, $data = null, $headers = [])
    {
        return $this->request(self::METHOD_POST, $url, $data, $headers);
    }

    /**
     * @param string            $url
     * @param string|array|null $data
     * @param array             $headers
     * @return mixed
     * @throws \Exception
     */
    public function put($url, $data = null, $headers = [])
    {
        return $this->request(self::METHOD_PUT, $url, $data, $headers);
    }

    /**
     * @param string            $url
     * @param string|array|null $data
     * @param array             $headers
     * @return mixed
     * @throws \Exception
     */
    public function delete($url, $data = null, $headers = [])
    {
        return $this->request(self::METHOD_DELETE, $url, $data, $headers);
    }
}
