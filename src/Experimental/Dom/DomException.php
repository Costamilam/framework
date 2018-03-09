<?php
/*
 * Inphinit
 *
 * Copyright (c) 2018 Guilherme Nascimento (brcontainer@yahoo.com.br)
 *
 * Released under the MIT license
 */

namespace Inphinit\Experimental\Dom;

class DomException extends Inphinit\Experimental\Exception
{
    /**
     * Raise an exception
     *
     * @param string $message
     * @param int    $trace
     * @return void
     */
    public function __construct($message = null, $trace = 1)
    {
        $err = \libxml_get_errors();

        $trace++;

        if ($message) {
            $message = $message;
        } elseif (isset($err[0]->message)) {
            $message = trim($err[0]->message);
        }

        if (empty($err[0]->file) === false && $err[0]->line > 0) {
            $this->file = preg_replace('#^file:/(\w+:)#i', '$1', $err[0]->file);
            $this->line = $err[0]->line;
            $trace = 0;
        }

        parent::__construct($message, $trace);
    }
}
