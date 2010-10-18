<?php

require_once(dirname(__FILE__) . '/_model.php');

class ShnUser extends Model
{
    //--------------------------------------------------------------------------
    // Data Members
    //--------------------------------------------------------------------------

    const TABLE_NAME = 'exp_shn_users';

    const CREATE_SQL =
        "CREATE TABLE IF NOT EXISTS exp_shn_users (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(256) NOT NULL,
            station VARCHAR(256) NOT NULL,
            email VARCHAR(256) NOT NULL,
            pwd_site_key VARCHAR(50) NOT NULL,
            pwd_nonce VARCHAR(50) NOT NULL,
            pwd_hash VARCHAR(255) NOT NULL,
            enabled BOOL NOT NULL,
            failed_login_count INT UNSIGNED NOT NULL DEFAULT 0,
            last_request_at TIMESTAMP NULL,
            current_login_at TIMESTAMP NULL,
            last_login_at TIMESTAMP NULL,
            current_login_ip VARCHAR(15) NULL,
            last_login_ip VARCHAR(15) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT UniqueEmail UNIQUE (email)
        );";

 
    const DROP_SQL = 'DROP TABLE exp_shn_users;';

    // internal data members
    private $_id;
    private $_name;
    private $_station;
    private $_email;
    private $_password;
    private $_password_confirmation;
    private $_pwd_site_key;
    private $_pwd_nonce;
    private $_pwd_hash;
    private $_enabled;
    private $_failed_login_count;
    private $_last_request_at;
    private $_current_login_at;
    private $_last_login_at;
    private $_current_login_ip;
    private $_last_login_ip;
    private $_created_at;
    private $_updated_at;

    //-------------------------------------------------------------------------
    // Construction and Destruction
    //-------------------------------------------------------------------------

    public function __construct(/*mixed*/ $id = false)
    {
        parent::__construct();

        $this->init();

        if ($id)
        {
            $this->load($id);
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    //-------------------------------------------------------------------------
    // Accessors
    //-------------------------------------------------------------------------

    public function id() /*string*/
    {
        return $this->_id;
    }

    public function name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_name = trim($value);
        return $this->_name;
    }

    public function email($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_email = trim($value);
        return $this->_email;
    }

    public function station($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_station = trim($value);
        return $this->_station;
    }

    public function password(/*string*/ $password) /*void*/
    {
        // http://stackoverflow.com/questions/401656/secure-hash-and-salt-for-php-passwords
        $config = new ShnConfig();
        $this->_password = trim($password);
        $this->_pwd_nonce = uniqid() . uniqid() . uniqid();
        $this->_pwd_site_key = $config->site_key();
        $this->_pwd_hash = $this->hash_password($this->_password, $this->_pwd_nonce, $this->_pwd_site_key);
    }

    public function password_confirmation(/*string*/ $password) /*void*/
    {
        // http://stackoverflow.com/questions/401656/secure-hash-and-salt-for-php-passwords
        $this->_password_confirmation = trim($password);
    }

    public function enabled($value = NULL) /*mixed*/
    {
        if (! is_null($value)) $this->_enabled = ($value ? TRUE : FALSE);
        return $this->_enabled != FALSE;
    }

    public function enable() /*void*/
    {
        $this->_enabled = TRUE;
    }

    public function disable() /*void*/
    {
        $this->_enabled = FALSE;
    }

    public function failed_login_count() /*string*/
    {
        return $this->_failed_login_count;
    }

    public function last_request_at() /*string*/
    {
        return $this->_last_request_at;
    }

    public function current_login_at() /*string*/
    {
        return $this->_current_login_at;
    }

    public function last_login_at() /*string*/
    {
        return $this->_last_login_at;
    }

    public function current_login_ip() /*string*/
    {
        return $this->_current_login_ip;
    }

    public function last_login_ip() /*string*/
    {
        return $this->_last_login_ip;
    }

    public function created_at() /*string*/
    {
        return $this->_created_at;
    }

    public function updated_at() /*string*/
    {
        return $this->_updated_at;
    }

    //-------------------------------------------------------------------------
    // Operations
    //-------------------------------------------------------------------------

    // http://stackoverflow.com/questions/401656/secure-hash-and-salt-for-php-passwords
    public function hash_password(/*string*/ $password, /*string*/ $nonce, /*string*/ $site_key) /*string*/
    {
        return hash_hmac('sha512', $password . $nonce, $site_key);
    }

    public function check_password(/*string*/ $password) /*bool*/
    {
        return $this->_pwd_hash == $this->hash_password($password, $this->_pwd_nonce, $this->_pwd_site_key);
    }

    public function authenticate(/*string*/ $password) /*bool*/
    {
        $ok = $this->_id != 0;
        
        if ($ok)
        {
            // check password
            $ok = ($this->enabled() && $this->check_password($password));

            // update user record
            if ($ok)
            {
                $this->_failed_login_count = 0;
                $this->_last_login_at = $this->_current_login_at;
                $this->_current_login_at = $this->mysql_now();
                $this->_last_login_ip = $this->_current_login_ip;
                $this->_current_login_ip = $_SERVER['REMOTE_ADDR'];
            }
            else
            {
                $this->_failed_login_count++;
                $this->_current_login_at = '';
            }
            $this->_last_request_at = $this->mysql_now();
            $this->save();
        }

        return $ok;
    }
    
    protected function init() /*void*/
    {
        parent::init();
        $this->_id = 0;
        $this->_name = '';
        $this->_station = '';
        $this->_email = '';
        $this->_password = '';
        $this->_password_confirmation = '';
        $this->_pwd_site_key = '';
        $this->_pwd_nonce = '';
        $this->_pwd_hash = '';
        $this->_enabled = FALSE;
        $this->_failed_login_count = '';
        $this->_last_request_at = '';
        $this->_current_login_at = '';
        $this->_last_login_at = '';
        $this->_current_login_ip = '';
        $this->_last_login_ip = '';
        $this->_created_at = '';
        $this->_updated_at = '';
    }

    public function to_a(/*bool*/ $sql_escape = false) /*array*/
    {
        $data = array(
            'id' =>  $this->_id,
            'name' =>  $this->_name,
            'station' =>  $this->_station,
            'email' =>  $this->_email,
            'pwd_site_key' =>  $this->_pwd_site_key,
            'pwd_nonce' =>  $this->_pwd_nonce,
            'pwd_hash' =>  $this->_pwd_hash,
            'enabled' =>  $this->_enabled,
            'failed_login_count' =>  $this->_failed_login_count,
            'last_request_at' =>  $this->_last_request_at,
            'current_login_at' =>  $this->_current_login_at,
            'last_login_at' =>  $this->_last_login_at,
            'current_login_ip' =>  $this->_current_login_ip,
            'last_login_ip' =>  $this->_last_login_ip,
            'created_at' =>  $this->_created_at,
            'updated_at' =>  $this->_updated_at,
        );

        if ($sql_escape)
        {
            foreach ($data as $key => $value)
            {
                $data[$key] = $this->_DB->escape_str($value);
            }
        }

        return $data;
    }

    public function from_a(/*array*/ $data) /*void*/
    {
        $this->init();
        if (is_array($data))
        {
            $this->_id = $data['id'];
            $this->_name = $data['name'];
            $this->_station = $data['station'];
            $this->_email = $data['email'];
            $this->_pwd_site_key = $data['pwd_site_key'];
            $this->_pwd_nonce = $data['pwd_nonce'];
            $this->_pwd_hash = $data['pwd_hash'];
            $this->_enabled = $data['enabled'];
            $this->_failed_login_count = $data['failed_login_count'];
            $this->_last_request_at = $data['last_request_at'];
            $this->_current_login_at = $data['current_login_at'];
            $this->_last_login_at = $data['last_login_at'];
            $this->_current_login_ip = $data['current_login_ip'];
            $this->_last_login_ip = $data['last_login_ip'];
            $this->_created_at = $data['created_at'];
            $this->_updated_at = $data['updated_at'];
            $this->_errors = array();
        }
    }

    public function validate() /*bool*/
    {
        // clear the errors array
        $this->_errors = array();

        // name must be present
        if (strlen($this->_name) == 0) {
            $this->_errors[] = "The Name cannot be blank.";
        }

        // email must be present and pass regex
        $pattern = '/^([^@\s]+)@((?:[-_a-z0-9]+\.)+[a-z]{2,})$/i';
        if (preg_match($pattern, $this->_email) != 1) {
            $this->_errors[] = "The Email address must be valid.";
        }

        // station must be present
        if (strlen($this->_station) == 0) {
            $this->_errors[] = "The Station cannot be blank.";
        }

        // only check password for new user or if it has been updated
        if ($this->_id == 0 || strlen($this->_password) > 0 || strlen($this->_password_confirmation) > 0)
        {
            // password must be minimum length
            if (strlen($this->_password) < 8) {
                $this->_errors[] = "The Password must be at least 8 characters in length.";
            }

            // password must match confirmation
            if ($this->_password != $this->_password_confirmation) {
                $this->_errors[] = "The Password and Password Confirmation must match.";
            }
        }

        return (empty($this->_errors));
    }

    public function save() /*bool*/
    {
        // validate before doing anything else
        if (! $this->validate()) return false;

        // capture the data to be saved
        $data = $this->to_a(true);
        $data['updated_at'] = $this->mysql_now();

        // is this a new user?
        if ($this->_id == 0)
        {
            unset($data['id']);
            $data['enabled'] = TRUE;
            $data['failed_login_count'] = 0;
            $data['created_at'] = $this->mysql_now();
            $sql = $this->_DB->insert_string(self::TABLE_NAME, $data);
        }
        else
        {
            $sql = $this->_DB->update_string(self::TABLE_NAME, $data, "id = {$this->_id}");
        }

        // store the data in the database
        $this->_DB->query($sql);
        $ok = $this->_DB->affected_rows > 0;

        if ($ok && $this->_id == 0)
        {
            // if new user, reload on success
            $ok = $this->load($this->_email);
        }

        return $ok;
    }

    public function destroy() /*bool*/
    {
        $ok = true;

        if ($this->_id != 0)
        {
            $this->_DB->query('delete from ' . self::TABLE_NAME . " where id = {$this->_id};");
            $ok = $this->_DB->affected_rows > 0;
        }

        $this->init();

        return $ok;
    }

    public function load(/*mixed*/ $id) /*bool*/
    {
        $this->_errors = array();
        if (is_numeric($id))
        {
            $sql = 'select * from ' . self::TABLE_NAME . " where id = {$id};";
        }
        else
        {
            $sql = 'select * from ' . self::TABLE_NAME . " where email like '{$id}';";
        }

        $rs = $this->_DB->query($sql);

        $found = $rs->num_rows > 0;

        if ($found) $this->from_a($rs->row);

        return $found;
    }

    public static function find(/*mixed*/ $id) /*ShnUser*/
    {
        $user = new ShnUser($id);
        if ($user->id() == 0) $user = NULL;
        return $user;
    }

    public static function all() /*array*/
    {
        global $DB;

        $all = array();

        $sql = 'select * from ' . self::TABLE_NAME . " order by name asc;";

        $rs = $DB->query($sql);

        if ($rs->num_rows > 0)
        {
            foreach($rs->result as $row)
            {
                $user = new ShnUser();
                $user->from_a($row);
                $all[] = $user;
            }
        }

        return $all;
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
