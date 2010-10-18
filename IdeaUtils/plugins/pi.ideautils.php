<?php
/**
 *******************************************************************************
 * Module file for the ideastream Utilities (IdeaUtils) plugin
 *
 * This file must be placed in the
 * /system/plugins/ folder in your ExpressionEngine installation.
 *******************************************************************************
 */

//------------------------------------------------------------------------------
// ExpressionEngine Setup
//------------------------------------------------------------------------------

// Check for valid file request
if ( ! defined('EXT'))
{
	exit('Invalid file request');
}

// Display
$plugin_info = array(
	'pi_name' => 'IdeaUtils',
	'pi_version' => '1.0',
	'pi_author' => "Jerry D'Antonio",
	'pi_author_url' => 'http://www.ideastream.org/',
	'pi_description' => 'Various, random utilities used at ideastream.',
	'pi_usage' => IdeaUtils::usage()
);

//******************************************************************************
// Class IdeaUtils
//******************************************************************************

class IdeaUtils
{
	//--------------------------------------------------------------------------
	// Data Members
	//--------------------------------------------------------------------------

	const EMPTY_STRING = '';

	public $return_data  = self::EMPTY_STRING;

	// local pointers to global variables
	protected $_DB = NULL;
	protected $_DSP = NULL;
	protected $_FNS = NULL;
	protected $_IN = NULL;
	protected $_LANG = NULL;
	protected $_LOC = NULL;
	protected $_REGX = NULL;
	protected $_TMPL = NULL;

	//--------------------------------------------------------------------------
	// Construction
	//--------------------------------------------------------------------------

	/**
	 * PHP4 Constructor
	 *
	 * @see __construct()
	 */
	function IdeaUtils()
	{
		$this->__construct();
	}

