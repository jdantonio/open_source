<?php

require_once(dirname(__FILE__) . '/_model.php');

/**
 * Configuration data for the StatehouseNews module.
 **/
class ShnConfig extends Model
{
    //--------------------------------------------------------------------------
    // Data Members
    //--------------------------------------------------------------------------

    const TABLE_NAME = 'exp_shn_config';

    const CREATE_SQL =
        "CREATE TABLE IF NOT EXISTS exp_shn_config (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            item VARCHAR(50) NOT NULL,
            setting VARCHAR(50) NOT NULL,
            CONSTRAINT UniqueKey UNIQUE (item)
        );";

 
    const DROP_SQL = 'DROP TABLE exp_shn_config;';

    const REGEX_FIELD_NAME = '/_field_name$/i';
    const REGEX_FIELD_CAT = '/^category/i';

    const FIELD_TYPE_DATE = 1;
    const FIELD_TYPE_TEXT = 2;
    const FIELD_TYPE_TEXTAREA = 3;
    const FIELD_TYPE_FILE = 4;
    const FIELD_TYPE_CAT = 5;

    // internal data members
    private $_site_key;
    private $_site_id;
    private $_weblog_id;
    private $_field_group_id;

    private $_author_username;
    private $_cp_member_groups;
    private $_upload_directory;
    private $_audio_file_types;

    private $_slug_field_name;
    private $_reporter_field_name;
    private $_station_field_name;
    private $_story_type_field_name;
    private $_classification_field_name;
    private $_audio_file_field_name;
    private $_audio_length_field_name;
    private $_publication_status_field_name;
    private $_anchor_copy_field_name;
    private $_full_story_copy_field_name;
    private $_story_notes_field_name;

    //-------------------------------------------------------------------------
    // Construction and Destruction
    //-------------------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();

        $this->init();
        $this->load();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    //-------------------------------------------------------------------------
    // Accessors
    //-------------------------------------------------------------------------

    /**
     * Site key used in password hashing algorithm.
     **/
    public function site_key($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_site_key = trim($value);
        return $this->_site_key;
    }

    /**
     * ExpressionEngine site of the weblog that data is saved to.
     * 
     * @see weblog_id
     * @see field_group_id
     **/
    public function site_id($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_site_id = trim($value);
        return $this->_site_id;
    }

    /**
     * ExpressionEngine weblog that data is saved to.
     * 
     * @see site_id
     * @see field_group_id
     **/
    public function weblog_id($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_weblog_id = trim($value);
        return $this->_weblog_id;
    }

    /**
     * ExpressionEngine field group of the weblog that data is saved to.
     * 
     * @see site_id
     * @see weblog_id
     **/
    public function field_group_id($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_field_group_id = trim($value);
        return $this->_field_group_id;
    }

    /**
     * Default ExpressionEngine member username for all posts.
     **/
    public function author_username($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_author_username = trim($value);
        return $this->_author_username;
    }

    /**
     * Control Panel member groups allowed to access SHN pages (comma-delimited).
     **/
    public function cp_member_groups($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_cp_member_groups = trim($value);
        return $this->_cp_member_groups;
    }

    /**
     * Directory on the web server where audio files are upload.
     **/
    public function upload_directory($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_upload_directory = trim($value);
        return $this->_upload_directory;
    }

    /**
     * Allowed extensions for audio file uploads (comma-delimited).
     **/
    public function audio_file_types($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_audio_file_types = trim($value);
        return $this->_audio_file_types;
    }

    /**
     * Field name or category id for mapping the SLUG field.
     **/
    public function slug_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_slug_field_name = trim($value);
        return $this->_slug_field_name;
    }

    /**
     * Field name or category id for mapping the REPORTER field.
     **/
    public function reporter_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_reporter_field_name = trim($value);
        return $this->_reporter_field_name;
    }

    /**
     * Field name or category id for mapping the STATION field.
     **/
    public function station_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_station_field_name = trim($value);
        return $this->_station_field_name;
    }

    /**
     * Field name or category id for mapping the STORY TYPE field.
     **/
    public function story_type_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_story_type_field_name = trim($value);
        return $this->_story_type_field_name;
    }

    /**
     * Field name or category id for mapping the CLASSIFICATION field.
     **/
    public function classification_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_classification_field_name = trim($value);
        return $this->_classification_field_name;
    }

    /**
     * Field name or category id for mapping the AUDIO FILE field.
     **/
    public function audio_file_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_audio_file_field_name = trim($value);
        return $this->_audio_file_field_name;
    }

    /**
     * Field name or category id for mapping the AUDIO LENGTH field.
     **/
    public function audio_length_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_audio_length_field_name = trim($value);
        return $this->_audio_length_field_name;
    }

    /**
     * Field name or category id for mapping the PUBLICATION STATUS field.
     **/
    public function publication_status_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_publication_status_field_name = trim($value);
        return $this->_publication_status_field_name;
    }

    /**
     * Field name or category id for mapping the ANCHOR COPY field.
     **/
    public function anchor_copy_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_anchor_copy_field_name = trim($value);
        return $this->_anchor_copy_field_name;
    }

    /**
     * Field name or category id for mapping the FULL STORY COPY field.
     **/
    public function full_story_copy_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_full_story_copy_field_name = trim($value);
        return $this->_full_story_copy_field_name;
    }

    /**
     * Field name or category id for mapping the STORY NOTES field.
     **/
    public function story_notes_copy_field_name($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_story_notes_copy_field_name = trim($value);
        return $this->_story_notes_copy_field_name;
    }

    //-------------------------------------------------------------------------
    // Operations
    //-------------------------------------------------------------------------

    /**
     * Extract the category id from field mapped to an ExpressionEngine category.
     **/
    public function get_field_cat(/*string*/ $field) /*bool*/
    {
        $id = false;
        $items = $this->to_a();
        $field = $field . '_field_name';
        if (array_key_exists($field, $items))
        {
            if (preg_match(self::REGEX_FIELD_CAT, $items[$field]))
            {
                $id = intval(preg_replace('/\D/', '', $items[$field]));
            }
        }

        return $id;
    }

    /**
     * Get the field type constant based on the name of a field.
     **/
    public function get_field_type(/*string*/ $field) /*enum*/
    {
        $type = 0;
        $field = strtolower($field);

        if ($field == 'audio_file')
        {
            $type = self::FIELD_TYPE_FILE;
        }
        else if ($field == 'entry_date' || $field == 'kill_date')
        {
            $type = self::FIELD_TYPE_DATE;
        }
        else if ($this->get_field_cat($field))
        {
            $type = self::FIELD_TYPE_CAT;
        }
        else if ($field == 'anchor_copy' || $field == 'full_story_copy' || $field == 'story_notes')
        {
            $type = self::FIELD_TYPE_TEXTAREA;
        }
        else
        {
            $type = self::FIELD_TYPE_TEXT;
        }

        return $type;
    }

    /**
     * Load the configuration data from the database.
     **/
    public function load() /*void*/
    {
        $this->_errors = array();

        $sql = 'select * from ' . self::TABLE_NAME . ';';

        $rs = $this->_DB->query($sql);

        $data = $this->to_a();

        if ($rs->num_rows > 0)
        {
            foreach ($rs->result as $row)
            {
                $data[$row['item']] = $row['setting'];
            }
        }

        $this->from_a($data);
    }

    /**
     * Is the configuration complete?
     **/
    public function is_complete() /*bool*/
    {
        $ok = true;
        $settings = $this->to_a();
        foreach ($settings as $item => $setting)
        {
            if (empty($setting))
            {
                $ok = false;
                break;
            }
        }

        return $ok;
    }
    
    /**
     * Initialize the object with default values.
     *
     * @override
     **/
    protected function init() /*void*/
    {
        parent::init();
        $this->_site_key = '';
        $this->_site_id = '';
        $this->_weblog_id = '';
        $this->_field_group_id = '';
        $this->_author_username = '';
        $this->_cp_member_groups = '';
        $this->_upload_directory = '';
        $this->_audio_file_types = '';
        $this->_slug_field_name = '';
        $this->_reporter_field_name = '';
        $this->_station_field_name = '';
        $this->_story_type_field_name = '';
        $this->_classification_field_name = '';
        $this->_audio_file_field_name = '';
        $this->_audio_length_field_name = '';
        $this->_publication_status_field_name = '';
        $this->_anchor_copy_field_name = '';
        $this->_full_story_copy_field_name = '';
        $this->_story_notes_field_name = '';
    }

    /**
     * Convert the model data into an associative array.
     *
     * @override
     **/
    public function to_a(/*bool*/ $sql_escape = false) /*array*/
    {
        $data = array(
            'site_key' => $this->_site_key,
            'site_id' => $this->_site_id,
            'weblog_id' => $this->_weblog_id,
            'field_group_id' => $this->_field_group_id,
            'author_username' => $this->_author_username,
            'cp_member_groups' => $this->_cp_member_groups,
            'upload_directory' => $this->_upload_directory,
            'audio_file_types' => $this->_audio_file_types,
            'slug_field_name' => $this->_slug_field_name,
            'reporter_field_name' => $this->_reporter_field_name,
            'station_field_name' => $this->_station_field_name,
            'story_type_field_name' => $this->_story_type_field_name,
            'classification_field_name' => $this->_classification_field_name,
            'audio_file_field_name' => $this->_audio_file_field_name,
            'audio_length_field_name' => $this->_audio_length_field_name,
            'publication_status_field_name' => $this->_publication_status_field_name,
            'anchor_copy_field_name' => $this->_anchor_copy_field_name,
            'full_story_copy_field_name' => $this->_full_story_copy_field_name,
            'story_notes_field_name' => $this->_story_notes_field_name,
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

    /**
     * Set the model data values from an associative array. Array elements not
     * corresponding to object properties are ignored. Data is NOT saved to the
     * database when this function is called nor is the data validated.
     *
     * @override
     **/
    public function from_a(/*array*/ &$data) /*void*/
    {
        $this->init();
        if (is_array($data))
        {
            $this->_site_key = $data['site_key'];
            $this->_site_id = $data['site_id'];
            $this->_weblog_id = $data['weblog_id'];
            $this->_field_group_id = $data['field_group_id'];
            $this->_author_username = $data['author_username'];
            $this->_cp_member_groups = $data['cp_member_groups'];
            $this->_upload_directory = $data['upload_directory'];
            $this->_audio_file_types = $data['audio_file_types'];
            $this->_slug_field_name = $data['slug_field_name'];
            $this->_reporter_field_name = $data['reporter_field_name'];
            $this->_station_field_name = $data['station_field_name'];
            $this->_story_type_field_name = $data['story_type_field_name'];
            $this->_classification_field_name = $data['classification_field_name'];
            $this->_audio_file_field_name = $data['audio_file_field_name'];
            $this->_audio_length_field_name = $data['audio_length_field_name'];
            $this->_publication_status_field_name = $data['publication_status_field_name'];
            $this->_anchor_copy_field_name = $data['anchor_copy_field_name'];
            $this->_full_story_copy_field_name = $data['full_story_copy_field_name'];
            $this->_story_notes_field_name = $data['story_notes_field_name'];
        }
    }

    /**
     * Validate the property values of the current object.
     *
     * @override
     **/
    public function validate() /*bool*/
    {
        // clear the errors array
        $this->_errors = array();

        // get all attributes
        $settings = $this->to_a();

        // ensure all attributes are present
        foreach ($settings as $item => $setting)
        {
            if (empty($setting))
            {
                $field_name = $this->_LANG->line("shn_config_attr_{$item}");
                $this->_errors[] = "The '{$field_name}' setting must be provided.";
            }
        }

        // check numericality of integer fields
        $fields = array('site_id', 'weblog_id', 'field_group_id');
        foreach ($fields as $field)
        {
            if (! is_numeric($settings[$field]) || (int) $settings[$field] <= 0)
            {
                $field_name = $this->_LANG->line("shn_config_attr_{$field}");
                $this->_errors[] = "The '{$field_name}' must be a positive integer.";
            }
        }

        // check length of fields with minimum length
        $fields = array('site_key' => 30);
        foreach ($fields as $field => $min)
        {
            if (strlen($settings[$field]) < $min)
            {
                $field_name = $this->_LANG->line("shn_config_attr_{$field}");
                $this->_errors[] = "The '{$field_name}' must be at least {$min} characters long.";
            }
        }

        return (empty($this->_errors));
    }

    /**
     * Save the current object properties to the database if, and only if, the
     * values are valid (calls the validate() method before saving).
     *
     * @override
     **/
    public function save(/*bool*/ $initialize = false) /*bool*/
    {
        // validate before doing anything else
        if (! $initialize && ! $this->validate()) return false;

        // start a transaction
        $this->_DB->query('START TRANSACTION;'); 

        // loop through all attributes
        $ok = false;
        $settings = $this->to_a();
        foreach ($settings as $item => $setting)
        {
            // create the sql
            $data = array(
                'item' => $item,
                'setting' => $setting,
            );
            if ($initialize)
            {
                $sql = $this->_DB->insert_string(self::TABLE_NAME, $data);
            }
            else
            {
                $sql = $this->_DB->update_string(self::TABLE_NAME, $data, "item = '{$item}'");
            }
        
            // insert or update
            $this->_DB->query($sql);

            // check for success
            if (! ($ok = ($this->db_errno() == 0)))
            {
                $this->_errors[] = $this->db_error();
                break;
            }
        }

        // end transaction
        if ($ok) {
            $this->_DB->query('COMMIT;'); 
        } else {
            $this->_DB->query('ROLLBACK;'); 
        }

        return $ok;
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
