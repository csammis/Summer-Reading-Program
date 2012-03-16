<?php
/*
* srp-inc-template.php
* Common HTML printing functions.

This WordPress plugin was developed for the Olathe Public Library, Olathe, KS
http://www.olathelibrary.org

Copyright (c) 2010, Chris Sammis
http://csammisrun.net/

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*
*/

//  SRP_PrintPageStart
//  SRP_PrintPageEnd
//  SRP_PrintPageEndWithSearch
//  SRP_PrintPageEndCommonStart
//  SRP_PrintPageEndCommonEnd
//  SRP_PrintStars
//  SRP_PrintLinkToTemplatedPage
//  SRP_PrintSpringSchoolSelector
//  SRP_PrintFallSchoolSelector
//  SRP_PrintGenreSelector
//  SRP_PrintGradeSelector
//  SRP_PrintJavascriptNumberValidator
//  SRP_PrintHeaderImg
//  SRP_PrintFooterImg

/*
 * SRP_PrintPageStart
 * Prints the two-column page header with a default left column width of 60%
 */

$SRP_LEFTWIDTH  = 60;
$SRP_RIGHTWIDTH = 100 - $SRP_LEFTWIDTH;

function SRP_PrintPageStart($leftwidth = 60)
{
    // Assign to globals so header.php can grab these
    // and inject a <style> element in the right place

    $SRP_LEFTWIDTH = $leftwidth;
    $SRP_RIGHTWIDTH = 100 - $leftwidth;

    get_header();
?>
<div id="main-wrap1">
<div id="main-wrap2">
<div id="main" class="block-content">
<div class="mask-main rightdiv">
<div class="mask-left">
<div class="col1">
<div id="main-content">
<?php 

    global $SrpTheme;

    if (strlen($SrpTheme->getGoogleAnalyticsID()) > 0)
    {
?>
<!-- Google analytics -->
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo $SrpTheme->getGoogleAnalyticsID(); ?>']);
_gaq.push(['_trackPageview']);

(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
<!-- /Google analytics -->
<?php
    }
}

/*
 * SRP_PrintPageEnd
 * Prints the two-column page footer with a default left column width of 60%
 */
function SRP_PrintPageEnd($leftwidth = 60, $fieldfocus = '')
{
    SRP_PrintPageEndCommonStart();
    if ($leftwidth < 100)
    {
        require_once('srp-inc-lists.php');
        get_sidebar();
    }
    SRP_PrintPageEndCommonEnd($fieldfocus);
}

/*
 * SRP_PrintPageEndWithSearch
 * Prints the two-column page footer with a review search area instead of the standard sidebar.
 */
function SRP_PrintPageEndWithSearch($action_url)
{
    global $_REQUEST;
    
    $s_author = esc_attr(stripslashes($_REQUEST['s_author']));
    $s_title  = esc_attr(stripslashes($_REQUEST['s_title']));
    $s_rating = esc_attr(stripslashes($_REQUEST['s_rating']));
    $s_genre  = esc_attr(stripslashes($_REQUEST['s_genre']));
    $s_grade  = esc_attr(stripslashes($_REQUEST['s_grade']));

    $fieldfocus = 's_author';
    if (strlen($s_author) == 0 && strlen($s_title) > 0)
    {
        $fieldfocus = 's_title';
    }

    SRP_PrintPageEndCommonStart();
?>
    <div class="col2">
    <h4>&nbsp;</h4>
    <form method="post" action="<?php echo $action_url; ?>" id="SRP_ReviewSearch">
    <div><label class="SRPSearchLabel">Search by author:<br />
    <input class="SRPInputNoSizeNoMargin" type="text" id="s_author" name="s_author" value="<?php echo $s_author; ?>" />
    </label></div>
    <div><label class="SRPSearchLabel">Search by title:<br />
    <input class="SRPInputNoSizeNoMargin" type="text" id="s_title" name="s_title" value="<?php echo $s_title; ?>" />
    </label></div>
    <div><label class="SRPSearchLabel">Search by rating:<br />
    <select name="s_rating" class="SRPInputNoSizeNoMargin">
        <option value="" <?php if ($s_rating == 0) echo 'selected="selected"';?>></option>
        <option value="5" <?php if ($s_rating == 5) echo 'selected="selected"';?>>5 - Great!</option>
        <option value="4" <?php if ($s_rating == 4) echo 'selected="selected"';?>>4 - Pretty good</option>
        <option value="3" <?php if ($s_rating == 3) echo 'selected="selected"';?>>3 - Not bad</option>
        <option value="2" <?php if ($s_rating == 2) echo 'selected="selected"';?>>2 - Wouldn't recommend it</option>
        <option value="1" <?php if ($s_rating == 1) echo 'selected="selected"';?>>1 - Terrible</option>
    </select>
    </label></div>
    <div>
    <label class="SRPSearchLabel">Search by genre:<br /> <?php SRP_PrintGenreSelector('s_genre', $s_genre, true, 'SRPInputNoSizeNoMargin'); ?></label>
    </div>
    <div>
    <label class="SRPSearchLabel">Show only reviews by:<br />
    <select name="s_grade" class="SRPInputNoSizeNoMargin">
        <option  value="" <?php   if ($s_grade == 0) echo 'selected="selected"';?>></option>
        <option  value="6" <?php  if ($s_grade == 6) echo 'selected="selected"';?>>Grade 6</option>
        <option  value="7" <?php  if ($s_grade == 7) echo 'selected="selected"';?>>Grade 7</option>
        <option  value="8" <?php  if ($s_grade == 8) echo 'selected="selected"';?>>Grade 8</option>
        <option  value="9" <?php  if ($s_grade == 9) echo 'selected="selected"';?>>Grade 9</option>
        <option value="10" <?php if ($s_grade == 10) echo 'selected="selected"';?>>Grade 10</option>
        <option value="11" <?php if ($s_grade == 11) echo 'selected="selected"';?>>Grade 11</option>
        <option value="12" <?php if ($s_grade == 12) echo 'selected="selected"';?>>Grade 12</option>
    </select>
    </label>
    </div>
    <div><input type="submit" value="Search Reviews" /></div>
    </form>
    </div>
<?php
    SRP_PrintPageEndCommonEnd($fieldfocus);
}

function SRP_PrintPageEndCommonStart()
{
print <<<END1
        </div> <!-- /main-content -->
        </div> <!-- /col1 -->
END1;
}

function SRP_PrintPageEndCommonEnd($fieldfocus = '')
{
print <<<END2
    </div> <!-- /mask-left -->
    </div> <!-- /mask-main -->
    <div class="clear-content"></div>
    </div> <!-- /main -->
</div> <!-- /main-wrap2 -->
</div> <!-- /main-wrap1 -->
END2;

    if (strlen($fieldfocus) > 0)
    {
        echo "<script type=\"text/javascript\">\n";
        echo "  try{document.getElementById('$fieldfocus').focus();}catch(e){}\n";
        echo "</script>\n";
    }

    global $SrpMessage;

    get_footer();
}

/*
 * SRP_PrintStars
 * Prints the specified number of star images from the SRPReview plugin directory.
 */
function SRP_PrintStars($count)
{
    if ($count > 5)
    {
        $count = 5;
    }
    else if ($count < 0)
    {
        $count = 0;
    }
    
    $imgdir = WP_PLUGIN_URL . '/SRPReview/';
    $title = "$count star rating";
    $empty_stars = 5 - $count;
    for ($i = 0; $i < $count; $i++)
    {
        echo '<img height="20" width="20" src="' . $imgdir . '/star_gold.png" alt="' . $title . '" title="' . $title . '" />';
    }
    
    for ($i = 0; $i < $empty_stars; $i++)
    {
        echo '<img height="20" width="20" src="' . $imgdir . '/star_empty.png" alt="' . $title . '" title="' . $title . '" />';
    }
}

/*
 * SRP_PrintLinkToTemplatedPage
 * Prints a hyperlink to the WP page with specified template name.
 * Uses SRP methods:
 *  SRP_SelectUrlOfTemplatedPage
 */
function SRP_PrintLinkToTemplatedPage($templatename, $linktext, $linkclass = '')
{
    $url = SRP_SelectUrlOfTemplatedPage($templatename);
    if (strlen($url) == 0)
    {
        echo "<span>No page found with template '$templatename'</span>";
    }
    else
    {
        echo "<a href=\"$url\"";
        if ($linkclass != '')
        {
            echo " class=\"$linkclass\"";
        }
        echo ">$linktext</a>";
    }
}

/*
 * SRP_PrintSpringSchoolSelector
 * Prints a selector for the spring school list.
 */
function SRP_PrintSpringSchoolSelector($selected = -1)
{
    SRP_PrintSchoolSelector(0, $selected);
}

/*
 * SRP_PrintFallSchoolSelector
 * Prints a selector for the fall school list.
 */
function SRP_PrintFallSchoolSelector($selected = -1)
{
    SRP_PrintSchoolSelector(1, $selected);
}

function SRP_PrintSchoolSelector($type, $selected)
{
    require_once('srp-inc-lists.php');
    
    $options = get_option('SRPTheme');
    ksort($options);
    $optionkeys = array_keys($options);
    
    $gid2name = array();
    $sid2name = array();
    $gid2sids = array();
    
    // Associate names and IDs to each other
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $key = $optionkeys[$i];
        $matches = array();
        
        if (strpos($key, 'srp_group') === 0)
        {
            preg_match('/srp_group([0-9]+)/', $key, $matches);
            $gid = $matches[1] + 0;
            $gid2name[$gid] = $options[$key];
        }
        else if (strpos($key, 'srp_school') === 0)
        {
            preg_match('/srp_school([0-9]+)group([0-9]+)/', $key, $matches);
            $gid = $matches[2] + 0; $sid = $matches[1] + 0;
            if (strpos($key, 'show') !== false)
            {
                $show_value = $options[$key];
                if ($show_value == $type || $show_value == 2)
                {
                    $gid2sids[$gid][] = $sid;
                }
            }
            else
            {
                $sid2name[$sid] = $options[$key];
            }
        }
    }

    $fieldname = ($type == 0) ? 'srp_school_spring' : 'srp_school_fall';
    
    echo "<select name=\"$fieldname\" class=\"SRPInput\">\n";
    ksort($gid2name);
    foreach ( $gid2name as $gid => $groupname)
    {
        if (count($gid2sids[$gid]) == 0)
        {
            // Don't print empty groups
            continue;
        }
        
        echo "<option value=\"-$gid\" disabled=\"disabled\">$groupname</option>\n";
        $sidarray = $gid2sids[$gid];
        asort($sidarray); // Put the schools within groups in ID numerical order
        foreach ($sidarray as $sid)
        {
            $schoolname = $sid2name[$sid];
            echo "<option value=\"$sid\"";
            if ($sid == $selected)
            {
                echo ' selected="selected"';
            }
            echo ">&nbsp;&nbsp;&nbsp;&nbsp;$schoolname</option>\n";
        }
    }
    echo "</select>\n";
}

