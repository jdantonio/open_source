<?php

require_once(dirname(__FILE__) . '/../models/shn_story.php');

/**
 *
 **/
class ShnImporter
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
    
    //--------------------------------------------------------------------------
    // Construction and Destruction
    //--------------------------------------------------------------------------
    
    function ShnImporter()
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
    // Operations
    //--------------------------------------------------------------------------

    /**
     * {exp:statehousenews:import
     *     file="/Users/Jerry/Desktop/soundclip10.csv"
     *     entry_date = "STARTDATE"
     *     title = "SLUG"
     *     slug = "SLUG"
     *     reporter = "REPORTER"
     *     station = "STATION"
     *     kill_date = "KILLDATE"
     *     audio_file = "SFILE"
     *     audio_length = "LENGTH"
     *     anchor_copy = "LFILE"
     *     full_story_copy = "LFILE"
     *     story_notes = "NOTES"
     *     story_type = "STORYTYPE"
     *     publication_status = "VIEWFLAG"
     *     defaults='{"classification":10,"story_type":4,"publication_status":15}'
     *     category_mappings='{"story_type":&#123;"QA":8,"F":9,"S":6,"C\/C":4,"V":5&#125;,"publication_status":&#123;"Y":15,"N":16&#125;}'
     *     validate_only="true"
     * }
     *
     * @param $filepath The relative or absolute path to the data csv file.
     * @param $errors An empty array for storing error data.
     * @param $validate_only A boolean indicating whether the data should only be validated
     *        rather than saved.
     * 
     * @return The number of records successfully validated/imported.
     **/
    public function import(/*string*/ $filepath, /*array*/ &$errors, /*bool*/ $validate_only = false) /*int*/
    {
        $count = 0;
        $line = 0;

        // get the default field parameters
        $defaults = $this->_TMPL->fetch_param('defaults');
        if ($defaults) $defaults = json_decode($defaults, true);

        // get the category field mappings
        $mappings = $this->_TMPL->fetch_param('category_mappings');
        if ($mappings) $mappings = json_decode(html_entity_decode($mappings), true);

        // open the file
        $filepath = html_entity_decode($filepath);
        if (preg_match('@^/@', $filepath) == 0) $filepath = dirname(__FILE__) . '/' . $filepath;
        $file = fopen($filepath, 'rb');
        if ($file === FALSE)
        {
            $errors[] = array(
                'record' => 'fopen',
                'messages' => array("Failed to open file '{$filepath}' for reading.")
            );
            return 0;
        }

        // read the header row and get column names
        $columns = fgetcsv($file, 0);
        $columns = $this->map_csv_column_names($columns);
        $line++;

        // loop through the remaining rows
        while (($data = fgetcsv($file)) !== FALSE)
        {
            // set the properties
            $properties = $this->csv_to_story_properties($columns, $data);

            // map category values
            foreach ($mappings as $field => $map)
            {
                $value = $properties[$field];
                if (array_key_exists($value, $map))
                {
                    $properties[$field] = $map[$value];
                }
            }

            // set the default values
            foreach ($defaults as $field => $value)
            {
                if (! array_key_exists($field, $properties) || empty($properties[$field]))
                {
                    $properties[$field] = $value;
                }
            }

            // check for missing anchor and full story copy
            if (! isset($properties['anchor_copy']) || empty($properties['anchor_copy']))
            {
                $properties['anchor_copy'] = $properties['title'];
            }
            if (! isset($properties['full_story_copy']) || empty($properties['full_story_copy']))
            {
                $properties['full_story_copy'] = $properties['title'];
            }

            // convert dates to timestamps
            $properties['entry_date'] = strtotime($properties['entry_date']);
            $properties['kill_date'] = strtotime($properties['kill_date']);

            // create a new story object
            $story = new ShnStory($properties);

            // attempt to save
            if ($validate_only)
            {
                $ok = $story->validate();
            }
            else
            {
                $ok = $story->save();
            }

            // increment counters and save errors
            $line++;
            if ($ok)
            {
                $count++;
            }
            else
            {
                $errors[] = array(
                    'record' => $data,
                    'line' => $line,
                    'messages' => $story->errors()
                );
            }
        }

        // close the file
        fclose($file);

        return $count;
    }

    protected function map_csv_column_names(/*array*/ &$columns) /*array*/
    {
        // get a story array for
        $story = new ShnStory();
        $properties = $story->to_a();

        // loop through the properties and fetch the tag params
        foreach ($properties as $field => $value)
        {
            $properties[$field] = $this->_TMPL->fetch_param($field);

            // find the column index of the given key
            if ($properties[$field])
            {
                // find the column index
                for ($index = 0; $index < count($columns); $index++)
                {
                    if ($properties[$field] == $columns[$index])
                    {
                        $properties[$field] = $index;
                        break;
                    }
                }
            }
            else
            {
                // null the unmapped property
                $properties[$field] = NULL;
            }
        }

        return $properties;
    }

    protected function csv_to_story_properties(/*array*/ &$columns, /*array*/ &$data) /*array*/
    {
        $properties = array();

        // loop through the column map
        foreach ($columns as $property => $index)
        {
            // only process mapped columns
            if (! is_null($index))
            {
                // get the required value
                $properties[$property] = $data[$index];
            }
        }

        return $properties;
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
