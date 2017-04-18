<?php
/*
 * Inphinit
 *
 * Copyright (c) 2017 Guilherme Nascimento (brcontainer@yahoo.com.br)
 *
 * Released under the MIT license
 */

namespace Inphinit;

class Cache
{
    private $handle;
    private $cacheName;
    private $cacheTmp;
    private $isCache = false;
    private $isGetHead = false;
    private $noStarted = true;
    private $expires;
    private $modified;
    private static $needHeaders;

    /**
     * Create a cache instance by route path
     *
     * @param int    $expires
     * @param int    $modified
     * @param string $prefix
     * @param bool   $querystring
     * @return void
     */
    public function __construct($expires = 900, $modified = 0, $prefix = '', $querystring = false)
    {
        $filename = INPHINIT_PATH . 'storage/cache/output/';

        $path = \UtilsPath();

        $filename .= strlen($path) . '/' . sha1($path) . '/';

        $name = '';

        if (false === empty($prefix)) {
            $name = strlen($prefix) . '.' . sha1($prefix) . '/';
        }

        if ($querystring && ($qs = Request::query())) {
            $name .= strlen($qs) . '.' . sha1($qs);
        } else {
            $name .= 'cache';
        }

        $filename .= $name;
        $checkexpires = $filename . '.1';

        $this->cacheName = $filename;

        if (is_file($filename) && is_file($checkexpires)) {
            $this->isCache = file_get_contents($checkexpires) > REQUEST_TIME;

            if ($this->isCache && self::isGetHead()) {
                $etag = sha1_file($filename);

                if (self::match(REQUEST_TIME + $modified, $etag)) {
                    Response::putHeader('Etag: ' . $etag);
                    Response::cache($expires, $modified);
                    Response::dispatchHeaders();
                    App::stop(304);
                }
            }

            if ($this->isCache) {
                App::on('ready', array($this, 'show'));

                return null;
            }
        }

        $this->cacheTmp = Storage::temp();

        $tmp = fopen($this->cacheTmp, 'wb');

        if ($tmp === false) {
            return null;
        }

        $this->handle = $tmp;
        $this->expires = $expires;
        $this->modified = $modified === 0 ? REQUEST_TIME : $modified;

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (ob_start(array($this, 'write'), 1024)) {
            $this->noStarted = false;
            App::on('finish', array($this, 'finish'));
        }
    }

    /**
     * Check is HEAD or GET method
     *
     * @return bool
     */
    protected static function isGetHead()
    {
        if (self::$needHeaders !== null) {
            return self::$needHeaders;
        }

        return self::$needHeaders = Request::is('GET') || Request::is('HEAD');
    }

    /**
     * Write cache
     *
     * @return void
     */
    public function finish()
    {
        if ($this->isCache || $this->noStarted) {
            return null;
        }

        ob_end_flush();

        if ($this->handle) {
            fclose($this->handle);
        }

        if (App::hasError()) {
            return null;
        }

        Storage::put($this->cacheName);

        if (filesize($this->cacheTmp) > 0 && copy($this->cacheTmp, $this->cacheName)) {
            file_put_contents($this->cacheName . '.1', REQUEST_TIME + $this->expires);

            if (self::isGetHead()) {
                Response::putHeader('Etag: ' . sha1_file($this->cacheName));
                Response::cache($this->expires, $this->modified);
                Response::dispatchHeaders();
            }

            if (App::isReady()) {
                $this->show();
                return null;
            }

            App::on('ready', array($this, 'show'));
        }
    }

    /**
     * Check `HTTP_IF_MODIFIED_SINCE` and `HTTP_IF_NONE_MATCH` from server
     * If true you can send `304 Not Modified`
     *
     * @param string $modified
     * @param string $etag
     * @return bool
     */
    public static function match($modified, $etag = null)
    {
        $modifiedsince = Request::header('If-Modified-Since');

        if ($modifiedsince &&
            preg_match('/^[a-z]{3}[,] \d{2} [a-z]{3} \d{4} \d{2}[:]\d{2}[:]\d{2} GMT$/i', $modifiedsince) !== 0 &&
            strtotime($modifiedsince) == $modified) {
            return true;
        }

        $nonematch = Request::header('If-None-Match');

        return $nonematch && trim($nonematch) === $etag;
    }

    /**
     * Checks if page (from route) is already cached.
     *
     * @return bool
     */
    public function cached()
    {
        return $this->isCache;
    }

    /**
     * Write data in cache file.
     * This method returns the set value itself because the class uses `ob_start`
     *
     * @param string $data
     * @return string
     */
    public function write($data)
    {
        if ($this->handle !== null) {
            fwrite($this->handle, $data);
        }

        return '';
    }

    /**
     * Show cache content from current page (from route) in output
     *
     * @return void
     */
    public function show()
    {
        if (filesize($this->cacheName) > 524287) {
            File::output($this->cacheName);
        } else {
            readfile($this->cacheName);
        }
    }
}
