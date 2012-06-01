<?php
/*
* Template Name: SRP "Fix User"
* Page to control fixing a user's entered information

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
SRP_AuthRedirect($SRP_AUTH_ADMIN);

require_once('includes/srp-inc-template.php');

$srp_leftcolumnwidth = 100;

$action = 0;
$username = '';
$useremail = '';
$userid = '';
$firstname = '';
$lastname = '';
$gprize = '';
$minutes = 0;

if (isset($_POST['action']))
{
    $action = $_POST['action'];
    switch ($action)
    {
    case 1:
        $username = $_POST['username'];
        $useremail = $_POST['useremail'];

        if (strlen($username) > 0)
        {
            $user = get_user_by('login', $username);
        }
        else if (strlen($useremail) > 0)
        {
            $user = get_user_by('email', $useremail);
        }

        if (!isset($user) || $user == false)
        {
            $action = 0;
            $errormsg = 'User could not be found.';
        }
        else
        {
            $userid = $user->ID; // lookup user
        
            $username = $user->user_login;
            $firstname = get_user_meta($userid, 'first_name'); $firstname = $firstname[0];
            $lastname = get_user_meta($userid, 'last_name'); $lastname = $lastname[0];
            $gprize = get_user_meta($userid, 'srp_grandprize'); $gprize = $gprize[0];
            $minutes = get_user_meta($userid, 'srp_minutes'); $minutes = $minutes[0];
        }
        break;

    case 2:
        $userid = $_POST['userid'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $gprize = $_POST['gprize'];
        $minutes = $_POST['minutes'];

        if (strlen($firstname) <= 0 || strlen($lastname) <= 0 || !is_numeric($minutes) || $minutes < 0)
        {
            $action = 1;
            $errormsg = 'Information entered is invalid.';
        }
        else
        {
            update_user_meta($userid, 'first_name', $firstname);
            update_user_meta($userid, 'last_name', $lastname);
            update_user_meta($userid, 'srp_grandprize', $gprize);
            update_user_meta($userid, 'srp_minutes', $minutes);
        }
        break;
    }
}

$pageid = '';
if (have_posts()) :
  the_post(); /* start The Loop so we can get the page ID */
  $pageid = get_the_ID();
endif;


SRP_PrintPageStart($srp_leftcolumnwidth);
?>
<h3>Fix a user</h3>

<?php if (isset($errormsg)) : ?><p class="errormsg"><?php echo $errormsg; ?></p><?php endif; ?>

<form id="FixUser" method="POST" action="<?php echo get_permalink($pageid);?>">
<input type="hidden" id="action" name="action" value="<?php echo $action + 1; ?>" />

<?php if ($action == 0) : ?>
<p>Step one: enter the user's login or email address</p>
<div><label>Login:</label>&nbsp;<input type="text" class="SRPInput" name="username" size="20" /></div>
<div><label>Email:</label>&nbsp;<input type="text" class="SRPInput" name="useremail" size="20" /></div>
<input type="submit" value="Get Profile" />
<?php endif; ?>

<?php if ($action == 1) : ?>
<p>Step two: review and correct information for <?php echo $username; ?></p>
<input type="hidden" id="userid" name="userid" value="<?php echo $userid; ?>" />
<div><label>First name:</label>&nbsp;<input type="text" class="SRPInputNoSize" name="firstname" value="<?php echo $firstname; ?>" size="20" /></div>
<div><label>Last name:</label>&nbsp;<input type="text" class="SRPInputNoSize" name="lastname" value="<?php echo $lastname; ?>" size="20" /></div>
<div><label>Grand prize:</label>&nbsp;<?php SRP_PrintGrandPrizeSelector('gprize', $gprize); ?></div>
<div><label>Minutes read:</label>&nbsp;<input type="text" class="SRPInputNoSize" name="minutes" value="<?php echo $minutes; ?>" size="4" /></div>
<input type="submit" value="Update Profile" />
<?php endif; ?>

<?php if ($action == 2) : ?>
<p>User updated!</p>
<?php endif; ?>

</form>
<?php
SRP_PrintPageEnd($srp_leftcolumnwidth);
?>
