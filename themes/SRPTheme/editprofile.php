<?php
/*
* Template Name: SRP Edit Profile
* Controls for editing a user's profile

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

require_once(ABSPATH . WPINC . '/registration.php');
require_once('includes/srp-inc-template.php');

$srp_leftcolumnwidth = 100;

$user_id = $current_user->ID;
$is_administrator = SRP_IsUserAdministrator();
$srp_login  = $current_user->user_login;
$srp_email  = $current_user->user_email;
$srp_fname  = get_usermeta($user_id, 'first_name');
$srp_lname  = get_usermeta($user_id, 'last_name');

$action_type = $_POST['action'];
if (empty($action_type))
{
    $action_type = 'start';
    $srp_grade  = get_usermeta($user_id, 'school_grade');
    $srp_school_fall = get_usermeta($user_id, 'school_name_fall');
    $srp_school_spring = get_usermeta($user_id, 'school_name_spring');
    $srp_phone  = get_usermeta($user_id, 'phone');
}

$profileUpdated = false;

switch ($action_type)
{
    case 'updateprofile':
       
        /** Collect and validate input **/
        $reqfields = '';
    
        $srp_pass1 = esc_attr(stripslashes($_POST['srp_pass1']));
        $srp_pass2 = esc_attr(stripslashes($_POST['srp_pass2']));
        if ($is_administrator == false)
        {
            $srp_school_fall = esc_attr(stripslashes($_POST['srp_school_fall']));
            if (strlen($srp_school_fall) == 0 || $srp_school_fall == -1) $reqfields .= 'srp_school_fall:';
            $srp_school_spring = esc_attr(stripslashes($_POST['srp_school_spring']));
            if (strlen($srp_school_spring) == 0 || $srp_school_spring == -1) $reqfields .= 'srp_school_spring:';
            $srp_grade = esc_attr(stripslashes($_POST['srp_grade']));

            // Email and phone requirements are contingent on grade
            $srp_phone = esc_attr(stripslashes($_POST['srp_phone']));

            $checkemail = $srp_grade >= 8;

            if (!$checkemail && strlen($srp_phone) == 0) $reqfields .= 'srp_phone:';
            if ($checkemail && strlen($srp_email) == 0) $reqfields .= 'srp_email:';
        }
    
        // Error precedence:
        //  (1) Ensure all fields are filled in...
        //  (2) ...and if the password is filled in, check it.
        if (strlen($reqfields) != 0)
        {
            $errorcode = 'reqfields';
        }
        else if (strlen($srp_pass1) != 0 && ($srp_pass1 != $srp_pass2))
        {
            $errorcode = 'pass';
        }
        else
        {
            if ($checkemail === FALSE)
            {
                // Substitute the admin email
            }
            
            // do this thing and redirect to someplace nicer
            $user_id = $current_user->ID;
      
            // Whoo!  Add meta information, send notification, and log this fine person into the system
            if ($is_administrator == false)
            {
                update_usermeta($user_id, 'school_grade', $srp_grade);
                update_usermeta($user_id, 'school_name_fall', $srp_school_fall);
                update_usermeta($user_id, 'school_name_spring', $srp_school_spring);
                if (strlen($srp_phone) > 0)
                {
                    update_usermeta($user_id, 'phone', $srp_phone);
                }
            }
      
            if (strlen($srp_pass1) > 0)
            {
                $userdata = array('ID' => $user_id, 'user_pass' => $srp_pass1);
                wp_update_user($userdata);
            }

            $profileUpdated = true;
        }

        /** Intentional fall-through if profile update failed **/
    case 'start':
        SRP_PrintPageStart($srp_leftcolumnwidth);
        if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
        
        if (isset($errorcode))
        {
            $errormsg = '<div class="errormsg">Whoops, your profile couldn\'t be updated!<br />';
            switch ($errorcode)
            {
                case 'pass':
                    $errormsg .= 'The entered passwords do not match.';
                    break;
                case 'reqfields':
                    $errormsg .= 'Some required fields were left blank.';
                    break;
                default:
                    $errormsg .= 'An unknown error has occured: ' . $errorcode;
                    break;
            }
            $errormsg .= '</div>';
            echo $errormsg;
        }
    
        if ($profileUpdated)
        {
            echo '<div><h3>Profile updated!</h3></div>';
        }
        