/*
 * SRP_PrintGenreSelector
 * Prints a selector for the configured book genres.
 */
function SRP_PrintGenreSelector($inputname, $selected = '', $bIncludeBlank = false, $class = 'SRPInput')
{
    require_once('srp.class.genre.php');
    $genres = new SRPGenreSettings;
    if (!$genres->dbSelect())
    {
        die('Cannot read genre information from the database (loc = 8FRECW)');
    }

    echo "<select name=\"$inputname\" id=\"$inputname\" class=\"$class\">\n";
    if ($bIncludeBlank)
    {
        echo '<option value=""'; if ($selected == '') echo ' selected="selected"'; echo ' disabled="disabled">' . "</option>\n";
    }
    foreach ($genres as $genre)
    {
        $gid = $genre->getID();
        $name = $genre->getName();
        echo "<option value=\"$gid\""; if ($selected === $gid) echo ' selected="selected"'; echo ">$name</option>\n";
    }
    echo "</select>\n";
}

/*
 * SRP_PrintGradeSelector
 * Prints a selector for the grades allowed for the reading program.
 */
function SRP_PrintGradeSelector($inputname, $selected = '', $class = 'SRPInput', $enabled = true)
{
    $change = "javascript:processGradeChange('" . site_url('/') . "/jquery-processor/');";
    echo "<select name=\"$inputname\" id=\"$inputname\" class=\"$class\" onChange=\"$change\"";
    if ($enabled === false)
    {
        echo ' disabled="disabled"';
    }
    echo ">\n";
?>
    <option value="-1" <?php if ($selected == '') echo 'selected="selected"';?> disabled="disabled">-- Select a grade --</option>
    <option value="6" <?php  if ($selected ==  6) echo 'selected="selected"';?>>Grade 6</option>
    <option value="7" <?php  if ($selected ==  7) echo 'selected="selected"';?>>Grade 7</option>
    <option value="8" <?php  if ($selected ==  8) echo 'selected="selected"';?>>Grade 8</option>
    <option value="9" <?php  if ($selected ==  9) echo 'selected="selected"';?>>Freshman (grade 9)</option>
    <option value="10" <?php if ($selected == 10) echo 'selected="selected"';?>>Sophomore (grade 10)</option>
    <option value="11" <?php if ($selected == 11) echo 'selected="selected"';?>>Junior (grade 11)</option>
    <option value="12" <?php if ($selected == 12) echo 'selected="selected"';?>>Senior (grade 12)</option>
<?php
    echo "</select>\n";
}

