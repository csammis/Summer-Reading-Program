<?php
/*
* Template Name: SRP User Registration
* Controls to register a new user

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

/**** Actual stuff ****/

// If there is a logged-in user, they really don't need to see anything
if (is_user_logged_in())
{
    header('Location: ' . site_url('/'));
    exit();
}

$srp_leftcolumnwidth = 100;

if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */

$action_type = $_POST['action'];
if (empty($action_type))
{
    $action_type = 'start';
}

switch ($action_type)
{
    case 'register':
        require ( ABSPATH . WPINC . '/registration.php' );
        
        /** Collect and validate input **/
        $reqfields = '';
        $srp_pass1 = esc_attr(stripslashes($_POST['srp_pass1']));
        $srp_pass2 = esc_attr(stripslashes($_POST['srp_pass2']));
        $srp_login = esc_attr(stripslashes($_POST['srp_login']));
        if (strlen($srp_login) == 0) $reqfields .= 'srp_login:';
        $srp_fname = esc_attr(stripslashes($_POST['srp_fname']));
        if (strlen($srp_fname) == 0) $reqfields .= 'srp_fname:';
        $srp_lname = esc_attr(stripslashes($_POST['srp_lname']));
        if (strlen($srp_lname) == 0) $reqfields .= 'srp_lname:';
        $srp_school_fall = esc_attr(stripslashes($_POST['srp_school_fall']));
        if (strlen($srp_school_fall) == 0 || $srp_school_fall < 0) $reqfields .= 'srp_school_fall:';
        $srp_school_spring = esc_attr(stripslashes($_POST['srp_school_spring']));
        if (strlen($srp_school_spring) == 0 || $srp_school_spring < 0) $reqfields .= 'srp_school_spring:';
        $srp_grade = esc_attr(stripslashes($_POST['srp_grade']));
        if (strlen($srp_grade) == 0 || $srp_grade < 6) $reqfields .= 'srp_grade:';
        
        $srp_grandprize = esc_attr(stripslashes($_POST['srp_grandprize']));
        if (strlen($srp_grandprize) == 0) $reqfields .= 'srp_grandprize:';
        
        $srp_useragreement = esc_attr(stripslashes($_POST['srp_useragreement']));
        
        // Email and phone requirements are contingent on grade
        $srp_email = esc_attr(stripslashes($_POST['srp_email']));
        $srp_email1 = esc_attr(stripslashes($_POST['srp_email1']));
        $srp_phone = esc_attr(stripslashes($_POST['srp_phone']));
        
        if (strlen($srp_phone) == 0 && $srp_grade  < 8) $reqfields .= 'srp_phone:';
        if ($srp_grade >= 8 && strlen($srp_email) == 0) $reqfields .= 'srp_email:';
        
        // Error precedence:
        //  (1) Ensure all fields are filled in...
        //  (2) ...and the email address is okay...
        //  (3) ...and the username is OK...
        //  (4) ...before messing with the password.
        if (strlen($reqfields) != 0)
        {
            $errorcode = 'reqfields';
        }
        else if (strlen($srp_email) > 0 && $srp_email != $srp_email1)
        {
            $errorcode = 'emailnomatch';
            $reqfields = 'srp_email:';
        }
        else if (strlen($srp_email) > 0 && !is_email($srp_email))
        {
            $errorcode = 'invalidemail';
            $reqfields = 'srp_email:';
        }
        else if (strlen($srp_email) > 0 && email_exists($srp_email))
        {
            $errorcode = 'emailexists';
            $reqfields = 'srp_email:';
        }
        else if (username_exists($srp_login))
        {
            $errorcode = 'userexists';
            $reqfields = 'srp_login:';
        }
        else if (strlen($srp_pass1) == 0 || $srp_pass1 != $srp_pass2)
        {
            $errorcode = 'pass';
        }
        else if (strlen($srp_useragreement) == 0 || $srp_useragreement != 1)
        {
            $errorcode = 'available';
        }
        else
        {
            // If the user hasn't entered an email and has gotten this far,
            // it's a 6th / 7th grader who doesn't need to have one.
            $sendEmail = true;
            if (strlen($srp_email) == 0)
            {
                $srp_email = uniqid() . '@nonexistant.com';
                $sendEmail = false;
            }
            
            // do this thing and redirect to someplace nicer
            $user_id = wp_create_user($srp_login, $srp_pass1, $srp_email);
            if (!$user_id)
            {
                $errorcode = 'create_user_failure';
            }
            else
            {
                // Whoo!  Add meta information, send notification, and log this fine person into the system
                update_user_meta($user_id, 'first_name', $srp_fname);
                update_user_meta($user_id, 'display_name', $srp_fname);
                update_user_meta($user_id, 'last_name', $srp_lname);
                update_user_meta($user_id, 'school_grade', $srp_grade);
                update_user_meta($user_id, 'school_name_fall', $srp_school_fall);
                update_user_meta($user_id, 'school_name_spring', $srp_school_spring);
                update_user_meta($user_id, 'phone', $srp_phone);
                update_user_meta($user_id, 'srp_grandprize', $srp_grandprize);
                
                if ($sendEmail == true)
                {
                    $confirmation_id = uniqid();
                    update_user_meta($user_id, 'confirmation_id', $confirmation_id);
                    SRP_SendNewEmail($user_id, $srp_pass1, $confirmation_id);
                    SRP_PrintPageStart($srp_leftcolumnwidth);
?>
<h2>Thanks for registering!</h2>
<div>Please check your e-mail for a confirmation link.  Follow this link to confirm your account and log in to the site.</div>
<?php
                    break;
                }
                else
                {
                    update_user_meta($user_id, 'srp_noemail', '1');

                    // Log the user in right away
                    $credentials['user_login'] = $srp_login;
                    $credentials['user_password'] = $srp_pass1;
                    $credentials['remember'] = true;
                    $user = wp_signon($credentials);
                    
                    header('Location: ' . site_url('/'));
                    exit();
                }
            }
        }

        if (isset($errorcode))
        {
            $errormsg = '<div class="errormsg">Whoops, your registration couldn\'t be processed!<br />';
            switch ($errorcode)
            {
                case 'pass':
                    $errormsg .= 'Please enter a password and confirm it.';
                    break;
                case 'reqfields':
                    $errormsg .= 'Some required fields were left blank.';
                    break;
                case 'invalidemail':
                    $errormsg .= "The email address you entered ($srp_email) is not a valid email address.";
                    unset($srp_email);
                    break;
                case 'emailexists':
                    $errormsg .= "The email address you entered ($srp_email) is already in use, please use another.";
                    unset($srp_email);
                    break;
                case 'emailnomatch':
                    $errormsg .= 'The email addresses you entered do not match.';
                    unset($srp_email);
                    break;
                case 'userexists':
                    $errormsg .= "The username you requested ($srp_login) already exists.";
                    unset($srp_login);
                    break;
                case 'available':
                    $errormsg .= 'Please agree to the Summer Reading Program user agreement.';
                    break;
                default:
                    $errormsg .= "An unknown error has occured: $errorcode";
                    break;
            }
            $errormsg .= '</div>';
        }
        
    /** Intentional fall-through if registration failed **/
    case 'start':
        SRP_PrintPageStart($srp_leftcolumnwidth);

        if (strlen($errormsg) > 0)
        {
            echo $errormsg;
        }
?>
        <h2><?php the_title(); ?></h2>
        <div class="post-content clearfix">
            <?php the_content('Read the rest of this page &raquo;'); ?>
        </div>
<?php
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
        <form id="SPRRegister" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
        <input type="hidden" name="action" value="register" />
        <input type="hidden" name="page_id" value="<?php the_ID(); ?>" />
        <h4>Your Account</h4>
        <p>
        <label <?php if (strpos($reqfields, 'srp_login:') !== FALSE) echo 'class="errormsg"';?>>Username:<br />
        <input type="text" name="srp_login" id="srp_login" class="SRPInput" value="<?php echo esc_attr(stripslashes($srp_login)); ?>" size="20" />
        </label>
        </p>
        <p>
        <label><span <?php if (strpos($reqfields, 'srp_email:') !== FALSE) echo 'class="errormsg"';?>>E-mail</span> (please do not use your school e-mail address):<br />
        <input type="text" name="srp_email" id="srp_email" class="SRPInput" value="<?php echo esc_attr(stripslashes($srp_email)); ?>" size="20" />
        </label>
        </p>
        <p>
        <label>E-mail (confirm):<br />
        <input type="text" name="srp_email1" id="srp_email1" class="SRPInput" size="20" />
        </label>
        </p>
        <p>
        <label>Password:<br />
        <input type="password" name="srp_pass1" id="srp_pass1" class="SRPInput" size="20" />
        </label>
        </p>
        <p>
        <label>Password (confirm):<br />
        <input type="password" name="srp_pass2" id="srp_pass2" class="SRPInput" size="20" />
        </label>
        </p>
        <p>&nbsp;</p>
        <h4>Your Information</h4>
        <p>
        <label <?php if (strpos($reqfields, 'srp_fname:') !== FALSE) echo 'class="errormsg"';?>>First name:<br />
        <input type="text" name="srp_fname" id="srp_fname" class="SRPInput" value="<?php echo esc_attr(stripslashes($srp_fname)); ?>" size="20" />
        </label>
        </p>
        <p>
        <label <?php if (strpos($reqfields, 'srp_lname:') !== FALSE) echo 'class="errormsg"';?>>Last name:<br />
        <input type="text" name="srp_lname" id="srp_lname" class="SRPInput" value="<?php echo esc_attr(stripslashes($srp_lname)); ?>" size="20" />
        </label>
        </p>
        <p>
        <label <?php if (strpos($reqfields, 'srp_phone:') !== FALSE) echo 'class="errormsg"';?>>Phone:<br />
        <input type="text" name="srp_phone" id="srp_phone" class="SRPInput" value="<?php echo esc_attr(stripslashes($srp_phone)); ?>" size="20" />
        </label>
        </p>
        <p>&nbsp;</p>
        <h4>Your School</h4>
        <p>
        <label <?php if (strpos($reqfields, 'srp_school_spring:') !== FALSE) echo 'class="errormsg"';?>>The school you attended this spring:<br />
            <?php SRP_PrintSpringSchoolSelector($srp_school_spring); ?>
        </label>
        </p>

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
        <script language="javascript">
        function processGradeChange(jqueryUrl)
        {
            var dropdown = document.getElementById('srp_grade');
            var selIndex = dropdown.selectedIndex;
            var grade = dropdown.options[selIndex].value;

            $.post(jqueryUrl,
                { "action" : "g2p", "grade" : grade },
                function (data) {
                    var arrayData = data.split('\n');
                    var gprizeDropdown = document.getElementById('srp_grandprize');
                    gprizeDropdown.options.length = 0;
                    var blankOption = document.createElement('option');
                    blankOption.value = -1;
                    blankOption.text = '';
                    gprizeDropdown.options.add(blankOption);

                    for (i = 0; i < arrayData.length - 1; i += 2)
                    {
                        var id = arrayData[i];
                        var name = arrayData[i + 1];
                        var option = document.createElement('option');
                        option.value = id;
                        option.text = name;
                        gprizeDropdown.options.add(option);
                    }
                });
            return true;
        }

        if (document.addEventListener)
        {
            document.addEventListener('DOMContentLoaded', function() { processGradeChange('<?php echo site_url('/') . '/jquery-processor/'; ?>'); }, false);
        }
        else
        {
            window.onLoad = processGradeChange('<?php echo site_url('/') . '/jquery-processor/'; ?>');
        }
        </script>

        <p>
        <label <?php if (strpos($reqfields, 'srp_grade:') !== FALSE) echo 'class="errormsg"';?>>The grade you will enter this fall:<br />
        <?php SRP_PrintGradeSelector('srp_grade', $srp_grade); ?>
        </label>
        </p>
        <p>
        <label <?php if (strpos($reqfields, 'srp_school_fall:') !== FALSE) echo 'class="errormsg"';?>>The school you will attend this fall:<br />
            <?php SRP_PrintFallSchoolSelector($srp_school_fall); ?>
        </label>
        </p>
        <p>&nbsp;</p>
        <h4>Summer Reading Program Options</h4>
        <p>
        <label>At the end of the summer, users who have read more than 16 hours are entered for their choice of a grand prize. Select the 
               prize you'd like to be entered for:<br />
        <?php if (strpos($reqfields, 'srp_grandprize:') !== FALSE) : ?><span class="errormsg">Please select a grand drawing prize</span><br /><?php endif; ?>
        <?php SRP_PrintGrandPrizeSelector('srp_grandprize'); ?>
        </label>
        </p>
        <h4>User Agreement</h4>
        <div><input type="checkbox" name="srp_useragreement" id="srp_useragreement" value="1" /> <?php echo SRP_FormatMessage('srp_regagreement'); ?></div>
        <div>&nbsp;</div>
        <div><input type="submit" value="Create Profile" />&nbsp;<input type="reset" value="Reset fields" /></div>
        </form>
<?php
        } // end get_srptheme_option('program_active')
        break; // end case 'start' and 'register'
    default:
        echo "hello";
        break;
    }
    
endif; /* end The Loop */
    
SRP_PrintPageEnd($srp_leftcolumnwidth, 'srp_login');
?>
