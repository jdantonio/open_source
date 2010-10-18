<?php
//*****************************************************************************
// Class Statehousenews_CP
//*****************************************************************************

// ExpressionEngine Setup
if ( ! defined('EXT'))
{
    exit('Invalid file request');
}

// load module components
require_once(dirname(__FILE__) . '/models/_model.php');
require_once(dirname(__FILE__) . '/models/shn_user.php');
require_once(dirname(__FILE__) . '/models/shn_config.php');
require_once(dirname(__FILE__) . '/models/shn_story.php');
require_once(dirname(__FILE__) . '/views/__view_helpers.php');

class Statehousenews_CP
{
    //-------------------------------------------------------------------------
    // Data Members
    //-------------------------------------------------------------------------

    public $version = '0.1';

    public $module_name = 'Statehousenews';

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

    //-------------------------------------------------------------------------
    // Construction and Destruction
    //-------------------------------------------------------------------------

    public function Statehousenews_CP($switch = TRUE)
    {
        $this->__construct($switch);
    }

    public function __construct($switch = TRUE)
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

        // is the module installed?
        $query = $this->_DB->query("SELECT COUNT(*) AS count FROM exp_modules WHERE module_name = '$this->module_name'");

        // if not, abend
        if ($query->row['count'] == 0)
        {
            return;
        }

