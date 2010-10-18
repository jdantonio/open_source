<?php

/**
 * Base class for models within the StatehouseNews module.
 **/
abstract class Model
{
    //--------------------------------------------------------------------------
    // Data Members
    //--------------------------------------------------------------------------

    // local pointers to global variables
    protected $_DB = NULL;
    protected $_DSP = NULL;
    protected $_FNS = NULL;
    protected $_IN = NULL;
    protected $_LANG = NULL;
    protected $_LOC = NULL;
    protected $_REGX = NULL;
    protected $_SESS = NULL;
    protected $_TMPL = NULL;

    protected $_errors;

    //-------------------------------------------------------------------------
    // Construction and Destruction
    //-------------------------------------------------------------------------

    public function __construct(/*mixed*/ $id = false)
    {
        global $DB, $DSP, $FNS, $IN, $LANG, $LOC, $REGX, $SESS, $TMPL;

        // get pointers to global data
        $this->_DB = $DB;
        $this->_DSP = $DSP;
        $this->_FNS = $FNS;
        $this->_IN = $IN;
        $this->_LANG = $LANG;
        $this->_LOC = $LOC;
        $this->_REGX = $REGX;
        $this->_SESS = $SESS;
        $this->_TMPL = $TMPL;

        $this->init();
    }

    public function __destruct()
    {
    }
    
    /**
     * Initialize the model object.
     **/
    protected function init() /*void*/
    {
        $this->_errors = array();
    }

    //-------------------------------------------------------------------------
    // Accessors
    //-------------------------------------------------------------------------
    
    /**
     * Get or set error messages into the internal errors array.
     **/
    public function errors(/*mixed*/ $errors = false) /*array*/
    {
        if ($errors)
        {
            if (! is_array($errors)) $errors = array($errors);
            foreach($errors as $error) $this->_errors[] = (string) $error;
        }
        return $this->_errors;
    }

    /**
     * The number of error messages in the internal error array.
     **/
    public function error_count() /*int*/
    {
        return count($this->_errors);
    }

    //-------------------------------------------------------------------------
    // Operations
    //-------------------------------------------------------------------------

    /**
     * Format string for MySQL datetime data.
     **/
    public function mysql_now()
    {
        return strftime('%Y-%m-%d %H:%M:%S');
    }

    /**
     * Return the Errno from the last database call.
     **/
    protected function db_errno()
    {
        return mysql_errno($this->_DB->conn_id);
    }

    /**
     * Return the Error from the last database call.
     **/
    protected function db_error()
    {
        return mysql_error($this->_DB->conn_id);
    }
    
    /**
     * Convert the model data into an associative array.
     **/
    public function to_a(/*bool*/ $sql_escape = false) /*array*/
    {
        return array();
    }

    /**
     * Set the model data values from an associative array. Array elements not
     * corresponding to object properties are ignored. Data is NOT saved to the
     * database when this function is called nor is the data validated.
     **/
    public function from_a(/*array*/ $data) /*void*/
    {
        $this->init();
    }

    /**
     * Validate the property values of the current object.
     **/
    public function validate() /*bool*/
    {
        return false;
    }

    /**
     * Save the current object properties to the database if, and only if, the
     * values are valid (calls the validate() method before saving).
     *
     * @see validate
     **/
    public function save() /*bool*/
    {
        return false;
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
