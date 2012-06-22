<?php
/*
* Template Name: SRP Contact Us
* Simple template with contact information

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
require_once('includes/recaptchalib.php');

$RC_pub_key = get_srptheme_option('recaptcha_public');
$RC_priv_key = get_srptheme_option('recaptcha_private');

if (isset($_POST['action']) && ($RC_pub_key != '' && $RC_priv_key != ''))
{
    $firstname = esc_attr(stripslashes($_POST['firstname']));
    $lastname = esc_attr(stripslashes($_POST['lastname']));
    $emailaddr = esc_attr(stripslashes($_POST['emailaddr']));
    $question = esc_attr(stripslashes($_POST['question']));

    if ($firstname == '' || $lastname == '' || $emailaddr == '' || $question == '')
    {
        $errormsg = 'A required field was left blank';
    }
    else
    {
        require_once('includes/srp-inc-utility.php');

        $resp = recaptcha_check_answer ($RC_priv_key,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
        if (!$resp->is_valid)
        {
            $errormsg = 'The ReCAPTCHA was not filled in correctly.';
        }
        else
        {
            $subject = 'Contact from Summer Reading Program';
            $body = "<html><body><div>Name: $firstname $lastname</div><div>Email address: $emailaddr</div><div>Question:<br />$question</div></body></html>";
            $to = get_srptheme_option('gmail_account');
            SRP_SendEmail($subject, $body, $to);
            $successmessage = "Thank you! Your question has been sent and will be answered as soon as possible.";
        }
    }
}

$srp_leftcolumnwidth = 60;

SRP_PrintPageStart($srp_leftcolumnwidth);
if (isset($successmessage))
{
?>
<h2>Thank you for your question!</h2>
<div>Your question has been sent and will be answered as soon as possible.</div>
<?php
}
else
{
    if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
?>
<div id="post-<?php the_ID(); ?>" <?php if (function_exists("post_class")) post_class(); else print 'class="post"'; ?>>
<h2><?php the_title(); ?></h2>
<div class="post-content clearfix"><?php the_content('Read the rest of this page &raquo;'); ?></div>
</div>
<?php
        if (strlen($RC_pub_key) > 0 && strlen($RC_priv_key) > 0)
        {
?>
<h3>Ask a Question</h3>
<div>
<?php if (isset($errormsg)) : ?><div class="errormsg"><?php echo $errormsg; ?></div><?php endif; ?>
<div>Please fill in all fields.</div>
<div>&nbsp;</div>
<form id="SRPContactForm" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
<input type="hidden" name="action" value="submitquestion" />
<div>First name: <input type="text" name="firstname" id="firstname" class="SRPInput" value="<?php echo esc_attr(stripslashes($firstname)); ?>" size="20" /></div>
<div>Last name: <input type="text" name="lastname" id="lastname" class="SRPInput" value="<?php echo esc_attr(stripslashes($lastname)); ?>" size="20" /></div>
<div>Email address: <input type="text" name="emailaddr" id="emailaddr" class="SRPInput" value="<?php echo esc_attr(stripslashes($emailaddr)); ?>" size="20" /></div>
<div>Your question:<br />
<textarea rows="5" cols="40" name="question" id="question" class="SRPInput"><?php echo esc_attr(stripslashes($question)); ?></textarea>
</div>
<div>Enter the words shown below in order to prove you are not a robot.</div>
<?php
            echo recaptcha_get_html($RC_pub_key);
?>
<div><input type="submit" value="Send Message" onClick="this.disabled=true; this.value='Sending...'; this.form.submit();" /></div>
</form>
</div>
<?php
        }
    endif; /* end The Loop */
}

SRP_PrintPageEnd($srp_leftcolumnwidth);
?>