function SRP_PrintGrandPrizeSelector($inputname, $selected = '', $class = 'SRPInput')
{
    $options = get_option('SRPTheme');
    ksort($options);
    $optionkeys = array_keys($options);
    
    $name2pid = array();
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $key = $optionkeys[$i];
        $pos = strpos($key, 'srp_gprize');
        if ($pos === false || $pos != 0)
        {
            continue;
        }

        $grandPrizeId = substr($key, strlen('srp_gprize'));
        if (is_numeric($grandPrizeId))
        {
            $name2pid[$options[$key]] = $grandPrizeId;
        }
    }
    
    ksort($name2pid);
    
    echo "<select name=\"$inputname\" id=\"$inputname\" class=\"$class\">\n";
    foreach ($name2pid as $name => $id)
    {
        echo "<option value=\"$id\""; if ($selected === $id) echo ' selected="selected"'; echo ">$name</option>\n";
    }
    echo "</select>\n";
}

/*
 * SRP_PrintJavascriptNumberValidator
 * Outputs a javascript snippet that may be used on a text input:  onkeypressed="validateNumber(event)"
 */
function SRP_PrintJavascriptNumberValidator()
{
?>
<script language="javascript">
function isNumber(c)
{
    return (c - 0) == c && c.length > 0;
}

function validateNumber(event)
{
    var theEvent = event || window.event;
    var key = theEvent.keyCode || theEvent.which;
    var keyStr = String.fromCharCode(key);
    if (!isNumber(keyStr))
    {
        theEvent.returnValue = false;
        theEvent.preventDefault();
    }
}
</script>
<?php
}

/*
 * SRP_PrintHeaderImg
 * Prints an <img> tag for the SRP header.
 */
function SRP_PrintHeaderImg()
{
    global $SrpTheme;
    $url = $SrpTheme->getHeaderImageUrl();

    if (strlen($url) > 0)
    {
        echo "<img src=\"$url\" style=\"width:778px;height:178px;\" alt=\"\" />\n";
    }
    
    echo '';
}

/*
 * SRP_PrintFooterImg
 * Prints an <img> tag for the SRP footer.
 */
function SRP_PrintFooterImg()
{
    global $SrpTheme;
    $url = $SrpTheme->getFooterImageUrl();

    if (strlen($url) > 0 && file_exists($url))
    {
        echo "<img src=\"$url\" alt=\"\" />\n";
    }
    
    echo '';
}
?>
