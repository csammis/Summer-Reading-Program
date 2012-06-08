<?php
/*
* Template Name: SRP Book Submission
* Controls to submit pages / minutes read

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

require_once('includes/srp-inc-users.php');
SRP_AuthRedirect($SRP_AUTHENTICATED);

require_once('includes/srp-inc-template.php');

$srp_leftcolumnwidth = 100;

$action_type = $_POST['action'];
if (empty($action_type))
{
    $action_type = 'start';
  $srp_numbertype = 0;
}

switch ($action_type)
{
    case 'postbook':
        /** Collect and validate input **/
        $reqfields = '';
        $srp_title = esc_attr(stripslashes($_POST['srp_title']));
        if (strlen($srp_title) == 0) $reqfields .= 'srp_title:';
        $srp_author = esc_attr(stripslashes($_POST['srp_author']));
        if (strlen($srp_author) == 0) $reqfields .= 'srp_author:';
    $srp_genre = esc_attr(stripslashes($_POST['srp_genre']));
        if (strlen($srp_genre) == 0) $reqfields .= 'srp_genre:';
    
    $srp_submitreview = esc_attr(stripslashes($_POST['srp_submitreview']));
    if (strlen($srp_submitreview) == 0) $srp_submitreview = 0;
    
    $srp_pages = esc_attr(stripslashes($_POST['srp_pages']));
        if (strlen($srp_pages) == 0 || !is_numeric($srp_pages) || $srp_pages <= 0)
        {
            $reqfields .= 'srp_pages:';
        }
        else
        {
            $srp_minutes = $srp_pages;
        }
        
        if (strlen($reqfields) != 0)
        {
            $errorcode = 'reqfields';
        }
        else
        {
            $program_open_date = get_srptheme_option('program_open_date');
            // odd zero day behavior?  $days_open = ((time() - $program_open_date) / 60 / 60 / 24) + 1;
            $days_open = ((time() - $program_open_date) / 60 / 60 / 24);
            $reading_hours_open = $days_open * 20;
            
            $userminutes = get_usermeta($current_user->ID, 'srp_minutes');
            $newminutes = $userminutes + $srp_minutes;
            if (($newminutes / 60) > $reading_hours_open)
            {
                $errorcode = 'toolong';
            }
            else
            {
                require_once('includes/srp-inc-prizes.php');
                
                $userminutes = get_usermeta($current_user->ID, 'srp_minutes');
                
                SRP_AwardHourlyPrizesWithinBoundary($current_user->ID, $userminutes, ($userminutes + $srp_minutes));
                SRP_SetGrandPrizeEntriesWithinBoundary($current_user->ID, $userminutes, ($userminutes + $srp_minutes));
                
                $userminutes = $userminutes + $srp_minutes;
                update_user_meta($current_user->ID, 'srp_minutes', $userminutes);
                
                $url = site_url('/');
                if ($srp_submitreview == 1)
                {
                    $submitreview_info = urlencode($srp_title) . '=' . urlencode($srp_author) . '=' . urlencode($srp_genre);
                    update_user_meta($current_user->ID, 'submitreview_info', $submitreview_info);
                    
                    $url = SRP_SelectUrlOfTemplatedPage('submitreview');
                }
                header('Location: ' . $url);
                exit();
            }
        }

    case 'start':
        SRP_PrintPageStart($srp_leftcolumnwidth);
        if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
?>
        <div id="post-<?php the_ID(); ?>" <?php if (function_exists("post_class")) post_class(); else print 'class="post"'; ?>>
        <h2><?php the_title(); ?></h2>
        <div class="post-content clearfix"><?php the_content('Read the rest of this page &raquo;'); ?></div>
<?php
        if (isset($errorcode))
        {
            $errormsg = '<div class="errormsg">Whoops, your submission couldn\'t be posted: ';
            switch ($errorcode)
            {
                case 'reqfields':
                    $errormsg .= 'Some fields were left blank or are invalid.';
                    break;
                case 'toolong':
                    $errormsg .= 'You\'ve entered more time than the Summer Reading Program has been open.';
                    break;
                default:
                    $errormsg .= 'An unknown error has occured: ' . $errorcode;
                    break;
            }
            $errormsg .= '</div>';
            echo $errormsg;
        }

    if (get_srptheme_option('program_active') == 0)
    {
?>
    <div>&nbsp;</div>
    <div>Sorry, the Summer Reading Program is closed!</div>
<?php
    }
    else
    {
?>
      <div>&nbsp;</div>
      <div class="reviewbox">
            <form id="SRPSubmitBook" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
            <input type="hidden" name="action" value="postbook" />
            <input type="hidden" name="page_id" value="<?php the_ID(); ?>" />
            <p>
            <label <?php if (strpos($reqfields, 'srp_title:') !== FALSE) echo 'class="errormsg"';?>>Title:<br />
            <input type="text" name="srp_title" id="srp_title" class="SRPInput" value="<?php echo esc_attr(stripslashes($srp_title)); ?>" size="40" />
            </label>
            </p>
            <p>
            <label <?php if (strpos($reqfields, 'srp_author:') !== FALSE) echo 'class="errormsg"';?>>Author:<br />
            <input type="text" name="srp_author" id="srp_author" class="SRPInput" value="<?php echo esc_attr(stripslashes($srp_author)); ?>" size="40" />
            </label>
            </p>
            <p>
            <label <?php if (strpos($reqfields, 'srp_genre:') !== FALSE) echo 'class="errormsg"';?>>Which best describes this book?<br />
            <?php SRP_PrintGenreSelector('srp_genre', $srp_genre); ?>
            </label>
            </p>
            <div>&nbsp;</div>
            <?php SRP_PrintJavascriptNumberValidator(); ?>
            <div>
            <span>
            <label for="srp_pages" <?php if (strpos($reqfields, 'srp_pages:') !== FALSE) echo 'class="errormsg"';?>>Number of pages / minutes spent reading:</label>
            <br />
            <input type="text" name="srp_pages" id="srp_pages" class="SRPInputNoSize"
                   value="<?php echo esc_attr(stripslashes($srp_pages)); ?>" size="5" onKeyPress="return onlyNumbers(event.charCode || event.keyCode);" />
            &nbsp;&nbsp;<span style="font-size:smaller;font-style:italic">1 page = 1 minute</span>
            </span>
            </div>
            <div>&nbsp;</div>
            <div>
            <label><input type="checkbox" name="srp_submitreview" value="1"
                          <?php if ($srp_submitreview == 1) echo 'checked'; ?> /> After submitting this book, I want to write a review of it.</label>
            </div>
            <p>
            <input type="submit" value="Submit book" />
            </p>
            </form>
      </div>
<?php
    } // end closed check
?>
    </div>
<?php
    
        endif; /* end The Loop */
        SRP_PrintPageEnd($srp_leftcolumnwidth, 'srp_title');
       break;
}
?>
