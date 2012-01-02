<?php
/*
* Template Name: SRP Password Retrieve
* Controls for user password retrieval

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

// If there is a logged-in user, they really don't need to see anything
if (is_user_logged_in()) { header('Location: ' . site_url('/')); exit(); }

$srp_leftcolumnwidth = 100;

$action_type = $_POST['action'];
if (empty($action_type))
{
    $action_type = 'start';
}

SRP_PrintPageStart($srp_leftcolumnwidth);
if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */        
?>
<h2><?php the_title(); ?></h2>
<?php
switch ($action_type)
{
    case 'retrieve':
        /** Collect and validate input **/
		$srp_login = esc_attr(stripslashes($_POST['srp_login']));
        $srp_grade = esc_attr(stripslashes($_POST['srp_grade']));
		$srp_school_fall = esc_attr(stripslashes($_POST['srp_school_fall']));
		
		$user_id = SRP_VerifyUserAccount($srp_login, $srp_grade, $srp_school_fall);
		$srp_noemail = get_usermeta($user_id, 'srp_noemail');
		if ($user_id == -1 || $srp_noemail == '1')
		{
?>
<div class="post-content clearfix">Sorry, we cannot automatically reset your password. Please contact the library for assistance.</div>
<?php
		}
		else
		{
			SRP_SendPasswordResetEmail($user_id);
?>
<div class="post-content clearfix">Your password has been reset and e-mailed to you. <a href="<?php echo site_url('/');?>">Return to the home page to log in.</a></div>
<?php
		}
		break;

    case 'start':
?>
<div class="post-content clearfix"><?php the_content('Read the rest of this page &raquo;'); ?></div>
<div>&nbsp;</div>
<form id="SPRRegister" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
<input type="hidden" name="action" value="retrieve" />
<input type="hidden" name="page_id" value="<?php the_ID(); ?>" />
<div>
    <label>Username:<br />
    <input type="text" name="srp_login" id="srp_login" class="SRPInput" value="<?php echo esc_attr(stripslashes($srp_login)); ?>" size="20" />
    </label>
</div>
<div><label>The grade you will enter this fall:<br /><?php SRP_PrintGradeSelector('srp_grade', $srp_grade); ?></label></div>
<div><label>The school you will attend this fall:<br /><?php SRP_PrintFallSchoolSelector($srp_school_fall); ?></label></div>
<div>&nbsp;</div>
<div><input type="submit" value="Retrieve password" /></div>
</form>
<?php
        break; // end case 'start'
    default:
        echo "hello";
        break;
    }
	
	endif; /* end The Loop */
	SRP_PrintPageEnd($srp_leftcolumnwidth, 'srp_login');
?>