        // process commands
        if ($switch)
        {
            switch($this->_IN->GBL('P'))
            {
            case 'show_user' :
                $this->show_user();
                break;
            case 'create_user' :
                $this->create_user();
                break;
            case 'update_user' :
                $this->update_user();
                break;
            case 'index_users' :
                $this->index_users();
                break;
            case 'enable_user' :
                $this->set_user_enabled(true);
                break;
            case 'disable_user' :
                $this->set_user_enabled(false);
                break;
            case 'show_config' :
                $this->show_config();
                break;
            case 'update_config' :
                $this->update_config();
                break;
            case 'index' :
            default :
                $this->index();
                break;
            }
        }
    }
    
    public function __destruct()
    {
    }

    //-------------------------------------------------------------------------
    // Installation and Uninstallation
    //-------------------------------------------------------------------------

    public function statehousenews_module_install()
    {
        $sql = array();

        $sql[] = "INSERT INTO exp_modules (
            module_id,
            module_name,
            module_version,
            has_cp_backend
        ) VALUES (
            '',
            '$this->module_name',
            '$this->version',
            'y'
        )";

        $sql[] = ShnUser::CREATE_SQL;
        $sql[] = ShnConfig::CREATE_SQL;

        foreach ($sql as $query)
        {
            $this->_DB->query($query);
        }

        $config = new ShnConfig();
        $config->save(true);

        return true;
    }

    public function statehousenews_module_deinstall()
    {
        $sql = array();

        $query = $this->_DB->query("SELECT module_id FROM exp_modules WHERE module_name = '$this->module_name'");

        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";
        $sql[] = "DELETE FROM exp_modules WHERE module_name = '$this->module_name'";

        $sql[] = ShnUser::DROP_SQL;
        $sql[] = ShnConfig::DROP_SQL;

        foreach ($sql as $query)
        {
            $this->_DB->query($query);
        }

        return true;
    }

    //-------------------------------------------------------------------------
    // Control Panel Pages
    //-------------------------------------------------------------------------

    private function index($msg = '')
    {
        // check configuration
        $msg = $this->cp_check_config($msg);

        // display the CP header
        $this->cp_header('shn_breadcrumb_menu', $msg);

        // display the view
        //$this->_DSP->body .= $this->_DSP->view('index.php', false, TRUE);
        $this->_DSP->body .= view('index.php', false, TRUE);
    }

    private function index_users($msg = '')
    {
        // display the CP header
        $this->cp_header('shn_breadcrumb_users');

        // get all users
        $users = ShnUser::all();

        // display the view
        $vars = array('users' => $users);
        //$this->_DSP->body .= $this->_DSP->view('index_users.php', $vars, TRUE);
        $this->_DSP->body .= view('index_users.php', $vars, TRUE);

    }

    private function create_user($msg = '')
    {
        $redirect = false;

        // display form or process form submission?
        if ($this->_IN->GBL('process'))
        {
            // create the user
            $user = new ShnUser();
            $user->name($this->_IN->GBL('name'));
            $user->email($this->_IN->GBL('email'));
            $user->station($this->_IN->GBL('station'));
            $user->password($this->_IN->GBL('password'));
            $user->password_confirmation($this->_IN->GBL('password_confirmation'));

            // attempt to save
            if (! $user->save())
            {
                $this->_DSP->body .= $this->error_message_from_model($user);
            }
            else
            {
                $this->_FNS->redirect($this->cp_url('index_users').'&MSG=shn_msg_success');
                $redirect = true;
            }
        }

        if (! $redirect)
        {
            // display the CP header
            $this->cp_header('shn_breadcrumb_create_user');

            // display the view
            $user = new ShnUser();
            $vars = array('user' => $user);
            //$this->_DSP->body .= $this->_DSP->view('create_user.php', $vars, TRUE);
            $this->_DSP->body .= view('create_user.php', $vars, TRUE);
        }
    }

    private function update_user($msg = '')
    {
        $redirect = false;
        $error_msg = false;

        // create the user
        $user = ShnUser::find($this->_IN->GBL('id'));

        if (! is_null($user))
        {
            // display form or process form submission?
            if ($this->_IN->GBL('process'))
            {
                $user->name($this->_IN->GBL('name'));
                $user->email($this->_IN->GBL('email'));
                $user->station($this->_IN->GBL('station'));
                $user->password($this->_IN->GBL('password'));
                $user->password_confirmation($this->_IN->GBL('password_confirmation'));

                // attempt to save
                if (! $user->save())
                {
                    $error_msg = $this->error_message_from_model($user);
                }
                else
                {
                    $this->_FNS->redirect($this->cp_url('show_user').'&id='.$user->id().'&MSG=shn_msg_success');
                    $redirect = true;
                }
            }
        }
        else
        {
            $error_msg = $this->_DSP->error_message('shn_error_invalid_user_id');
        }

        if (! $redirect)
        {
            // display the CP header
            $this->cp_header('shn_breadcrumb_update_user');

            // display the error messages, if any
            if ($error_msg) $this->_DSP->body .= $error_msg;

            // display the view
            $vars = array('user' => $user);
            //$this->_DSP->body .= $this->_DSP->view('update_user.php', $vars, TRUE);
            $this->_DSP->body .= view('update_user.php', $vars, TRUE);
        }
    }

    private function set_user_enabled($enabled, $msg = '')
    {
        $msg = 'shn_msg_success';

        // create the user
        $user = ShnUser::find($this->_IN->GBL('id'));

        if (! is_null($user))
        {
            $user->enabled($enabled);

            // attempt to save
            if (! $user->save())
            {
                $msg = 'shn_error_save_failure';
            }
        }
        else
        {
            $msg = 'shn_error_invalid_user_id';
        }

        $this->_FNS->redirect($this->cp_url('index_users').'&id='.$user->id().'&MSG='.$msg);
    }

    private function show_user($msg = '')
    {
        // display the CP header
        $this->cp_header('shn_breadcrumb_show_user');

        // get the user id
        $id = $this->_IN->GBL('id');
        if (! $id || is_null($user = ShnUser::find($id))) 
        { 
            $this->_DSP->body .= $this->_DSP->error_message($this->_LANG->line('shn_error_invalid_user_id'));
            return;
        } 

        // display the view
        $vars = array('user' => $user);
        //$this->_DSP->body .= $this->_DSP->view('show_user.php', $vars, TRUE);
        $this->_DSP->body .= view('show_user.php', $vars, TRUE);
    }

    private function show_config($msg = '')
    {
        // check configuration
        $msg = $this->cp_check_config($msg);

        // display the CP header
        $this->cp_header('shn_breadcrumb_config', $msg);

        // get the configuration
        $config = new ShnConfig();

        // display the view
        $vars = array('config' => $config);
        //$this->_DSP->body .= $this->_DSP->view('show_config.php', $vars, TRUE);
        $this->_DSP->body .= view('show_config.php', $vars, TRUE);
    }

    private function update_config($msg = '')
    {
        $redirect = false;
        $error_msg = false;

        // get the configuration
        $config = new ShnConfig();

        // display form or process form submission?
        if ($this->_IN->GBL('process'))
        {
            // update the config from the POST data
            $config->from_a($_POST);

            // attempt to save
            if (! $config->save())
            {
                $error_msg = $this->error_message_from_model($config);
            }
            else
            {
                $this->_FNS->redirect($this->cp_url('show_config').'&MSG=shn_msg_success');
                $redirect = true;
            }
        }

        if (! $redirect)
        {
            // check configuration
            $msg = $this->cp_check_config($msg);

            // display the CP header
            $this->cp_header('shn_breadcrumb_config', $msg);

            // display the error messages, if any
            if ($error_msg) $this->_DSP->body .= $error_msg;

            // display the view
            $vars = array('config' => $config);
            //$this->_DSP->body .= $this->_DSP->view('update_config.php', $vars, TRUE);
            $this->_DSP->body .= view('update_config.php', $vars, TRUE);
        }
    }

    //-------------------------------------------------------------------------
    // Control Panel Helpers
    //-------------------------------------------------------------------------

    private function cp_check_config(/*string*/ $msg = '') /*string*/
    {
        $config = new ShnConfig();
        if (! $config->is_complete())
        {
            if (! empty($msg)) $msg .= '<br />';
            $msg .= $this->_LANG->line('shn_msg_incomplete_config');
        }

        return $msg;
    }
    
    private function error_message_from_model(/*Model*/ $model)
    {
        $message = '';
        $errors = $model->errors();
        foreach ($errors as $error) {
            $message .= "{$error}<br/>";
        }
        return $this->_DSP->error_message($message);
    }

    private function cp_url(/*string*/ $path, /*string*/ $msg = '') /*string*/
    {
        return BASE.'&C=modules&M=statehousenews&P='.$path; 
    }

    private function cp_header(/*string*/ $title, /*string*/ $msg = '') /*void*/
    {
        //  HTML Title and Navigation Crumblinks 
        $this->_DSP->title = $this->_LANG->line('statehousenews_module_name'); 
        $this->_DSP->crumb = $this->_DSP->anchor(BASE.
            AMP.'C=modules'.AMP.'M=statehousenews', $this->_LANG->line('statehousenews_module_name'));                                     
        $this->_DSP->crumb .= $this->_DSP->crumb_item($this->_LANG->line($title));     

        //  Page Heading 
        //$this->_DSP->body .= $this->_DSP->heading($this->_LANG->line($title));    

        // if necessary, display the message
        if ($this->_IN->GBL('MSG'))
        {
            $msg = $this->_LANG->line($this->_IN->GBL('MSG'));
        }
        if (! empty($msg)) 
        { 
            $this->_DSP->body .= "<div class='box'><div class='success'>{$msg}</div></div>\n\n";
        } 
    }

    private function cp_table_header(/*string*/ $title, /*array*/ $columns = false) /*void*/
    {
        if ($columns) {
            for ($i = 0; $i < count($columns); $i++)
            {
                $columns[$i] = $this->_LANG->line($columns[$i]);
            }
        }

        $this->_DSP->body .=
            $this->_DSP->div('tableHeading', 'left') .
            $this->_LANG->line($title) .
            $this->_DSP->div_c();

        $this->_DSP->body .=
            $this->_DSP->table('tableBorder', '0', '0', '100%') .
            $this->_DSP->tr() .
            $this->_DSP->td('tablePad'); 
               
        if ($columns) {
            $this->_DSP->body .=
                $this->_DSP->table('', '0', '', '100%') . 
                $this->_DSP->tr() . 
                $this->_DSP->table_qcell('tableHeadingAlt', $columns);
        }

        $this->_DSP->body .= $this->_DSP->tr_c(); 
    }

    private function cp_table_footer() /*void*/
    {
        $this->_DSP->body .= $this->_DSP->table_c(); 

        $this->_DSP->body .=
            $this->_DSP->td_c() .
            $this->_DSP->tr_c() .
            $this->_DSP->table_c();
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
