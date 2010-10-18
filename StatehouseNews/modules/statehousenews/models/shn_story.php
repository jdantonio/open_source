<?php

require_once(dirname(__FILE__) . '/_model.php');

class ShnStory extends Model
{
    //--------------------------------------------------------------------------
    // Data Members
    //--------------------------------------------------------------------------

    // internal data members
    private $_entry_id;
    private $_author_id;
    private $_entry_date;
    private $_title;
    private $_slug;
    private $_reporter;
    private $_station;
    private $_story_type;
    private $_classification;
    private $_kill_date;
    private $_audio_file;
    private $_audio_length;
    private $_publication_status;
    private $_anchor_copy;
    private $_full_story_copy;
    private $_story_notes;

    private $_categories;

    //-------------------------------------------------------------------------
    // Construction and Destruction
    //-------------------------------------------------------------------------

    public function __construct(/*array*/ $data = false)
    {
        parent::__construct();

        $this->init();
        if (is_array($data)) $this->from_a($data);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    //-------------------------------------------------------------------------
    // Accessors
    //-------------------------------------------------------------------------

    public function entry_id() /*string*/
    {
        return $this->_entry_id;
    }

    public function author_id($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_author_id = intval(trim($value));
        return $this->_author_id;
    }

    public function entry_date($value = NULL) /*string*/
    {
        if (! is_null($value))
        {
            $value = trim($value);
            $this->_entry_date = intval($value);
            if ($this->_entry_date <= 0) $this->_entry_date = strtotime($value);
        }
        return $this->_entry_date;
    }

    public function title($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_title = trim($value);
        return $this->_title;
    }

    public function slug($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_slug = trim($value);
        return $this->_slug;
    }

    public function reporter($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_reporter = trim($value);
        return $this->_reporter;
    }

    public function station($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_station = trim($value);
        return $this->_station;
    }

    public function story_type($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_story_type = trim($value);
        return $this->_story_type;
    }

    public function classification($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_classification = trim($value);
        return $this->_classification;
    }

    public function kill_date($value = NULL) /*string*/
    {
        if (! is_null($value))
        {
            $value = trim($value);
            $this->_kill_date = intval($value);
            if ($this->_kill_date <= 0) $this->_kill_date = strtotime($value);
        }
    }

    public function audio_file($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_audio_file = $this->cleanse_file_name(trim($value));
        return $this->_audio_file;
    }

    public function audio_length($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_audio_length = trim($value);
        return $this->_audio_length;
    }

    public function publication_status($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_publication_status = trim($value);
        return $this->_publication_status;
    }

    public function anchor_copy($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_anchor_copy = trim($value);
        return $this->_anchor_copy;
    }

    public function full_story_copy($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_full_story_copy = trim($value);
        return $this->_full_story_copy;
    }

    public function story_notes($value = NULL) /*string*/
    {
        if (! is_null($value)) $this->_story_notes = trim($value);
        return $this->_story_notes;
    }

    public function add_categories(/*mixed*/ $group_id, /*array*/ $categories) /*bool*/
    {
        $ok = false;
        $id = intval($group_id);
        if ($id <= 0) $id = intval(preg_replace('/\D/', '', $group_id));
        
        if ($id > 0 && is_array($categories))
        {
            $this->_categories[(string)$id] = $categories;
            $ok = true;
        }

        return $ok;
    }

    //-------------------------------------------------------------------------
    // Operations
    //-------------------------------------------------------------------------
    
    protected function init() /*void*/
    {
        parent::init();

        $this->_entry_id = 0;
        $this->_author_id = 0;
        $this->_entry_date = 0;
        $this->_title = '';
        $this->_slug = '';
        $this->_reporter = '';
        $this->_station = '';
        $this->_story_type = '';
        $this->_classification = '';
        $this->_kill_date = 0;
        $this->_audio_file = '';
        $this->_audio_length = '';
        $this->_publication_status = '';
        $this->_anchor_copy = '';
        $this->_full_story_copy = '';
        $this->_story_notes = '';

        $this->_categories = array();
    }

    public function to_a(/*bool*/ $sql_escape = false) /*array*/
    {
        $data = array(
            'entry_id' =>  $this->_entry_id,
            'author_id' =>  $this->_author_id,
            'entry_date' => $this->_entry_date,
            'title' => $this->_title,
            'slug' => $this->_slug,
            'reporter' => $this->_reporter,
            'station' => $this->_station,
            'story_type' => $this->_story_type,
            'classification' => $this->_classification,
            'kill_date' => $this->_kill_date,
            'audio_file' => $this->_audio_file,
            'audio_length' => $this->_audio_length,
            'publication_status' => $this->_publication_status,
            'anchor_copy' => $this->_anchor_copy,
            'full_story_copy' => $this->_full_story_copy,
            'story_notes' => $this->_story_notes,
        );

        if (intval($this->_entry_date) > 0)
        {
            $entry_date = getdate(intval($this->_entry_date));
            $data['entry_date_year'] = $entry_date['year'];
            $data['entry_date_month'] = $entry_date['mon'];
            $data['entry_date_day'] = $entry_date['mday'];
        }

        if (intval($this->_kill_date) > 0)
        {
            $kill_date = getdate(intval($this->_kill_date));
            $data['kill_date_year'] = $kill_date['year'];
            $data['kill_date_month'] = $kill_date['mon'];
            $data['kill_date_day'] = $kill_date['mday'];
        }

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
            // set individual entries
            $this->_entry_date = mktime();
            $this->_kill_date = 0;

            $this->_entry_id = $data['entry_id'];
            $this->_author_id = $data['author_id'];
            if (isset($data['entry_date'])) $this->entry_date($data['entry_date']);
            $this->_title = $data['title'];
            $this->_slug = $data['slug'];
            $this->_reporter = $data['reporter'];
            $this->_station = $data['station'];
            $this->_story_type = $data['story_type'];
            $this->_classification = $data['classification'];
            if (isset($data['kill_date'])) $this->kill_date($data['kill_date']);
            $this->_audio_file = $this->cleanse_file_name($data['audio_file']);
            $this->_audio_length = $data['audio_length'];
            $this->_publication_status = $data['publication_status'];
            $this->_anchor_copy = $data['anchor_copy'];
            $this->_full_story_copy = $data['full_story_copy'];
            $this->_story_notes = $data['story_notes'];

            // set entry date when given date components
            if (! isset($data['entry_date']) && isset($data['entry_date_year']))
            {
                $this->_entry_date = mktime(
                    date('H'),
                    date('i'),
                    date('s'),
                    intval($data['entry_date_month']),
                    intval($data['entry_date_day']),
                    intval($data['entry_date_year'])
                );
            }

            // set kill date when given date components
            if (! isset($data['kill_date']) && isset($data['kill_date_year']))
            {
                $this->_kill_date = mktime(
                    date('H'),
                    date('i'),
                    date('s'),
                    intval($data['kill_date_month']),
                    intval($data['kill_date_day']),
                    intval($data['kill_date_year'])
                );
            }

            // set categories
            foreach ($_POST as $item => $value)
            {
                if (is_array($value)) $this->add_categories($item, $value);
            }
        }
    }

