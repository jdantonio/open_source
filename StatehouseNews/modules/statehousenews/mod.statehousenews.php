<?php
//******************************************************************************
// Class Statehousenews
// http://expressionengine.com/docs/development/plugins.html 
//******************************************************************************

// load module components
require_once(dirname(__FILE__) . '/mcp.statehousenews.php');
require_once(dirname(__FILE__) . '/helpers/shn_flash.php');
require_once(dirname(__FILE__) . '/helpers/shn_importer.php');

class Statehousenews
{
    //--------------------------------------------------------------------------
    // Data Members
    //--------------------------------------------------------------------------

    const SITE_KEY = '4fac3e9b-37a4-45b5-a8c3-c103ced5c4bc';

    const USER_SESSION_KEY = '__statehousenews_user__';
    const STORY_SESSION_KEY = '__statehousenews_story__';

    const USER_TYPE_SHN = 1;
    const USER_TYPE_CP = 2;

    public $return_data = '';

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
    
    //--------------------------------------------------------------------------
    // Construction and Destruction
    //--------------------------------------------------------------------------
    
    function Statehousenews()
    {
        $this->__construct();
    }

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
    }
    
    public function __destruct()
    {
    }

    //--------------------------------------------------------------------------
    // Flash Operations
    //--------------------------------------------------------------------------

    /**
     * {exp:statehousenews:notice}
     **/
    public function notice()
    {
        return flash_notice();
    }

    /**
     * {exp:statehousenews:flash}
     *     {if var_1}Var 1: {var_1}{/if}
     *     {if var_2}Var 2: {var_2}{/if}
     *     {if var_3}Var 3: {var_3}{/if}
     * {/exp:statehousenews:flash}
     *
     * {exp:statehousenews:flash variable="var_1"}
     *
     * {exp:statehousenews:flash object="obj_1"}
     *
     * {exp:statehousenews:flash object="obj_1" variable="var_1"}
     **/
    public function flash()
    {
        $var = $this->_TMPL->fetch_param('variable');
        if (! $var) $var = $this->_TMPL->fetch_param('var');
        $obj = $this->_TMPL->fetch_param('object');
        if (! $obj) $obj = $this->_TMPL->fetch_param('obj');

        $tagdata = '';

        if ($obj && $var)
        {
            // if both object and variable
            $flash = flash_to_array();
            if (array_key_exists($obj, $flash)) {
                $tagdata = $flash[$obj][$var];
            }
        }
        elseif ($var)
        {
            // if just variable
            $tagdata = flash_get($var);
        }
        elseif ($obj)
        {
            // if just object
            $flash = flash_to_array();
            if (array_key_exists($obj, $flash)) {
                $tagdata = $this->process_var_singles($this->_TMPL->tagdata, $flash[$obj]);
            }
        }
        else
        {
            // if neither object or variable
            $tagdata = $this->process_var_singles($this->_TMPL->tagdata, flash_to_array());
        }

        return $tagdata;
    }

    /**
     * {exp:statehousenews_errors class="div css classes" id="div_id"}
     **/
    public function errors()
    {
        $class = ( ! $this->_TMPL->fetch_param('class')) ? 'errorExplanation' : $this->_TMPL->fetch_param('class');
        $id = ( ! $this->_TMPL->fetch_param('id')) ? 'errorExplanation' : $this->_TMPL->fetch_param('id');

        $errors = flash_errors();
        $count = count($errors);
        $tagdata = '';

        if ($count > 0)
        {
            $tagdata = "<div class=\"{$class}\" id= \"{$id}\">"
                . "<h2>{$count} error(s) occurred during processing</h2>"
                . "<p>The following errors were returned:</p>"
                . "<ul>";

            foreach($errors as $error) {
                $tagdata .= "<li>{$error}</li>";
            }

            $tagdata .= '</ul></div>';
        }

        flash_reset_errors();
        return $tagdata;
    }

    //--------------------------------------------------------------------------
    // Operations
    //--------------------------------------------------------------------------

    public function import()
    {
        $date_format = "g:i:s A";

        $validate_only = $this->_TMPL->fetch_param('validate_only');
        switch (strtolower($validate_only))
        {
        case 'yes' :
        case 'y' :
        case 'true' :
        case 't' :
            $validate_only = true;
            break;
        default :
            $validate_only = false;
            break;
        }

        $filepath = $this->_TMPL->fetch_param('file');
        $importer = new ShnImporter();
        $errors = array();
        
        $start = time();
        $count = $importer->import($filepath, $errors, $validate_only);
        $error_count = count($errors);
        $end = time();

        $diff = $end - $start;
        $diffm = floor($diff / 60);
        $diffs = $diff % 60;
        $start = date($date_format, $start);
        $end = date($date_format, $end);

        $tagdata = "<p>Starting import at {$start}.</p>";
        if ($validate_only) $tagdata .= "<p>Operating in validation-only mode. No records will be saved.</p>";
        $tagdata .= "<p>Importing data from file '{$filepath}'</p>";
        $tagdata .= "<p>Successfully imported {$count} records.</p>";
        $tagdata .= "<p>Import complete at {$end} ({$diffm} minutes and {$diffs} seconds elapsed time).</p>";
        $tagdata .= "<p>Generated {$error_count} errors during processing.</p>";
        if ($error_count > 0)
        {
            $tagdata .= "<p>A complete list of errors follows:</p>";
            $tagdata .= "<div>" . print_r($errors, true) . "</div>";
        }

        return $tagdata;
    }

    public function logout()
    {
        $this->clear_current_user();
        $this->redirect(true, $tagdata);
        return $tagdata;
    }

    public function if_logged_in()
    {
        $tagdata = '';
        $user = $this->get_current_user();

        if ($user)
        {
            $tagdata = $this->_TMPL->tagdata;
        }

        return $tagdata;
    }

    public function if_not_logged_in()
    {
        $tagdata = '';
        $user = $this->get_current_user();

        if (! $user)
        {
            $tagdata = $this->_TMPL->tagdata;
        }

        return $tagdata;
    }

    public function authenticate()
    {
        $user = false;
        $tagdata = '';

        // check for login cookie
        $user = $this->get_current_user();

        // check if logged in to CP
        if (! $user && $this->_SESS->userdata['admin_sess'])
        {
            $user = $this->_SESS->userdata;
        }

        // authenticate local user
        if (! $user)
        {
            // get POST data
            $email = $_POST['email'];
            $password = $_POST['password'];

            // authenticate
            $user = new ShnUser($email);
            if (! $user->authenticate($password)) $user = false;
        }

        // authenticate CP user
        if (! $user)
        {
            $user = $this->authenticate_cp_member($email, $password);
        }

        // if authenticated, save data
        if ($user)
        {
            $this->set_current_user($user);
        }
        else
        {
            flash_errors('The username and password you entered are not valid.');
        }

        // process response
        $this->redirect((bool) $user, $tagdata);
        return $tagdata;
    }

    public function upload()
    {
        $ok = true;
        flash_reset_errors();
        flash_clear(self::STORY_SESSION_KEY);

        // get the story configuration
        $config = new ShnConfig();

        // create a new story object
        $story = new ShnStory($_POST);
        $story->author_id($this->_SESS->userdata['member_id']);
        
        // check file upload
        if (! isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] != 0)
        {
            flash_errors("There was a problem uploading the file.");
            $ok = false;
        }

        // set the audio file name
        $upload = $_FILES['audio_file'];
        $story->audio_file($upload['name']);

        // check the file extension for allowed type
        if ($ok)
        {
            $filetypes = explode(',', $config->audio_file_types());
            $pattern = '/\.(';
            foreach ($filetypes as $ftype) $pattern .= strtolower(trim($ftype)) . '|';
            $pattern = preg_replace('/\|$/', '', $pattern);
            $pattern .= ')$/i';
            if (! preg_match($pattern, $story->audio_file()))
            {
                flash_errors("The file '{$story->audio_file()}' is not of an authorized type.");
                $ok = false;
            }
        }

        // validate story
        $ok = ($story->validate() && $ok);

        // move file to staging directory
        if ($ok)
        {
            // set the destination file path
            $dest = $config->upload_directory();
            if (substr_compare($dest, '/', -1, 1) != 0) $dest .= '/';
            $dest .= $story->audio_file();

            // perform the move
            if (FALSE === move_uploaded_file($upload['tmp_name'], $dest))
            {
                flash_errors("There was an error moving '{$upload['tmp_name']}' to '{$dest}'.");
                $ok = false;
            }
        }

        // save the story
        if ($ok && ! $story->save())
        {
            flash_errors("There was a problem saving the story.");
            $ok = false;
        }

        // process response
        if (! $ok)
        {
            flash_errors($story->errors());
            flash_set(self::STORY_SESSION_KEY, $story);
        }
        else
        {
            flash_clear(self::STORY_SESSION_KEY);
            flash_reset_errors();
        }
        $this->redirect($ok, $tagdata);
        return $tagdata;
    }

    //--------------------------------------------------------------------------
    // Form Helpers
    //--------------------------------------------------------------------------

    private function authenticate_cp_member(/*string*/ $username, /*string*/ $password) /*mixed*/
    {
        $member = false;
        $config = new ShnConfig();

        // create the sql statement
        $sql = "SELECT * FROM exp_members WHERE username like '{$username}' or email like '{$username}';";

        // query the member
        $rs = $this->_DB->query($sql);

        if ($rs->num_rows > 0)
        {
            // get the authorized groups
            $groups = explode(',', $config->cp_member_groups());

            // hash the password
            // EE cp.login.php function authenticate
            $password = $this->_FNS->hash(stripslashes($password))  ;

            // check each returned member
            foreach($rs->result as $row)
            {
                // check the password
                if ($password != $row['password']) break;

                // check the member's group
                foreach ($groups as $group)
                {
                    if (intval($group) == intval($row['group_id']))
                    {
                        $member = $row;
                        break;
                    }
                }
            }
        }

        return $member;
    }

    private function get_current_story() /*array*/
    {
        $story = flash_get(self::STORY_SESSION_KEY);
        if ($story instanceof ShnStory)
        {
            $story = $story->to_a();
        }
        else
        {
            $story = $_POST;
        }

        return $story;
    }

    public function form()
    {
        $action = $this->_TMPL->fetch_param('action');
        $class = $this->_TMPL->fetch_param('class');
        $style = $this->_TMPL->fetch_param('style');
        $name = $this->_TMPL->fetch_param('name');
        $id = $this->_TMPL->fetch_param('id');
        if (! $id) $id = $name;

        return "<form method=\"POST\" action=\"{$action}\" enctype=\"multipart/form-data\" name=\"{$name}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\">";
    }

    public function input()
    {
        $field = $this->_TMPL->fetch_param('field');

        $class = $this->_TMPL->fetch_param('class');
        $style = $this->_TMPL->fetch_param('style');
        $id = $this->_TMPL->fetch_param('id');
        if (! $id) $id = $field;

        $tagdata = '';

        $config = new ShnConfig();
        $story = $this->get_current_story();

        if ($field == 'reporter')
        {
            $tagdata = $this->reporter_select($config, $id, $class, $style);
        }
        else if ($field == 'station')
        {
            $tagdata = $this->station_select($config, $id, $class, $style);
        }
        else
        {
            switch ($config->get_field_type($field))
            {
            case ShnConfig::FIELD_TYPE_DATE :
                $tagdata = $this->date_select($field, $id, $class, $style);
                break;
            case ShnConfig::FIELD_TYPE_TEXT :
                $tagdata = "<input type=\"text\" name=\"{$field}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\" value=\"{$story[$field]}\" />";
                break;
            case ShnConfig::FIELD_TYPE_TEXTAREA :
                $tagdata = "<textarea name=\"{$field}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\">{$story[$field]}</textarea>";
                break;
            case ShnConfig::FIELD_TYPE_FILE :
                $tagdata = "<input type=\"file\" name=\"{$field}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\" value=\"{$story[$field]}\" />";
                break;
            case ShnConfig::FIELD_TYPE_CAT :
                $tagdata = $this->category_select($config, $field, $id, $class, $style);
                break;
            default :
                $tagdata = '';
                break;
            }
        }

        return $tagdata;
    }

    //--------------------------------------------------------------------------
    // Utility Methods
    //--------------------------------------------------------------------------

    protected function date_select(/*string*/ $field, /*string*/ $id = '',
                                   /*string*/ $class = '', /*string*/ $style = '') /*string*/
    {
        $tagdata = '';
        $story = $this->get_current_story();

        // month select
        $fld = $field . '_month';
        $sel = isset($story[$fld]) ? $story[$fld] : date('m');
        $tagdata .= "<select name=\"{$fld}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\">";
        for ($i = 1; $i <= 12; $i++)
        {
            $month = date('F', mktime(0, 0, 0, $i));
            $tagdata .= "<option value=\"{$i}\"";
            if ($sel == $i) $tagdata .= ' selected="selected"';
            $tagdata .= ">{$month}</option>";
        }
        $tagdata .= '</select>';
        
        // day select
        $fld = $field . '_day';
        $sel = isset($story[$fld]) ? $story[$fld] : date('d');
        $tagdata .= "<select name=\"{$fld}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\">";
        for ($i = 1; $i <= 31; $i++)
        {
            $tagdata .= "<option value=\"{$i}\"";
            if ($sel == $i) $tagdata .= ' selected="selected"';
            $tagdata .= ">{$i}</option>";
        }
        $tagdata .= '</select>';

        // year select
        $fld = $field . '_year';
        $sel = isset($story[$fld]) ? $story[$fld] : date('Y');
        $tagdata .= "<select name=\"{$fld}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\">";
        $year = getdate();
        $year = $year['year'];
        for ($i = $year-1; $i <= $year+1; $i++)
        {
            $tagdata .= "<option value=\"{$i}\"";
            if ($sel == $i) $tagdata .= ' selected="selected"';
            $tagdata .= ">{$i}</option>";
        }
        $tagdata .= '</select>';

        return $tagdata;
    }
    
    protected function reporter_select(/*ShnConfig*/ &$config, /*string*/ $id = '',
                                       /*string*/ $class = '', /*string*/ $style = '') /*string*/
    {
        $tagdata = '';
        $field = 'reporter';
        $story = $this->get_current_story();

        // retrieve all members
        $sql = 'SELECT * FROM ' . ShnUser::TABLE_NAME . ' WHERE enabled = 1 ORDER BY name ASC;';
        $rs = $this->_DB->query($sql);

        // retrieve current user
        $user = $this->get_current_user();

        // open the tag
        $tagdata = "<select name=\"{$field}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\">";

        // check for control panel user
        if ($user['type'] == self::USER_TYPE_CP)
        {
            $value = htmlentities($user['name']);
            $tagdata .= "<option selected=\"selected\" value=\"{$value}\">{$value}</option>";
        }

        // build option from the query results
        if ($rs->num_rows > 0)
        {
            foreach($rs->result as $row)
            {
                // check if row matches current user
                $sel = '';
                if ((isset($story[$field]) && $story[$field] == $row['id']) || $user['email'] == $row['email'])
                {
                    $sel = 'selected="selected"';
                }

                // determine the value
                $value = htmlentities($row['name']);

                // create the HTML
                $tagdata .= "<option {$sel} value=\"{$value}\">{$value}</option>";
            }
        }

        // close the tag
        $tagdata .= '</select>';

        return $tagdata;
    }
    
    protected function station_select(/*ShnConfig*/ &$config, /*string*/ $id = '',
                                      /*string*/ $class = '', /*string*/ $style = '') /*string*/
    {
        $tagdata = '';
        $field = 'station';
        $story = $this->get_current_story();

        // retrieve all members
        $sql = 'SELECT DISTINCT station FROM ' . ShnUser::TABLE_NAME . ' ORDER BY station ASC;';
        $rs = $this->_DB->query($sql);

        // retrieve current user
        $user = $this->get_current_user();

        // open the tag
        $tagdata = "<select name=\"{$field}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\">";

        // check for control panel user
        if ($user['type'] == self::USER_TYPE_CP)
        {
            $value = htmlentities($user['station']);
            $tagdata .= "<option selected=\"selected\" value=\"{$value}\">{$value}</option>";
        }

        // build option from the query results
        if ($rs->num_rows > 0)
        {
            foreach($rs->result as $row)
            {
                // check if row matches current user
                $sel = '';
                if ((isset($story[$field]) && $story[$field] == $row['id']) || $user['station'] == $row['station'])
                {
                    $sel = 'selected="selected"';
                }

                // determine the value
                $value = htmlentities($row['station']);

                // create the HTML
                $tagdata .= "<option {$sel} value=\"{$value}\">{$value}</option>";
            }
        }

        // close the tag
        $tagdata .= '</select>';

        return $tagdata;
    }
    
    protected function category_select(/*ShnConfig*/ &$config, /*string*/ $field, /*string*/ $id = '',
                                       /*string*/ $class = '', /*string*/ $style = '') /*string*/
    {
        $tagdata = '';
        $story = $this->get_current_story();

        // get the required category id
        $cat_id = $config->get_field_cat($field);
        
        // retrieve the category data
        $sql = "SELECT * FROM exp_categories WHERE group_id = {$cat_id} ORDER BY cat_order ASC;";
        $rs = $this->_DB->query($sql);

        // build the tag
        if ($rs->num_rows > 0)
        {
            $tagdata = "<select name=\"{$field}\" id=\"{$id}\" class=\"{$class}\" style=\"{$style}\">";

            foreach($rs->result as $row)
            {
                $sel = ($row['cat_id'] == $story[$field]) ? 'selected="selected"' : '';
                $tagdata .= "<option {$sel} value=\"{$row['cat_id']}\">{$row['cat_name']}</option>";
            }
        
            $tagdata .= '</select>';
        }

        return $tagdata;
    }

    protected function redirect_page($url = false)
    {
        if (! $url) $url = $_SERVER['HTTP_REFERER'];

$redirect_string = <<<REDIRECT
<HTML>
<HEAD>
<TITLE>redirect</TITLE>
<META HTTP-EQUIV="refresh" CONTENT="0;URL={$url}">
</HEAD>
<BODY onLoad="window.location.href='{$url}';">
<P>The page you have requested has moved. If you are not redirected please click
<A HREF="{$url}">here</A>.</P>
</BODY>
</HTML>
REDIRECT;

        return $redirect_string;
    }

    /**
     * Determine if a redirection response is required and if so send it.
     * The URL for the redirection, when required, is determined based on the
     * appropriate template paramaters. If the appropriate success/failure
     * template parameter is not available then no redirect will occur.
     *
     * @param $success Boolean indicating the success of the action for which
     *        there may be redirection. When true the success redirect segment,
     *        if available, is used. When false the failure redirect segment,
     *        if available, is used.
     *
     * @return Boolean indicating whether redirection will occur.
     **/
    protected function redirect(/*bool*/ $success, /*string*/ &$tagdata = false) /*bool*/
    {
        // get template parameters
        $success_segment = $failure_segment = FALSE;
        if (! $success_segment) $success_segment = $this->_TMPL->fetch_param('success_segment');
        if (! $success_segment) $success_segment = $this->_TMPL->fetch_param('success');
        if (! $success_segment) $success_segment = $this->_TMPL->fetch_param('redirect');
        if (! $failure_segment) $failure_segment = $this->_TMPL->fetch_param('error_segment');
        if (! $failure_segment) $failure_segment = $this->_TMPL->fetch_param('error');
        if (! $failure_segment) $failure_segment = $this->_TMPL->fetch_param('failure_segment');
        if (! $failure_segment) $failure_segment = $this->_TMPL->fetch_param('failure');
        if (! $failure_segment) $failure_segment = $this->_TMPL->fetch_param('fail');

        // check for full URL
        if ($success_segment && preg_match('/^http/i', $success_segment) == 0) {
            $success_segment = $this->_FNS->create_url($success_segment);
        }
        if ($failure_segment && preg_match('/^http/i', $failure_segment) == 0) {
            $failure_segment = $this->_FNS->create_url($failure_segment);
        }

        // process the redirect
        if ($success && $success_segment)
        {
            //$this->_FNS->redirect($success_segment);
            if ($tagdata !== false) $tagdata = $this->redirect_page($success_segment);
            return true;
        }
        else if (! $success && $failure_segment)
        {
            //$this->_FNS->redirect($failure_segment);
            if ($tagdata !== false) $tagdata = $this->redirect_page($failure_segment);
            return true;
        }
        else
        {
            return false;
        }
    }

    //--------------------------------------------------------------------------
    // User Session Methods
    //--------------------------------------------------------------------------

    protected function clear_current_user() /*void*/
    {
        flash_clear(self::USER_SESSION_KEY);
    }

    protected function set_current_user(/*mixed*/ &$user) /*void*/
    {
        $current_user = array(
            'name' => '',
            'station' => '',
            'email' => '',
            'type' => 0,
        );

        if ($user instanceof ShnUser)
        {
            $current_user['name'] = $user->name();
            $current_user['station'] = $user->station();
            $current_user['email'] = $user->email();
            $current_user['type'] = self::USER_TYPE_SHN;
        }
        elseif (is_array($user) && isset($user['member_id']) && $user['member_id'] > 0)
        {
            $current_user['name'] = $user['screen_name'];
            $current_user['station'] = 'ideastream';
            $current_user['email'] = $user['email'];
            $current_user['type'] = self::USER_TYPE_CP;
        }
        elseif (is_array($user) && isset($user['name']) && isset($user['station']) && isset($user['email']))
        {
            $current_user = $user;
        }
        else
        {
            $current_user = false;
        }

        if ($current_user)
        {
            flash_set(self::USER_SESSION_KEY, $current_user);
        }
        else
        {
            $this->clear_current_user();
        }
    }

    protected function get_current_user() /*ShnUser*/
    {
        return flash_get(self::USER_SESSION_KEY);
    }
}
?>
<?php
//******************************************************************************
// @author Jerry D'Antonio
// @see http://www.statehousenews.org
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
