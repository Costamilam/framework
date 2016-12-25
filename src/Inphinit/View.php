<?php
/*
 * Inphinit
 *
 * Copyright (c) 2016 Guilherme Nascimento (brcontainer@yahoo.com.br)
 *
 * Released under the MIT license
 */

namespace Inphinit;

class View
{
    private static $views = array();
    private static $sharedData = array();

    private static $force = false;

    /**
     * Force the View::render method to render at the time it is called
     *
     * @return void
     */
    public static function forceRender()
    {
        self::$force = true;
    }

    /**
     * Starts rendering of registered views. After calling this method call it will automatically execute View::forceRender().
     *
     * @return void
     */
    public static function dispatch()
    {
        $views = array_filter(self::$views);

        self::forceRender();

        if (empty($views) === false) {
            foreach ($views as $value) {
                self::render($value[0], $value[1]);
            }

            self::$views = $views = null;
        }
    }

    /**
     * Adds values that will be added as variables to the views that will be executed later
     *
     * @param  string        $key
     * @param  mixed         $value
     * @return void
     */
    public static function shareData($key, $value)
    {
        self::$sharedData[$key] = $value;
    }

    /**
     * Removes one or all values that have been added by View::shareData.
     *
     * @param  string|null        $key
     * @return void
     */
    public static function removeData($key = null)
    {
        if ($key === null) {
            self::$data = array();
        } else {
            self::$data[$key] = null;
            unset(self::$data[$key]);
        }
    }

    /**
     * Check if view exists in ./application/View/ folder
     *
     * @param  string        $view
     * @return boolean
     */
    public static function exists($view)
    {
        $path = INPHINIT_PATH . 'application/View/' . strtr($view, '.', '/') . '.php';
        return is_file($path) && \UtilsCaseSensitivePath($path);
    }

    /**
     * Register or render a View. If View is registred this method returns the index number from View
     *
     * @param  string        $view
     * @return integer|null
     */
    public static function render($view, array $data = array())
    {
        if (self::$force) {
            $data = self::$sharedData + $data;

            \UtilsSandboxLoader('application/View/' . strtr($view, '.', '/') . '.php', $data);

            $data = null;

            return null;
        }

        self::$views[] = array(strtr($view, '.', '/'), $data);
        return count(self::$views) - 1;
    }

    /**
     * Remove a registred View by index
     *
     * @param  integer        $view
     * @return void
     */
    public static function remove($index)
    {
        if (isset(self::$views[$index])) {
            self::$views[$index] = null;
        }
    }
}