    public function validate() /*bool*/
    {
        // clear the errors array
        $this->_errors = array();

        // check required fields
        $fields = $this->to_a();
        foreach ($fields as $field => $value)
        {
            if ($field != 'story_notes' && $field != 'entry_id' && $field != 'author_id' && empty($value))
            {
                $this->_errors[] = "The '{$field}' field is required.";
            }
        }

        // check date fields
        if ($this->_entry_date <= 0) $this->_errors[] = "The 'entry_date' field must be a valid date.";
        if ($this->_kill_date <= 0) $this->_errors[] = "The 'kill_date' field must be a valid date.";

        // check audio length field
        if (preg_match('/^(\d*\d:)?([012345]?\d)?:?[012345]\d$/', $this->_audio_length) == 0) $this->_errors[] = "The 'audio_length' field must be in the hh:mm:ss format (hours and mintes optional).";

        return (empty($this->_errors));
    }

    public function save() /*bool*/
    {
        // validate before doing anything else
        if (! $this->validate()) return false;

        // get the config data
        $config = new ShnConfig();

        // get all weblog field metadata
        $sql = $this->select_weblog_fields_sql($config);
        $fields = $this->_DB->query($sql);

        // create weblog_titles sql
        $sql = $this->insert_weblog_titles_sql($config);

        // insert into titles
        $this->_DB->query($sql);
        $ok = ($this->_DB->affected_rows == 1);

        // insert into data and category tables
        if ($ok)
        {
            // get the entry id
            $this->_entry_id = $this->_DB->insert_id;

            // exp_weblog_data sql
            $sql = $this->insert_weblog_data_sql($config, $fields);
            $this->_DB->query($sql);
            $ok = ($this->_DB->affected_rows == 1);

            if ($ok)
            {
                // exp_category_posts sql
                $sqls = $this->insert_category_posts_sql($config);
                foreach ($sqls as $sql)
                {
                    $this->_DB->query($sql);
                    $ok = ($this->_DB->affected_rows == 1);
                    if (! $ok) break;
                }
            }

            // on failure, delete all entries
            if (! $ok)
            {
                $sqls = array();
                $sqls[] = "DELETE FROM exp_category_posts WHERE entry_id = {$this->_entry_id};";
                $sqls[] = "DELETE FROM exp_weblog_data WHERE entry_id = {$this->_entry_id};";
                $sqls[] = "DELETE FROM exp_weblog_titles WHERE entry_id = {$this->_entry_id};";
                foreach ($sqls as $sql) $this->_DB->query($sql);
            }
        }

        return $ok;
    }

    //-------------------------------------------------------------------------
    // SQL Helpers
    //-------------------------------------------------------------------------