?>
<div>&nbsp;</div>
<form id="SPRRegister" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
<input type="hidden" name="action" value="updateprofile" />
<input type="hidden" name="page_id" value="<?php the_ID(); ?>" />
<h4>Your Account</h4>
<div>
<label>Username:<br />
<input type="text" name="srp_login" id="srp_login" class="SRPInput" disabled="disabled"
value="<?php echo esc_attr(stripslashes($srp_login)); ?>" size="20" />
</label>
</div>
<div>
<label>E-mail:<br />
<input type="text" name="srp_email" id="srp_email" class="SRPInput" disabled="disabled"
value="<?php echo esc_attr(stripslashes($srp_email)); ?>" size="20" />
</label>
</div>
<?php
    if ($is_administrator == false)
    {
    ?>
<div>&nbsp;</div>
<h4>Your Information</h4>
<div>
    <label <?php if (strpos($reqfields, 'srp_fname:') !== FALSE) echo 'class="errormsg"';?>>First name:<br />
    <input type="text" name="srp_fname" id="srp_fname" class="SRPInput" disabled="disabled"
    value="<?php echo esc_attr(stripslashes($srp_fname)); ?>" size="20" />
    </label>
</div>
<div>
    <label <?php if (strpos($reqfields, 'srp_lname:') !== FALSE) echo 'class="errormsg"';?>>Last name:<br />
    <input type="text" name="srp_lname" id="srp_lname" class="SRPInput" disabled="disabled"
    value="<?php echo esc_attr(stripslashes($srp_lname)); ?>" size="20" />
    </label>
</div>
<div>
    <label <?php if (strpos($reqfields, 'srp_phone:') !== FALSE) echo 'class="errormsg"';?>>Phone:<br />
    <input type="text" name="srp_phone" id="srp_phone" class="SRPInput"
    value="<?php echo esc_attr(stripslashes($srp_phone)); ?>" size="20" />
    </label>
</div>
<div>&nbsp;</div>
<h4>Your School</h4>
<div>
    <label <?php if (strpos($reqfields, 'srp_school_spring:') !== FALSE) echo 'class="errormsg"';?>>The school you attended this spring:<br />
    <?php SRP_PrintSpringSchoolSelector($srp_school_spring); ?>
    </label>
</div>
<div>
    <label <?php if (strpos($reqfields, 'srp_grade:') !== FALSE) echo 'class="errormsg"';?>>The grade you will enter this fall:<br />
    <?php SRP_PrintGradeSelector('srp_grade', $srp_grade); ?>
    </label>
</div>
<div>
    <label <?php if (strpos($reqfields, 'srp_school_fall:') !== FALSE) echo 'class="errormsg"';?>>The school you will attend this fall:<br />
    <?php SRP_PrintFallSchoolSelector($srp_school_fall); ?>
    </label>
</div>
    <?php
    }
    ?>
<div>&nbsp;</div>
<div><em>To change your password, enter a new password here. Otherwise, leave it blank.</em></div>
<div>
<label>Password:<br />
<input type="password" name="srp_pass1" id="srp_pass1" class="SRPInput" size="20" />
</label>
</div>
<div>
<label>Password (confirm):<br />
<input type="password" name="srp_pass2" id="srp_pass2" class="SRPInput" size="20" />
</label>
</div>
<div>
<input type="submit" value="Update Profile" />&nbsp;<input type="reset" value="Reset fields" />
</div>
</form>
<?php
        endif; /* end The Loop */
        SRP_PrintPageEnd($srp_leftcolumnwidth, 'srp_phone'); // printCenterColumnEnd('srp_login');
        break; // end case 'start' and 'register'
    default:
        echo "hello";
        break;
    }
?>
