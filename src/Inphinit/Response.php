<?php
/*
 * Inphinit
 *
 * Copyright (c) 2017 Guilherme Nascimento (brcontainer@yahoo.com.br)
 *
 * Released under the MIT license
 */

namespace Inphinit;

class Response
{
    private static $httpCode;
    private static $headers = array();
    private static $dispatchedHeaders = false;

    /**
     * Define registered headers to response
     *
     * @return void
     */
    public static function dispatchHeaders()
    {
        $headers = self::$headers;

        if (empty($headers) === false) {
            $lastCode = null;

            self::$dispatchedHeaders = true;

            foreach ($headers as $value) {
                self::putHeader($value[0], $value[1], is_numeric($value[2]) ? $value[2] : null);
            }

            $headers = null;
        }
    }

    /**
     * Get registered headers
     *
     * @return array
     */
    public static function getHeaders()
    {
        return self::$headers;
    }

    /**
     * Get registered headers
     *
     * @param int  $code
     * @param bool $preventTrigger
     * @return array
     */
    public static function status($code = null, $preventTrigger = false)
    {
        if (self::$httpCode !== $code && is_int($code) && headers_sent() === false) {
            header('X-PHP-Response-Code: ' . $code, true, $code);

            self::$httpCode = $code;

            if (false === $preventTrigger) {
                App::trigger('changestatus', array($code, null));
            }

            return true;
        } elseif (self::$httpCode === null) {
            self::$httpCode = \UtilsStatusCode();
        }

        return self::$httpCode;
    }

    /**
     * Register a header and return your index, if `Response::dispatchHeaders`
     * was previously executed the header will be set directly and will not be
     * registered
     *
     * @param string $header
     * @param bool   $replace
     * @param int    $code
     * @return int|bool|void
     */
    public static function putHeader($header, $replace = true, $code = null)
    {
        if (self::$dispatchedHeaders) {
            if (is_numeric($code)) {
                header($header, $replace, $code);
                self::$httpCode = $code;
                App::on('changestatus', array($code, null));
            } else {
                header($header, $replace);
            }
            return null;
        }

        if (is_string($header) && is_bool($replace) && ($code === null || is_numeric($code))) {
            self::$headers[] = array($header, $replace, $code);
            return count(self::$headers) - 1;
        }

        return false;
    }

    /**
     * Remove registered header by index
     *
     * @param int $index
     * @return bool
     */
    public static function removeHeader($index)
    {
        if (self::$dispatchedHeaders === false && isset(self::$headers[$index])) {
            self::$headers[$index] = null;
            return true;
        }

        return false;
    }

    /**
     * Set header to cache page (or no-cache)
     *
     * @param int $seconds
     * @param int $lastModified
     * @return void
     */
    public static function cache($seconds, $lastModified = null)
    {
        $headers = array();

        if ($seconds < 1) {
            $g = gmdate('D, d M Y H:i:s');
            $headers['Expires: ' . $g . ' GMT'] = true;
            $headers['Last-Modified: ' . $g . ' GMT'] = true;
            $headers['Cache-Control: no-store, no-cache, must-revalidate'] = true;
            $headers['Cache-Control: post-check=0, pre-check=0'] = false;
            $headers['Pragma: no-cache'] = true;
        } else {
            $headers['Expires: ' . gmdate('D, d M Y H:i:s', REQUEST_TIME + $seconds) . ' GMT'] = true;
            $headers['Cache-Control: public, max-age=' . $seconds] = true;
            $headers['Pragma: max-age=' . $seconds] = true;

            if (is_int($lastModified)) {
                $headers['Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT'] = true;
            }
        }

        foreach ($headers as $key => $value) {
            self::putHeader($key, $value);
        }

        $headers = null;
    }

    /**
     * Force download current page
     *
     * @param string $name
     * @param int    $contentLength
     * @return void
     */
    public static function download($name, $contentLength = 0)
    {
        if (is_string($name)) {
            self::putHeader('Content-Transfer-Encoding: Binary');
            self::putHeader('Content-Disposition: attachment; filename="' . strtr($name, '"', '-') . '"');
        }

        if ($contentLength > 0) {
            self::putHeader('Content-Length: ' . $contentLength);
        }
    }

    /**
     * Set mime-type
     *
     * @param string $mime
     * @return void
     */
    public static function type($mime)
    {
        self::putHeader('Content-Type: ' . $mime);
    }
}
