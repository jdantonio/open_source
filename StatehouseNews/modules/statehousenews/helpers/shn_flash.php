<?php
//****************************************************************************
// Flash Convenience Functions
//****************************************************************************

/**
 * @see ShnFlash::notice()
 **/
function flash_notice() /*mixed*/
{
    $flash = ShnFlash::instance();
    if (func_num_args() == 0) {
        return $flash->notice();
    } else {
        $flash->notice(func_get_arg(0));
    }
}

/**
 * @see ShnFlash::errors()
 **/
function flash_errors(/*mixed*/ $data = null) /*mixed*/
{
    $flash = ShnFlash::instance();
    return $flash->errors($data);
}

/**
 * @see ShnFlash::clear_errors()
 **/
function flash_clear_errors() /*voif*/
{
    $flash = ShnFlash::instance();
    return $flash->clear_errors();
}

/**
 * @see ShnFlash::get()
 **/
function flash_get(/*string*/ $key) /*mixed*/
{
    $flash = ShnFlash::instance();
    return $flash->get($key);
}

/**
 * @see ShnFlash::set()
 **/
function flash_set(/*string*/ $key, /*string*/ $val) /*void*/
{
    $flash = ShnFlash::instance();
    return $flash->set($key, $val);
}

/**
 * @see ShnFlash::from_array()
 **/
function flash_from_array(/*array*/ $data) /*void*/
{
    $flash = ShnFlash::instance();
    return $flash->from_array($data);
}

/**
 * @see ShnFlash::to_array()
 **/
function flash_to_array() /*array*/
{
    $flash = ShnFlash::instance();
    return $flash->to_array();
}

/**
 * @see ShnFlash::clear()
 **/
function flash_clear(/*string*/ $key) /*void*/
{
    $flash = ShnFlash::instance();
    return $flash->clear($key);
}

/**
 * @see ShnFlash::reset()
 **/
function flash_reset() /*void*/
{
    $flash = ShnFlash::instance();
    return $flash->reset();
}

/**
 * @see ShnFlash::has()
 **/
function flash_has(/*string*/ $key) /*bool*/
{
    $flash = ShnFlash::instance();
    return $flash->has($key);
}

//****************************************************************************
// Class ShnFlash
//****************************************************************************

/**
 * A $_SESSION wrapper loosely based on the flash notice and error mechanisms
 * of Ruby on Rails. Provides consistent storage locations in the session for
 * notice messages, error messages, and other data. Error data is an array
 * of error messages. Notice is a single storage location generally used for
 * the most current message. The main flash storage is an associative array
 * that can hold any type of data required by the caller.
 **/
class ShnFlash {

    //-------------------------------------------------------------------------
    // Member Data
    //-------------------------------------------------------------------------

    // session keys
    const KEY = '__shn_flash_session_key__';
    const ERRORS = '__shn_flash_session_errors__';
    const NOTICE = '__shn_flash_session_notice__';

    const EMPTY_STRING = '';

    private static $_instance;  // singleton instance

    //-------------------------------------------------------------------------
    // Construction and Destruction
    //-------------------------------------------------------------------------
    
    /**
     * Obtain a reference to the singleton instance.
     *
     * @link http://php.net/manual/en/language.oop5.patterns.php 
     **/
    public static function instance()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    private function ShnFlash()
    {
        $this->__construct();
    }

    /**
     * Starts the session and creates the required session variables.
     **/
    private function __construct()
    {
        // suppress notice that session has already started
        $e = error_reporting('E_NONE');
        session_start();
        error_reporting($e);

        // initialize storage
        if (! isset($_SESSION[self::KEY])) $_SESSION[self::KEY] = array();
        if (! isset($_SESSION[self::ERRORS])) $_SESSION[self::ERRORS] = array();
        if (! isset($_SESSION[self::NOTICE])) $_SESSION[self::NOTICE] = self::EMPTY_STRING;
    }

    /**
     * Closes the sessison.
     **/
    public function __destruct()
    {
        session_write_close();
    }

    //-------------------------------------------------------------------------
    // Operations
    //-------------------------------------------------------------------------

    /**
     * Get or set the value(s) stored in flash-notice.
     **/
    public function notice() /*mixed*/
    {
        if (func_num_args() == 0) {
            return $_SESSION[self::NOTICE];
        } elseif (is_null(func_get_arg(0))) {
            $_SESSION[self::NOTICE] = self::EMPTY_STRING;
            return true;
        } else {
            $_SESSION[self::NOTICE] = (string) func_get_arg(0);
            return true;
        }
    }

    /**
     * Clear the errors array.
     **/
    public function clear_errors() /*void*/
    {
        $_SESSION[self::ERRORS] = array();
    }

    /**
     * Get/set error messages.
     **/
    public function errors(/*mixed*/ $data = null) /*mixed*/
    {
        if (is_null($data)) {
            return $_SESSION[self::ERRORS];
        } elseif (is_array($data)) {
            $_SESSION[self::ERRORS] = $data;
            return true;
        } else {
            $_SESSION[self::ERRORS] = array($data);
            return true;
        }
    }

    /**
     * Get data from flash storage.
     **/
    public function get(/*string*/ $key) /*mixed*/
    {
        if (isset($_SESSION[self::KEY][$key])) {
            return $_SESSION[self::KEY][$key];
        } else {
            return self::EMPTY_STRING;
        }
    }

    /**
     * Set data in flash storage.
     **/
    public function set(/*string*/ $key, /*string*/ $val) /*void*/
    {
        $_SESSION[self::KEY][$key] = $val;
    }

    /**
     * Set data in flash storage from array input.
     **/
    public function from_array(/*array*/ $data) /*void*/
    {
        foreach($data as $key => $val)
        {
            $_SESSION[self::KEY][$key] = $val;
        }
    }

    /**
     * Convert flash storage into an array.
     **/
    public function to_array() /*array*/
    {
        return $_SESSION[self::KEY];
    }

    /**
     * Clear a value in flash storage.
     **/
    public function clear(/*string*/ $key) /*void*/
    {
        unset($_SESSION[self::KEY][$key]);
    }

    /**
     * Reset all flash storage.
     **/
    public function reset() /*void*/
    {
        foreach(array_keys($_SESSION[self::KEY]) as $key)
        {
            unset($_SESSION[self::KEY][$key]);
        }
        $_SESSION[self::ERRORS] = array();
        $this->notice(null);
    }

    /**
     * Does flash storage contain the given key?
     **/
    public function has(/*string*/ $key) /*bool*/
    {
        return isset($_SESSION[self::KEY][$key]);
    }
}
?>
<?php
//******************************************************************************
// @author Jerry D'Antonio
// @see http://www.ideastream.org
// @copyright Copyright (c) ideastream
// @license http://www.opensource.org/licenses/mit-license.php
//******************************************************************************
// Copyright (c) ideastream
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
//******************************************************************************
?>

