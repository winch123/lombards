<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Author: Tuğrul Topuz <tugrultopuz@gmail.com>                           |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Http\Client\Provider;

use Phalcon\Http\Client\Exception as HttpException;
use Phalcon\Http\Client\Provider\Exception as ProviderException;
use Phalcon\Http\Client\Request;
use Phalcon\Http\Client\Response;
use Phalcon\Http\Request\Method;

class Curl extends Request
{
    private $handle = null;

    public static function isAvailable()
    {
        return extension_loaded('curl');
    }

    public function __construct()
    {
        if (!self::isAvailable()) {
            throw new ProviderException('CURL extension is not loaded');
        }

        $this->handle = curl_init();
        $this->initOptions();
        parent::__construct();
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }

    public function __clone()
    {
        $request = new self;
        $request->handle = curl_copy_handle($this->handle);

        return $request;
    }

    private function initOptions()
    {
        $this->setOptions(array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 20,
            CURLOPT_HEADER          => true,
            CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT       => 'Phalcon HTTP/' . self::VERSION . ' (Curl)',
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30
        ));
    }

    /**
     *
     * @param array $option
     * @param mixed $value
     * @return bool
     */
    public function setOption($option, $value)
    {
        return curl_setopt($this->handle, $option, $value);
    }

    /**
     *
     * @param array $options
     * @return bool
     */
    public function setOptions($options)
    {
        return curl_setopt_array($this->handle, $options);
    }

    /**
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->setOption(CURLOPT_TIMEOUT, $timeout);
    }

    /**
     *
     * @param int $timeout
     */
    public function setConnectTimeout($timeout)
    {
        $this->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
    }

    /**
     *
     * @param array $customHeader
     * @param bool $fullResponse
     * @return Response
     * @throws HttpException
     */
    private function send($customHeader = array(), $fullResponse = false)
    {
        if (!empty($customHeader)) {
            $header = $customHeader;
        } else {
            $header = array();
            if (count($this->header) > 0) {
                $header = $this->header->build();
            }
            $header[] = 'Expect:';
        }

        $this->setOption(CURLOPT_HTTPHEADER, $header);

        $content = curl_exec($this->handle);

        if ($errno = curl_errno($this->handle)) {
            throw new HttpException(curl_error($this->handle), $errno);
        }

        $headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);

        $response = new Response();
        $response->header->parse(substr($content, 0, $headerSize));

        if ($fullResponse) {
            $response->body = $content;
        } else {
            $response->body = substr($content, $headerSize);
        }

        return $response;
    }

    /**
     * Prepare data for a cURL post.
     *
     * @param mixed   $params      Data to send.
     * @param boolean $useEncoding Whether to url-encode params. Defaults to true.
     *
     * @return void
     */
    private function initPostFields($params, $useEncoding = true)
    {
        if (is_array($params)) {
            foreach ($params as $param) {
                if (is_string($param) && preg_match('/^@/', $param)) {
                    $useEncoding = false;
                    break;
                }
            }

            if ($useEncoding) {
                $params = http_build_query($params);
            }
        }

        if (!empty($params)) {
            $this->setOption(CURLOPT_POSTFIELDS, $params);
        }
    }

    /**
     *
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $pass
     */
    public function setProxy($host, $port = 8080, $user = null, $pass = null)
    {
        $this->setOptions(array(
            CURLOPT_PROXY     => $host,
            CURLOPT_PROXYPORT => $port
        ));

        if (!empty($user) && is_string($user)) {
            $pair = $user;
            if (!empty($pass) && is_string($pass)) {
                $pair .= ':' . $pass;
            }
            $this->setOption(CURLOPT_PROXYUSERPWD, $pair);
        }
    }

    /**
     *
     * @param string $uri
     * @param array $params
     * @param array $customHeader
     * @param bool $fullResponse
     * @return Response
     */
    public function get($uri, $params = array(), $customHeader = array(), $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => Method::GET,
        ));

        return $this->send($customHeader, $fullResponse);
    }

    /**
     *
     * @param string $uri
     * @param array $params
     * @param array $customHeader
     * @param bool $fullResponse
     * @return Response
     */
    public function head($uri, $params = array(), $customHeader = array(), $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => Method::HEAD,
        ));

        return $this->send($customHeader, $fullResponse);
    }

    /**
     *
     * @param string $uri
     * @param array $params
     * @param array $customHeader
     * @param bool $fullResponse
     * @return Response
     */
    public function delete($uri, $params = array(), $customHeader = array(), $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => Method::DELETE,
        ));

        return $this->send($customHeader, $fullResponse);
    }

    /**
     *
     * @param string $uri
     * @param array $params
     * @param bool $useEncoding
     * @param array $customHeader
     * @param bool $fullResponse
     * @return Response
     */
    public function post($uri, $params = array(), $useEncoding = true, $customHeader = array(), $fullResponse = false)
    {
        $this->setOptions(array(
            CURLOPT_URL           => $this->resolveUri($uri),
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => Method::POST,
        ));

        $this->initPostFields($params, $useEncoding);

        return $this->send($customHeader, $fullResponse);
    }

    /**
     *
     * @param string $uri
     * @param array $params
     * @param bool $useEncoding
     * @param array $customHeader
     * @param bool $fullResponse
     * @return Response
     */
    public function customPost($uri, $params = array(), $useEncoding = true, $customHeader = array(), $fullResponse = false)
    {
        $this->setOptions(array(
            CURLOPT_URL           => $uri,
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => Method::POST,
        ));

        $this->initPostFields($params, $useEncoding);

        return $this->send($customHeader, $fullResponse);
    }

    /**
     *
     * @param string $uri
     * @param array $params
     * @param bool $useEncoding
     * @param array $customHeader
     * @param bool $fullResponse
     * @return Response
     */
    public function put($uri, $params = array(), $useEncoding = true, $customHeader = array(), $fullResponse = false)
    {
        $this->setOptions(array(
            CURLOPT_URL           => $this->resolveUri($uri),
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => Method::PUT,
        ));

        $this->initPostFields($params, $useEncoding);

        return $this->send($customHeader, $fullResponse);
    }
}
