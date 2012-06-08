<?php
/*
* Template Name: SRP Reset Hours
* A minimal interface for reseting a specific user's reading time.

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

$action_type = $_POST['action'];
if (empty($action_type))
{
	$action_type = 'start';
}

SRP_PrintPageStart($srp_leftcolumnwidth);
if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
?>
<h2><?php the_title(); ?></h2>
<div>&nbsp;</div>
<?php

switch ($action_type)
{
    case 'reset':
        $reqfields = '';
        $srp_username = esc_attr(stripslashes($_POST['srp_username']));
        if (strlen($srp_username) == 0) $reqfields = 'srp_username:';
        $srp_firstname = esc_attr(stripslashes($_POST['srp_firstname']));
        if (strlen($srp_firstname) == 0) $reqfields = 'srp_firstname:';
        $srp_userminutes = esc_attr(stripslashes($_POST['srp_userminutes']));
        if (strlen($srp_userminutes) == 0 || $srp_userminutes <= 0) $reqfields = 'srp_userminutes:';

        if (strlen($reqfields) == 0)
        {
            $id = SRP_SelectUser($srp_username, $srp_firstname);
            if ($id < 0)
            {
                $reqfields = 'srp_username:';
            }
            else
            {
                SRP_UpdateUserMinutes($id, $srp_userminutes);
            ?><h4>The user's hours have been updated.</h4><?php
            }
        }
        break;
}

    if (strlen($reqfields) > 0)
    {
        $errormsg = "<div class=\"errormsg\">No such user exists in the system.</div>\n";
        unset($srp_username);
        unset($srp_firstname);
        unset($srp_userminutes);
        echo $errormsg;
    }
?>
<form id="SRPResetHours" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
<input type="hidden" name="action" value="reset" />
<div>
    <label>The user's login name:<br />
    <input type="text" name="srp_username" id="srp_username" class="SRPInputNoSize"
           value="<?php echo esc_attr(stripslashes($srp_username)); ?>" size="20" />
    </label>
</div>
<div>
    <label>The user's first name:<br />
    <input type="text" name="srp_firstname" id="srp_firstname" class="SRPInputNoSize"
           value="<?php echo esc_attr(stripslashes($srp_firstname)); ?>" size="20" />
    </label>
</div>
<?php SRP_PrintJavascriptNumberValidator(); ?>
<div>
    <label>How many minutes should this user have?<br />
    <input type="text" name="srp_userminutes" id="srp_userminutes" class="SRPInputNoSize"
           value="<?php echo esc_attr(stripslashes($srp_userminutes)); ?>" size="10" onKeyPress="return onlyNumbers(event.charCode || event.keyCode);" />
    </label>
</div>
<div>
<input type="submit" value="Reset hours" />
</div>
</form>
<?php
	endif; /* end The Loop */
	SRP_PrintPageEnd($srp_leftcolumnwidth);
?>