	/**
	 * PHP 5 Constructor
	 */
	public function __construct()
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
	}

    /**
     * Destructor.
     */
    public function __destruct()
	{
    }

	//--------------------------------------------------------------------------
	// Template Methods
	//--------------------------------------------------------------------------

    /**
     * Very basic, one-page authentication.
     *
     * This tag is useful when a single page needs minimal password protection.
     * This was created because several clients wanted to password-protect a
     * single page with a single login/password to be used by everyone accessing
     * the page. The security needs in these cases are so mininal that creating
     * an EE member and implementing a login form is excessive. HTTP Basic
     * Authentication is sufficient. Placing this tag at the top of an EE
     * template will cause Apache to prompt the user for the required
     * credentials and will then check them.
     *
     * @note This function provides minimal security at best and sends the login
     * credentials in clear text. It should NOT be used to protect any truly
     * sensitive information.
     **/
    public function authenticate()
    {
        // start session and suppress notice
        $e = error_reporting('E_NONE');
        session_start();
        error_reporting($e);

        // get tag parameters
		$username = $this->_TMPL->fetch_param('username');
		$password = $this->_TMPL->fetch_param('password');
        $realm = $this->_TMPL->fetch_param('realm');

        // set login session variable name
        $sess = "__{$realm}__login__";

        if (! $username || ! $password || ! $realm)
        {
            // improperly configured tag
            header('HTTP/1.0 401 Unauthorized');
            echo('You are not authorized to view this page.');
            exit;
        }
        else if (! isset($_SERVER['PHP_AUTH_USER'])
                || ! isset($_SERVER['PHP_AUTH_PW'])
                || ! isset($_SESSION[$sess])
            )
        {
            // prompt for login
            header("WWW-Authenticate: Basic realm=\"{$realm}\"");
            header('HTTP/1.0 401 Unauthorized');
            $_SESSION[$sess] = true;
            echo('You are not authorized to view this page.');
            exit;
        }
        else
        {
            // get login credentials
            $username = strtolower($username);
            $user = strtolower(trim($_SERVER['PHP_AUTH_USER']));
            $pwd = trim($_SERVER['PHP_AUTH_PW']);

            // check credentials
            if ($user != $username || $pwd != $password)
            {
                // send error
                header('HTTP/1.0 401 Unauthorized');
                unset($_SESSION[$sess]);
                echo('You are not authorized to view this page.');
                exit;
            }
        }
    }

    /**
     * Ensures that the given page is only accessed via SSL. Redirects to the
     * requested URL over SSL or the given URL when SSL is not on.
     *
     * {exp:ideautils:require_ssl}
     *
     * {exp:ideautils:require_ssl path="url_for_redirection"}
     **/
    public function require_ssl()
    {
		$path = ($this->_TMPL->fetch_param('path') === FALSE ? $_SERVER['REQUEST_URI'] : $this->_TMPL->fetch_param('path'));

        $path = str_replace('&#47;', '/', $path);

        if (! array_key_exists('HTTPS', $_SERVER) ||  $_SERVER['HTTPS'] === '' || strtolower($_SERVER['HTTPS']) === 'off') {
            $this->_FNS->redirect('https://'. $_SERVER['SERVER_NAME'] . $path);
        }

        return FALSE;
    }

    /**
     * Creates a series of hidden fields for all of the data in the POST request.
     *
     * {exp:ideautils:hidden}
     */
	public function hidden()
    {
        $this->return_data = self::EMPTY_STRING;

        foreach ($_POST as $field => $value)
        {
            $this->return_data .= "<input type=\"hidden\" name=\"{$field}\" id=\"{$field}\" value=\"{$value}\" />\n";
        }

        return $this->return_data;
    }

    /**
     * Returns the value of the last segment in the current page URL.
     * 
     * {exp:ideautils:last_seg}
	 */
	public function last_seg()
    {
        if (is_array($this->_IN->SEGS) && count($this->_IN->SEGS) > 0)
        {
            $this->return_data = $this->_IN->fetch_uri_segment( count( $this->_IN->SEGS ) ); 
            return $this->return_data;
        }
    }

    /**
     * Returns the value of the last segment in the current page URL.
     * 
     * {exp:ideautils:last_segment}
	 */
	public function last_segment()
	{
		return $this->last_seg();
	}

    /**
     * Returns the value of the requested form field or an empty string.
     * 
     * {exp:ideautils:get_field field=""}
	 */
    public function get_field()
    {
        $data = self::EMPTY_STRING;

        $field = $this->_TMPL->fetch_param('field');

        if ($field !== FALSE && $this->_IN->GBL($field, 'POST') !== FALSE)
        {
            $data = $this->_IN->GBL($field, 'POST');
        }

        return $data;
    }

    /**
     * Searches the POST data for field names matching enclosed single variables 
     * and outputs the field value or an empty string if no field exists.
     */ 
    public function form_data()
    {
        $this->return_data = $this->_TMPL->tagdata;
        
        foreach ($this->_TMPL->var_single as $key => $val)
        {
            if (isset($_POST[$key]))
            {
                $this->return_data = $this->_TMPL->swap_var_single($val, $_POST[$key], $this->return_data);
            }
            else
            {
                $this->return_data = $this->_TMPL->swap_var_single($val, '', $this->return_data);
            }
        }

        return $this->return_data;
    }

    /**
     * Determines if a given checkbox has been checked. If so returns
     * 'checked="checked"' for keeping the field checked. If not,
     * returns an empty string.
     * 
     * {exp:ideautils:is_checked field=""}
	 */
    public function is_checked()
    {
        $data = self::EMPTY_STRING;

        $field = $this->_TMPL->fetch_param('field');

        if ($field !== FALSE && $this->_IN->GBL($field, 'POST') !== FALSE)
        {
            $data = 'checked="checked"';
        }

        return $data;
    }

    /**
     * Marshals the data in a web form and sends an email with the data.
     * The 'mailto' and 'subject' fields are required. The 'required' field
     * is pipe-delimited list of form fields that are required for successful
     * form submission.
     * 
     * {exp:ideautils:email_form_data mailto="" subject="" required=""}
     * 
     * {exp:ideautils:email_form_data mailto="" subject="" required=""}
     *   {if no_results}
     *     Houston, we have a problem.
     *   {/if}
     * {/exp:ideautils:email_form_data}
     */ 
    public function email_form_data()
    {
        $this->return_data = FALSE;

		$mailto   = $this->_TMPL->fetch_param('mailto');
		$subject  = $this->_TMPL->fetch_param('subject');
		$required = $this->_TMPL->fetch_param('required');

        $ok = TRUE;
        if ($required !== FALSE) $ok = $this->_check_required($required);

        if (   $ok === FALSE
            || $mailto === FALSE
            || $subject === FALSE
            || $failure_page === FALSE
            || $this->_send_email($mailto, $subject, $_POST) === FALSE
        )
        {
            $this->return_data = $this->_TMPL->no_results();
        }
        else
        {
            $this->return_data = $this->_TMPL->tagdata;
        }

        return $this->return_data;
    }

    /**
     * Processes files uploaded by a web form. The 'types' paramater is a
     * pipe-delimited list of file extensions. Only files with matching
     * extensions will be uploaded. The 'upload_path' parameter is the path
     * on the server will successfully uploaded files will be moved to.
     * 
     * This tag can also perform all of the same processing as the email_form_data
     * Tag. This functionality is enabled when the 'mailto' parameter is provided.
     * All parameters allowed and required by that tag are also allowed any required
     * by thyis tag when that functionality is enabled.
     * 
     * {exp:ideautils:upload_file types="" upload_path=""}
     *   {name}, {type}, {size}, {error}<br />
     *   {if no_results}
     *     File upload unavailable at this time.
     *   {/if}
     * {/exp:ideautils:upload_file}
     *
     * {exp:ideautils:upload_file types="" upload_path="" mailto="" subject="" required=""}
     *   {name}, {type}, {size}, {error}<br />
     * {/exp:ideautils:upload_file}
     */ 
    public function upload_file()
    {
        $this->return_data = FALSE;

		$types       = $this->_TMPL->fetch_param('types');
		$upload_path = $this->_TMPL->fetch_param('upload_path');

		$mailto   = $this->_TMPL->fetch_param('mailto');
		$subject  = $this->_TMPL->fetch_param('subject');
		$required = $this->_TMPL->fetch_param('required');

        $ok = TRUE;
        if ($required !== FALSE) $ok = $this->_check_required($required);

        if ($types === FALSE || strlen($types) == 0 || $upload_path === FALSE || $this->_check_files_global() === FALSE)
        {
            $this->return_data = $this->_TMPL->no_results();
        }
        else if ($mailto !== FALSE && ($subject === FALSE || $ok === FALSE))
        {
            $this->return_data = $this->_TMPL->no_results();
        }
        else
        {
            $this->return_data = self::EMPTY_STRING;

            // retrieve file type extensions
            $types = explode('|', $types);

            // unescape EE's mangling of the file path
            $upload_path = str_ireplace('&#47;', '/', $upload_path);

            // check the upload path for trailing slash
            if (substr_compare($upload_path, '/', -1, 1) != 0)
            {
                $upload_path .= '/';
            }

            // process all uploaded files
            foreach ($_FILES as $file)
            {
                // check for empty upload fields
                if (strlen($file['tmp_name']) == 0) break;

                // compare to all allowed file extensions
                $ok = FALSE;
                foreach ($types as $type)
                {
                    // check the file name against current extension
                    if (substr_compare($file['name'], $type, -strlen($type), strlen($type), true) == 0)
                    {
                        $ok = TRUE;
                        break;
                    }
                }

                // check upload success
                if ($file['error'] === UPLOAD_ERR_OK)
                {
                    // check file type result
                    if ($ok === TRUE)
                    {
                        // replace all non-word characters with underscores
                        $dest = preg_replace('/[^A-Za-z0-9_\.]/', '_', $file['name']);

                        // set the destination file name and path
                        $dest = $upload_path . strtolower($dest);

                        // move the file
                        if (FALSE === move_uploaded_file($file['tmp_name'], $dest))
                        {
                            $file['error'] = 'File Upload Error';
                        }
                        else
                        {
                            $file['error'] = 'Success';
                        }
                    }
                    else
                    {
                        $file['error'] = 'File Type Not Supported';
                    }
                }
                else
                {
                    $file['error'] = 'File Upload Error';
                }

                // process the template with the results
                unset($file['tmp_name']);
                $tagdata = $this->_TMPL->tagdata;
                foreach ($file as $key => $value)
                {
                    $tagdata = $this->_TMPL->swap_var_single($key, trim($value), $tagdata);
                }
                $this->return_data .= $tagdata;
            }

            // if necessary send the email
            if ($mailto !== FALSE)
            {
                $this->_send_email($mailto, $subject, $_POST, $_FILES);
            }
        }

        return $this->return_data;
    }

	//--------------------------------------------------------------------------
	// Utility Methods
	//--------------------------------------------------------------------------

    /**
     * 
     * @link http://www.lost-in-code.com/programming/php-code/php-random-string-with-numbers-and-letters/
     **/
    private function _random_string(/*int*/ $length = 10) /*string*/
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
        $string = '';    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters))];
        }
        return $string;
    }

    /**
     * Checks the $_FILES global collection to see if any files have been provided
     * for upload. This is necessary because the $_FILES array will have one element
     * for every file field in the form, regardless of whether files were selected
     * for upload. The only way to determine if one or more files were selected
     * is to interrogate the array. This function provides TRUE/FALSE feedback
     * so that a processing function can return to the user if no files were
     * uploaded.
     * 
     * @return TRUE if one or more files have been provided for upload, else FALSE.
     */
    private function _check_files_global()
    {
        $ok = FALSE;

        foreach ($_FILES as $file)
        {
            if (strlen($file['name']) > 0)
            {
                $ok = TRUE;
                break;
            }
        }

        return $ok;
    }

    /**
     * Compares a list of required form fields to the contents of the $_POST
     * global array.
     *
     * @param $required A pipe-delimited list of required form fields.
     *
     * @return TRUE if all required fields have been submitted else FALSE.
     */
    private function _check_required(/*string*/ $required)
    {
        $ok = TRUE;
        $required = explode('|', $required);

        foreach($required as $field)
        {
            if (! array_key_exists($field, $_POST) || strlen($_POST[$field]) == 0)
            {
                $ok = FALSE;
                break;
            }
        }

        return $ok;
    }

    /**
     * Send an email with the contents of an array (normally the $_POST array).
     * Optionally, check the contents of a files array (normally the $_FILES array)
     * and include information about file uploads as well.
     *
     * @param $mailto The email address of the recipient.
     * @param $subject The subject line of the email.
     * @param $fields The array containing the field data (usually $_POST).
     * @param $files [Optional] The file data array (usually $_FILES)
     * 
     * @return TRUE/FALSE depending on the result of the mail() call.
     */
    private function _send_email(/*string*/ $mailto, /*string*/ $subject,
                                 /*array*/ $fields, /*array*/ $files = FALSE)

    {
        $mail_headers = sprintf("From: %s <do_not_reply@%s>\r\nX-Mailer: PHP/%s",
            $_SERVER['SERVER_NAME'], $_SERVER['SERVER_NAME'], phpversion());

        $message = "This is an automated email message. Do not reply.\r\n\r\n"
            . "A form has been submitted on your web site. The form data is below.\r\n\r\n";

        foreach ($fields as $field => $value)
        {
            $message .= "* {$field}: {$value}\r\n";
        }

        if ($files !== FALSE)
        {
            $file_message = self::EMPTY_STRING;

            foreach ($files as $field => $file)
            {
                if ($file['error'] == 0)
                {
                    $file_message .= "* '{$file['name']}' ({$file['type']}) [{$file['error']}]\r\n";
                }
            }

            if ($file_message != self::EMPTY_STRING)
            {
                $message .= "\r\nThe following files were uploaded:\r\n\r\n";
                $message .= $file_message;
            }
        }

        $message .= "\r\nCONFIDENTIAL:\r\nThe material in this e-mail is confidential. This e-mail is private and is intended only for the use of the individual(s) to whom it is addressed. If you are not the intended recipient, be advised that unauthorized use, disclosure, copying, distribution, or the taking of any action in reliance on this information is strictly prohibited. Do not forward this e-mail without permission. If you were erroneously listed as an addressee or otherwise received this transmission in error, please immediately notify us by telephone or return e-mail to arrange for return of this material to us, and destroy all copies of this e-mail and any attachments that remain on your computer system or in your possession.";

        // send the email
        return mail($mailto, $subject, $message, $mail_headers);
    }

	//--------------------------------------------------------------------------
	// Usage Method
	//--------------------------------------------------------------------------

	function usage()
	{
		ob_start();
?>
IdeaUtils API - Various random utilities used at ideastream.

----------

Places minimal, cleartext, HTTP Basic Authentication on a page.

{exp:ideautils:authenticate username="login" password="pwd" realm="where"}

----------

Ensures that the given page is only accessed via SSL. Redirects to the requested URL over SSL or the given URL when SSL is not on.

{exp:ideautils:require_ssl}

{exp:ideautils:require_ssl path="url_for_redirection"}

----------

Creates a series of hidden fields for all of the data in the POST request.

{exp:ideautils:hidden}

----------

Returns the value of the last segment in the current page URL.

{exp:ideautils:last_seg}

----------

Returns the value of the last segment in the current page URL.

{exp:ideautils:last_segment}

----------

Returns the value of the requested form field or an empty string.

{exp:ideautils:get_field field=""}

----------

Determines if a given checkbox has been checked. If so returns
'checked="checked"' for keeping the field checked. If not,
returns an empty string.

{exp:ideautils:is_checked field=""}

----------

Marshals the data in a web form and sends an email with the data.
The 'mailto' and 'subject' fields are required. The 'required' field
is pipe-delimited list of form fields that are required for successful
form submission.

{exp:ideautils:email_form_data mailto="" subject="" required=""}

{exp:ideautils:email_form_data mailto="" subject="" required=""}
  {if no_results}
    Houston, we have a problem.
  {/if}
{/exp:ideautils:email_form_data}

----------

Marshals the data in a web form and sends an email with the data.
The 'mailto' and 'subject' fields are required. The 'required' field
is pipe-delimited list of form fields that are required for successful
form submission.

{exp:ideautils:email_form_data mailto="" subject="" required=""}

{exp:ideautils:email_form_data mailto="" subject="" required=""}
  {if no_results}
    Houston, we have a problem.
  {/if}
{/exp:ideautils:email_form_data}

<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
// END CLASS
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
