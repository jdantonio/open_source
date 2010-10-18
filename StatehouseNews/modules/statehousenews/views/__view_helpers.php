<?php

/**
 * TODO: Figure out why $vars is not working properly with the hacked view() function.
 **/
function partial(/*string*/ $view, /*assoc*/ $vars = array(), /*bool*/ $return = FALSE, /*string*/ $path = '') /*mixed*/
{
    //global $DSP;

    if ($return)
    {
        //return $DSP->view($view, $vars, TRUE, $path);
        return view($view, $vars, TRUE, $path);
    }
    else
    {
        //$DSP->view($view, $vars, FALSE, $path);
        view($view, $vars, FALSE, $path);
    }
}

/**
 * Requied for Control Panel view file hack. See below.
 **/
if (! function_exists('lang'))
{    
    function lang($which = '', $label = '')
    {
        global $LANG;
        return $LANG->line($which, $label);
    }
}

/**
 * The ability to use 'view' files was added in Version 1.6.8
 * Build 20090723 (initial release). I used this feature while
 * developing under 1.6.9 and found it very useful but was unaware
 * at the time that the feature was not available in the current
 * production version (1.6.0). As a result I had to punt. The
 * code below was taken directly from ExpressionEngine Version 1.6.9
 * Build 20100430, file cp.display.php. Minor changes to the code
 * were made by me to convert code from a class instance method
 * to a generic function. These changes are clearly marked. Otherwise
 * the code remains unaltered and is the copyright of EllisLab, Inc.
 * As soon as the production servers are updated to a version of
 * ExpressionEngine that supports the CP View functionality this
 * code should be removed. 
 *
 * http://expressionengine.com/legacy_docs/changelog.html#v168 
 * http://expressionengine.com/legacy_docs/development/tutorial.html#views
 **/

/*
=====================================================
 ExpressionEngine - by EllisLab
-----------------------------------------------------
 http://expressionengine.com/
-----------------------------------------------------
 Copyright (c) 2003 - 2010 EllisLab, Inc.
=====================================================
 THIS IS COPYRIGHTED SOFTWARE
 PLEASE READ THE LICENSE AGREEMENT
 http://expressionengine.com/docs/license.html
=====================================================
 File: cp.display.php
-----------------------------------------------------
 Purpose: This class provides all the HTML dispaly
 elements used in the control panel.
=====================================================
*/

/** -------------------------------------
/**  Allows the use of View files to construct output
/** -------------------------------------*/

function view($view, $vars = array(), $return = FALSE, $path = '')
{
    global $DSP, $FNS, $LANG, $LOC, $PREFS, $REGX, $SESS;

    // Set the path to the requested file
    if ($path == '')
    {
        $ext = pathinfo($view, PATHINFO_EXTENSION);
        $file = ($ext == '') ? $view.EXT : $view;
        // GAD BEGIN
        //$path = $this->view_path.$file;
        $view_path = dirname(__FILE__);
        $path = $view_path.'/'.$file;
        // GAD END
    }
    else
    {
        $x = explode('/', $path);
        $file = end($x);
    }

    if ( ! file_exists($path))
    {
        trigger_error('Unable to load the requested file: '.$file);
        return FALSE;
    }

    /*
     * Extract and cache variables
     *
     * You can either set variables using the dedicated $this->load_vars()
     * function or via the second parameter of this function. We'll merge
     * the two types and cache them so that views that are embedded within
     * other views can have access to these variables.
     */	
    // GAD BEGIN
    //if (is_array($vars))
    //{
        //$this->cached_vars = array_merge($this->cached_vars, $vars);
    //}
    //extract($this->cached_vars);
    if (is_array($vars)) extract($vars);
    // GAD END

    /*
     * Buffer the output
     *
     * We buffer the output for two reasons:
     * 1. Speed. You get a significant speed boost.
     * 2. So that the final rendered template can be
     * post-processed by the output class.  Why do we
     * need post processing?  For one thing, in order to
     * show the elapsed page load time.  Unless we
     * can intercept the content right before it's sent to
     * the browser and then stop the timer it won't be accurate.
     */
    ob_start();

    // If the PHP installation does not support short tags we'll
    // do a little string replacement, changing the short tags
    // to standard PHP echo statements.
    
    if ((bool) @ini_get('short_open_tag') === FALSE)
    {
        echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($path))));
    }
    else
    {
        include($path); // include() vs include_once() allows for multiple views with the same name
    }
    
    // Return the file data if requested
    if ($return === TRUE)
    {		
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
    
    /*
     * Flush the buffer... or buff the flusher?
     *
     * In order to permit views to be nested within
     * other views, we need to flush the content back out whenever
     * we are beyond the first level of output buffering so that
     * it can be seen and included properly by the first included
     * template and any subsequent ones. Oy!
     *
     */	
    if (ob_get_level() > 1)
    {
        ob_end_flush();
    }
    else
    {
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}   
/* END */
/*
=====================================================
 END ExpressionEngine - by EllisLab
=====================================================
*/

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
