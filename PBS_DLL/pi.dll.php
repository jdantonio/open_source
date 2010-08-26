<?php
//******************************************************************************
// Class Dll
// http://expressionengine.com/docs/development/plugins.html 
//******************************************************************************

// EE Plugin Control Panel Information
$plugin_info = array(
    'pi_name' => 'PBS Digital Learning Library (DLL)',
    'pi_version' => '0.1',
    'pi_author' => "Jerry D'Antonio",
    'pi_author_url' => 'http://www.ideastream.org',
    'pi_description' => 'EE 1.6.x plugin for sending/parsing queries to the PBS DLL.',
    'pi_usage' => Npr::usage()
);

/**
 * ExpressionEngine plugin for interfacing with the NPR API.
 **/
class Dll
{
    //--------------------------------------------------------------------------
    // ExpressionEngine Data Members
    //--------------------------------------------------------------------------

    public $return_data = '';

    // local pointers to ExpressionEngine global variables
    protected $_DB = NULL;
    protected $_DSP = NULL;
    protected $_FNS = NULL;
    protected $_IN = NULL;
    protected $_LANG = NULL;
    protected $_LOC = NULL;
    protected $_REGX = NULL;
    protected $_SESS = NULL;
    protected $_TMPL = NULL;

    //--------------------------------------------------------------------------
    // cURL Data Members
    //--------------------------------------------------------------------------

    // class constants
    const CURL_TIMEOUT = 10;

    // cURL handle
    private $_curl;

    // error variables
    private $_error;
    private $_errno;
    private $_response;
    private $_status;
    
    //--------------------------------------------------------------------------
    // Construction and Destruction
    //--------------------------------------------------------------------------
    
    /**
     * Constructor.
     **/
    function Dll()
    {
        $this->__construct();
    }

    /**
     * Constructor. Maps ExpressionEngine global variables to object
     * member variables. Also initializes the cURL library for network
     * communications.
     **/
    public function __construct()
    {
        global $DB, $DSP, $FNS, $IN, $LANG, $LOC, $REGX, $SESS, $TMPL;

        $this->_DB = $DB;
        $this->_DSP = $DSP;
        $this->_FNS = $FNS;
        $this->_IN = $IN;
        $this->_LANG = $LANG;
        $this->_LOC = $LOC;
        $this->_REGX = $REGX;
        $this->_SESS = $SESS;
        $this->_TMPL = $TMPL;

        $this->_curl = FALSE;
        $this->init();
    }
    
    /**
     * Destructor. Closes cURL connections and releases cURL library.
     **/
    public function __destruct()
    {
        if ($this->is_init()) curl_close($this->_curl);
    }

    //--------------------------------------------------------------------------
    // Operations
    //--------------------------------------------------------------------------

    //--------------------------------------------------------------------------
    // Messaging Utilities
    //--------------------------------------------------------------------------
     
    //---------------------------------------------------------------------------
    // cURL Operations
    //---------------------------------------------------------------------------
    
    /**
     * Set the internal data members to valued representing a bad handle error.
     **/
    private function set_bad_handle() /*void*/ {
        $this->_response = '';
        $this->_errno = CURLM_BAD_HANDLE;
        $this->_error = 'The passed-in handle is not a valid cURL handle.';
        $this->_status = 0;
    }

    /**
     * Has the cURL library been correctly initialized?
     *
     * @return True if initialized else false.
     **/
    public function is_init() /*bool*/ {
        return $this->_curl !== FALSE;
    }

    /**
     * Close the current cURL handle and initialize the object anew.
     *
     * @see close
     * @see init
     *
     * @return True if initialization is successful else false.
     **/
    private function reset() /*bool*/ {
        $this->close();
        return $this->init();
    }
    
    /**
     * Initilize the internal cURL handle. Will not do anything if the object
     * has already been initialized.
     *
     * @return True if initialization is successful else false.
     **/
    private function init() /*bool*/ {
    
        if (! $this->is_init()) {
    
            // initialize a new session
            $this->_curl = curl_init();
    
            // set common options on success
            if ($this->_curl !== FALSE) {
                curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT);
            }
            else
            {
                $this->_response = '';
                $this->_errno = CURLE_FAILED_INIT;
                $this->_error = 'Failed to initialize the cURL session.';
                $this->_status = 0;
            }
        }
    
        // return a boolean
        return $this->is_init();
    }
    
    /**
     * Close the internal cURL handle and "zero" all internal data values.
     **/
    private function close() /*void*/ {
    
        // close an open session if it exists
        if ($this->is_init()) {
            curl_close($this->_curl);
            $this->_curl = FALSE;
        }
    
        // reset the error variables
        $this->_error = '';
        $this->_errno = CURLE_OK;
        $this->_response = '';
        $this->_status = 0;
    }
    
    /**
     * Set a cURL option.
     *
     * @link http://www.php.net/manual/en/function.curl-setopt.php 
     *
     * @param $option The defined constant for the option being set.
     * @param $value The value the option is to be set to.
     *
     * @return TRUE if successful else FALSE.
     **/
    private function setopt(/*int*/ $option, /*mixed*/ $value) /*BOOL*/ {
        if ($this->_curl) {
            return curl_setopt($this->_curl, (int)$option, $value);
        } else {
            return FALSE;
        }
    }
    
    /**
     * Convenience override of the underlying curl_getinfo method.
     *
     * @link http://www.php.net/manual/en/function.curl-getinfo.php 
     *
     * @param $opt The specific option to be retrieved or null for all options.
     *
     * @return A string when $opt is not null else an associative array.
     **/
    private function getinfo(/*int*/ $opt = null) /*mixes*/ {
        if (! $this->is_init()) {
            return null;
        } else if ($opt == null) {
            return curl_getinfo($this->_curl);
        } else {
            return curl_getinfo($this->_curl, $opt);
        }
    }
    
    /**
     * Execute the cURL command with the given parameters.
     *
     * @return true on success else false.
     **/
    private function exec() /*bool*/ {
        if ($this->_curl) {
            $this->_response = curl_exec($this->_curl);
            $this->_errno = curl_errno($this->_curl);
            $this->_error = curl_error($this->_curl);
            $this->_status = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        } else {
            $this->set_bad_handle();
        }
        return $this->_errno == CURLE_OK;
    }
   
    //--------------------------------------------------------------------------
    // Plugin Usage
    //--------------------------------------------------------------------------
    
    function usage()
    {
        ob_start(); 
?>
<?php
        $buffer = ob_get_contents();        
        ob_end_clean(); 
        return $buffer;
    }
    // END
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
