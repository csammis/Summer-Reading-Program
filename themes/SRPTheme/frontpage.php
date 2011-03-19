<?php
/*
* Template Name: SRP Front Page
* Provides the landing page for the summer reading program users, admins, and unregistered visitors

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
SRP_AuthRedirect($SRP_UNAUTHENTICATED);

require_once('includes/srp-inc-template.php');
require_once('includes/srp-inc-reporting.php');
require_once('includes/srp-inc-utility.php');
require_once('includes/srp-inc-prizes.php');

$srp_leftcolumnwidth = 60;

// Has an action been requested?
if (isset($_REQUEST['action']))
{
    // Do whatever is asked and pitch back to the index page (maybe with args)
    $pitchback = SRP_SelectUrlOfTemplatedPage('frontpage');
    
    switch ($_REQUEST['action'])
    {
        case 'confirmuser':
            $userid = $_GET['id'];
            $confirmid = $_GET['confirmid'];
            
            if (SRP_ConfirmUser($userid, $confirmid))
            {
                $pitchback .= '?loginstatus=3';
            }
            else
            {
                $pitchback .= '?loginstatus=4';
            }
            
            break;
        case 'logout':
            wp_logout();
            break;
        case 'logon':
            if (!SRP_IsUserConfirmed($_POST['srp_login']))
            {
                $pitchback .= '?loginstatus=2';
            }
            else
            {
                $credentials['user_login'] = $_POST['srp_login'];
                $credentials['user_password'] = $_POST['srp_pass'];
                $credentials['remember'] = true;
                $user = wp_signon($credentials);
                if (is_wp_error($user))
                {
                    $pitchback .= '?loginstatus=1';
                }
            }
            break;
    }
    
    header('Location: ' . $pitchback);
    exit();
}
    
SRP_PrintPageStart($srp_leftcolumnwidth);

if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
?>
    <div id="post-<?php the_ID(); ?>" <?php if (function_exists("post_class")) post_class(); else print 'class="post"'; ?>>          
<?php
if (is_user_logged_in())
{
?>
    <h2>Welcome, <?php echo $current_user->display_name; ?>!</h2>
    <div>For security, please log out and close your browser when you are done.</div>
    <div>&nbsp;</div>
    <div><?php SRP_PrintLinkToTemplatedPage('editprofile', 'Update profile');?></div>
    <div><a href="<?php echo SRP_GetLogoutUrl();?>">Log out</a></div>
    <div>&nbsp;</div>
<?php
    if (!SRP_IsUserAdministrator())
    {
        require_once('includes/srp-inc-prizes.php');
        
        $user_minutes_read = get_usermeta($current_user->ID, 'srp_minutes');
        if (strlen($user_minutes_read) == 0)
        {
            $user_minutes_read = 0;
        }
        
        $minutesread = $user_minutes_read;
        $hours = floor($minutesread / 60);
        $minutesread -= $hours * 60;
        $minutes = $minutesread;
        $read_str = "You have read $hours hour" . (($hours == 1) ? '' : 's') . " and $minutes minute" . (($minutes == 1) ? '' : 's') . ' so far.';
        
        $prizeswon = get_usermeta($current_user->ID, 'srp_prizeswon');
?>
        <h4>Your Books</h4>
        <div class="profilebox">
        <div class="profilenumber"><?php echo $read_str; ?></div>
        <!-- Prizes -->
        <div class="profilenumber">
        <?php if (strlen($prizeswon) == 0) { ?>
        <span>No prizes won yet!  Submit more hours to win prizes.</span><br />
        <?php } else { ?>
        <span class="profilenumberheader">So far you've won:</span><br />
        <ul>
        <?php
            $prizearray = explode(';', $prizeswon);
            foreach ($prizearray as $prize)
            {
                if (strlen($prize) == 0) continue;
                $prizename = SRP_HoursToPrizeName($prize);
                $prizecode = SRP_HoursToPrizeCode($prize);
        ?>
            <li><?php echo $prizename; ?> (verification code <?php echo $prizecode; ?>)</li>
        <?php
            }
        ?>
        </ul>
        <?php
        } // prizeswon
          
        // Determine the next prize that may be won
        $nextprize = SRP_NextPrizeFromHours($hours);
        if (is_array($nextprize))
        {
            $minutesleft = ($nextprize['hours'] * 60) - $user_minutes_read;
            $hoursleft = floor($minutesleft / 60);
            $minutesleft -= $hoursleft * 60;
            $minutes = $minutesleft;
            $msg  = "Only $hoursleft hour" . (($hoursleft == 1) ? '' : 's') . " and $minutes minute" . (($minutes == 1) ? '' : 's');
            $msg .= " remaining until you win a $nextprize[name]!";
            ?><div class="SRPNextPrize"><?php echo $msg; ?></div><?php
        }
        
        $srp_gprize_every = get_srptheme_option('srp_gprize_every');
          
        // Print a message saying that the user has been entered for their grand prize choice
        if ($hours >= $srp_gprize_every)
        {
            $srp_gprize_numentries = get_srptheme_option('srp_gprize_numentries');
            
            for ($i = 1; $i <= $srp_gprize_numentries; $i++)
            {
                if ($hours >= ($i * $srp_gprize_every))
                {
                    $morethan = ($i * $srp_gprize_every);
                }
                else break;
            }

            $entries = floor($morethan / $srp_gprize_every);

            $grandprize = SRP_GetGrandPrizeName($current_user->ID);

            $msg  = "You've completed more than $morethan hours of reading! ";
            $msg .= "You've earned $entries entr"; $msg .= ($entries == 1) ? "y" : "ies";
            $msg .= " into the drawing for a $grandprize. We'll draw a winner for this prize at the end of the summer.";
            ?><div>&nbsp;</div><div class="SRPGrandPrizeEntry"><?php echo $msg; ?></div><?php
        }
          
        if (strlen($prizeswon) > 0) :
        ?>
        <div>&nbsp;</div>
        <span style="font-size:smaller;font-style:italic"><?php echo SRP_FormatMessage('srp_hourlynotice'); ?></span>
        <?php endif; ?>
        
        </div><!-- / prizes -->
        <div>&nbsp;</div>
        <div><?php SRP_PrintLinkToTemplatedPage('submitbooks', 'Submit books or hours read');?></div>
        </div>
        <div>&nbsp;</div>
        <h4>Your Reviews</h4>
        <div class="userreviewbox">
        <?php
        $nPending = SRP_GetReviewCountByUser($current_user->ID, 'pending');
	$nReviews = SRP_GetReviewCountByUser($current_user->ID, 'publish');
        if ($nReviews > 0 || $nPending > 0)
        {
            require_once('includes/srp-inc-search.php');
            for ($j = 0; $j <= 1; $j++)
            {
		unset($status);
		if ($j == 0 && $nReviews > 0)
		{
		    $msg  = "You have entered $nReviews review";
		    $msg .= ($nReviews == 1) ? '' : 's';
	            $msg .= " so far.";
		    echo "<div>$msg</div>\n";
		    $status = 'publish';
		}
		else if ($j == 1 && $nPending > 0)
		{
		    $msg  = "You have $nPending review";
		    $msg .= ($nPending == 1) ? '' : 's';
	            $msg .= " pending approval.";
		    echo "<div>$msg</div>\n";
		    $status = 'pending';
		}
		$posts = SRP_GetPostsByUser($current_user->ID, $status);
		echo "<ul>\n";
		for ($i = 0; $i < count($posts); $i++)
		{
		    $title = $posts[$i]['book_title'];
		    $author = $posts[$i]['book_author'];
		    $date = get_date_from_gmt(date('Y-m-d H:i:s', strtotime($posts[$i]['date'])), 'F jS, Y');
		    echo "<li><em>$title</em>, by $author, reviewed on $date.</li>\n";
		}
		echo "</ul>\n";
	    }
        }
        ?>
        <div>&nbsp;</div>
        <div><?php SRP_PrintLinkToTemplatedPage('submitreview', 'Submit a review');?></div>
        <div><?php SRP_PrintLinkToTemplatedPage('reviewpage', 'View other reviews');?></div>
        </div>
<?php
    } // end srp_isadministrator
    else
    {
?>
        <div><?php SRP_PrintLinkToTemplatedPage('statistics', 'View SRP statistics');?></div>
        <div><?php SRP_PrintLinkToTemplatedPage('hoursreset', 'Reset a user\'s time');?></div>
<?php
    }
}
else
{
?>
        <h2><?php the_title(); ?></h2>
        <div class="post-content clearfix"><?php the_content('Read the rest of this page &raquo;'); ?></div>
        <div class="SRPHoursBanner">So far, <?php echo get_srptheme_option('library_name'); ?> readers have logged 
            <span class="SRPHoursNumber"><?php echo floor(SRP_SelectAllMinutes() / 60); ?></span> hours!</div>
        <div class="loginbox">
            <h4>Log in</h4>
            <?php
                if (isset($_REQUEST['loginstatus']))
                {
                    $loginstatus = $_REQUEST['loginstatus'];
                    if ($loginstatus == 1 || $loginstatus == 2)
                    {
            ?>
            <div class="errormsg">Whoops, your login couldn't be processed!</div>
            <?php
                        if ($_REQUEST['loginstatus'] == '2')
                        {
            ?>
            <div>Please check your email and follow the confirmation link before logging in.</div>
            <?php
                        }
                    }
                    else if ($loginstatus == 3)
                    {
            ?>
            <div>Your account has been confirmed!  Please log in below.</div>
            <?php
                    }
                    else if ($loginstatus == 4)
                    {
            ?>
            <div>Your account has already been confirmed, or the confirmation failed.  Please try to log in below.</div>
            <?php
                    }
                }
            ?>
            <form id="SRPLogin" method="post" action="<?php echo site_url('/'); ?>">
            <input type="hidden" name="action" value="logon" />
            <input type="hidden" name="page_id" value="<?php the_ID(); ?>" />
            <p>
            <label <?php if (strpos($reqfields, 'srp_login:') !== FALSE) echo 'class="errormsg"';?>>Username:<br />
            <input type="text" name="srp_login" id="srp_login" class="SRPInputNoSize"
                   value="<?php echo esc_attr(stripslashes($srp_login)); ?>" size="20" />
            </label>
            </p>
            <p>
            <label>Password:<br />
            <input type="password" name="srp_pass" id="srp_pass" class="SRPInputNoSize" size="20" />
            </label>
            </p>
            <p><input type="submit" value="Login" /></p>
            <p><?php SRP_PrintLinkToTemplatedPage('user_reg', 'New user?');?> &nbsp; | &nbsp; <?php SRP_PrintLinkToTemplatedPage('retrievepassword', 'Forgot your password?');?></p>
            </form>
        </div>
<?php
} // end is_user_logged_in()
?>
        </div>
<?php
endif; /* end The Loop */
SRP_PrintPageEnd($srp_leftcolumnwidth, 'srp_login');
?>