    public function cleanse_file_name(/*string*/ $fname)
    {
        // trim front and back
        $fname = trim($fname);

        // remove all escaped characters
        $fname = preg_replace('/%\d\d/', '', $fname);

        // remove all special characters
        $fname = preg_replace('/[^\w\d\s-_\.]/', '', $fname);

        // remove all spaces before dots
        $fname = preg_replace('/\s+\./', '.', $fname);

        // replace spaces with underscores
        $fname = preg_replace('/\s/', '_', $fname);

        // make lowercase
        $fname = strtolower($fname);

        return $fname;
    }

    private function get_author_id(/*ShnConfig*/ &$config) /*string*/
    {
        $sql = "SELECT member_id FROM exp_members WHERE username LIKE '{$config->author_username()}';";
        $rs = $this->_DB->query($sql);
        if ($rs->num_rows >= 0)
        {
            $author_id = $rs->row['member_id'];
        }
        else
        {
            $author_id = 0;
        }

        return $author_id;
    }

    private function select_weblog_fields_sql(/*ShnConfig*/ &$config) /*string*/
    {
        return "SELECT * FROM exp_weblog_fields WHERE site_id = {$config->site_id()} AND group_id = {$config->field_group_id()} ORDER BY field_name ASC;";
    }

    private function insert_weblog_titles_sql(/*ShnConfig*/ &$config) /*string*/
    {
        if (! $this->_author_id) $this->_author_id = $this->get_author_id($config);

        $data = array(
            'site_id' => $config->site_id(),
            'weblog_id' => $config->weblog_id(),
            'author_id' => $this->_author_id,
            'pentry_id' => 0,
            'forum_topic_id' => 0,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'title' => $this->_title,
            'url_title' => strtolower($this->_REGX->create_url_title($this->_title)),
            'status' => 'open',
            'versioning_enabled' => 'n',
            'view_count_one' => 0,
            'view_count_two' => 0,
            'view_count_three' => 0,
            'view_count_four' => 0,
            'allow_comments' => 'n',
            'allow_trackbacks' => 'n',
            'sticky' => 'n',
            'entry_date' => $this->_entry_date,
            'dst_enabled' => 'n',
            'year' => date('Y', $this->_entry_date),
            'month' => date('m', $this->_entry_date),
            'day' => date('d', $this->_entry_date),
            'expiration_date' => $this->_kill_date,
            'comment_expiration_date' => '0',
            'edit_date' => NULL,
            'recent_comment_date' => 0,
            'comment_total' => 0,
            'trackback_total' => 0,
            'sent_trackbacks' => 0,
            'recent_trackback_date' => 0,
        );

        return $this->_DB->insert_string('exp_weblog_titles', $data);
    }

    private function insert_weblog_data_sql(/*ShnConfig*/ &$config, /*RS*/ &$fields) /*string*/
    {
        $data = array(
            'entry_id' => $this->_entry_id,
            'site_id' => $config->site_id(),
            'weblog_id' => $config->weblog_id(),
        );

        // add sql for configurable fields
        $cfg = $config->to_a();
        foreach ($cfg as $item => $setting)
        {
            // filter fields only, not caregories
            if (preg_match(ShnConfig::REGEX_FIELD_NAME, $item) && ! preg_match(ShnConfig::REGEX_FIELD_CAT, $setting))
            {
                // find the field number
                foreach ($fields->result as $field)
                {
                    // check for field matching setting
                    if ($field['field_name'] == $setting)
                    {
                        $method_name = preg_replace(ShnConfig::REGEX_FIELD_NAME, '', $item);
                        $value = $this->{$method_name}();
                        $data['field_id_'.$field['field_id']] = $value;
                        break;
                    }
                }
            }
        }

        return $this->_DB->insert_string('exp_weblog_data', $data);
    }

    private function insert_category_posts_sql(/*ShnConfig*/ &$config) /*string*/
    {
        $sql = array();

        // add sql for configurable fields
        $cfg = $config->to_a();
        foreach ($cfg as $item => $setting)
        {
            // filter fields mapped to caregories
            if (preg_match(ShnConfig::REGEX_FIELD_NAME, $item) && preg_match(ShnConfig::REGEX_FIELD_CAT, $setting))
            {
                // get the category id
                $method_name = preg_replace(ShnConfig::REGEX_FIELD_NAME, '', $item);
                $cat_id = intval($this->{$method_name}());

                // build the sql
                if ($cat_id)
                {
                    $data = array(
                        'entry_id' => $this->_entry_id,
                        'cat_id' => $cat_id,
                    );
                    $sql[] = $this->_DB->insert_string('exp_category_posts', $data);
                }
            }
        }

        // add sql for additional categories
        foreach ($this->_categories as $group => $categories)
        {
            foreach ($categories as $cat_id)
            {
                $data = array(
                    'entry_id' => $this->_entry_id,
                    'cat_id' => $cat_id,
                );
                $sql[] = $this->_DB->insert_string('exp_category_posts', $data);
            }
        }

        return $sql;
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
