<?php
/*
* Template Name: SRP Prize Drawing
* Provides admin controls for drawing weekly prizes

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

require_once('includes/srp-inc-utility.php');
require_once('includes/srp-inc-template.php');
require_once('includes/srp-inc-prizes.php');

if (!SRP_IsUserAdministrator())
{
    header('Location: ' . site_url('/'));
    exit();
}

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
    case 'select':
        $reqfields = '';
        $srp_prizenum = esc_attr(stripslashes($_POST['srp_prizenum']));
        if (strlen($srp_prizenum) == 0 || $srp_prizenum <= 0)
        {
            $reqfields = 'srp_prizenum:';
        }
        else
        {
            $ids = SRP_SelectNReviewersFromLastWeek($srp_prizenum);
            $diff = $srp_prizenum - count($ids);
            if ($diff != 0)
            {
                $last_drawing = get_option("SRP_LastDrawing", "");
                $last_local_date = get_date_from_gmt(date('Y-m-d H:i:s', strtotime($last_drawing)), 'F jS, Y');
                echo "<div class=\"SRPPrizeWarning\">";
                echo "$diff prizes could not be given away because there were not enough reviewers since the last drawing on $last_local_date.";
                echo "</div>\n";
                echo "<div>&nbsp;</div>\n";
            }

            $manualNotificationIDs = array();
            $autoNotificationIDs = array();
            foreach ($ids as $userid)
            {
                $srp_noemail = get_usermeta($userid, 'srp_noemail');
                if (strlen($srp_noemail) != 0)
                {
                    $manualNotificationIDs[] = $userid;
                }
                else
                {
                    SRP_SendReviewerDrawingPrizeEmail($userid);
                    $autoNotificationIDs[] = $userid;
                }
            }

            if (count($autoNotificationIDs) > 0)
            {
?>
<div><span class="SRPStatLabel">The following reviewers have been emailed to pick up a prize:</span></div>
<ol>
<?php
                foreach ($autoNotificationIDs as $userid)
                {
                    echo '   <li>' . SRP_GetReviewerInformation($userid) . "</li>\n";
                }
?>
</ol>
<?php
            }
      
            if (count($manualNotificationIDs) > 0)
            {
?>
<div><span class="SRPStatLabel">The following reviewers have no email; please notify them that they have won a prize:</span></div>
<ol>
<?php
                foreach ($manualNotificationIDs as $userid)
                {
                    echo '   <li>' . SRP_GetReviewerInformation($userid, true) . "</li>\n";
                }
?>
</ol>
<?php
            }
        }
        break;
    
    case 'grandselect':
        $prizes = SRP_SelectGrandPrizeWinners();

        $manualNotificationIDs = array();
        $autoNotificationIDs = array();
?>
<div><span class="SRPStatLabel">The following grand prizes have been drawn. Please contact the winners:</span></div>
<ol>
<?php
        foreach (array_keys($prizes) as $prize)
        {
            $userids = $prizes[$prize];
            $selected_user = $userids[rand(0, count($userids) - 1)];
            $srp_noemail = get_usermeta($selected_user, 'srp_noemail');
            if (strlen($srp_noemail) != 0)
            {
                $userinfo = SRP_GetReviewerInformation($selected_user, true);
            }
            else
            {
                $userinfo = SRP_GetReviewerInformation($selected_user, true, true);
            }

            $prizename = get_srptheme_option("srp_gprize$prize");
?>
<li><?php echo $prizename; ?> goes to <?php echo $userinfo; ?></li>
<?php   } ?>
</ol>
<?php
        break;
}
?>
<h4>Reviewer Prize Drawing</h4>
<?php
  if (strlen($reqfields) > 0)
  {
    $errormsg = "<div class=\"errormsg\">Please enter a number of prizes greater than zero.</div>\n";
    unset($srp_prizenum);
    echo $errormsg;
  }
?>
<form id="SPRPrizeDrawing" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
<input type="hidden" name="action" value="select" />
<p>
<label>Enter the number of prizes to be given away:&nbsp;
<input type="text" name="srp_prizenum" id="srp_prizenum" class="SRPInputNoSize"
     value="<?php echo esc_attr(stripslashes($srp_prizenum)); ?>" size="5" />
</label>
</p>
<p>
<input type="submit" value="Distribute Prizes" onClick="this.disabled=true; this.value='Please Wait...'; this.form.submit();" />&nbsp;
<input type="reset" value="Reset fields" />
</p>
</form>
<div>&nbsp;</div>
<hr />
<div>&nbsp;</div>
<h4>Grand Prize Drawing</h4>
<form id="SRPGrandPrizeDrawing" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
<p>
<input type="hidden" name="action" value="grandselect" />
<input type="submit" value="Distribute Grand Prizes" onClick="this.disabled=true; this.value='Please Wait...'; this.form.submit();" />
</p>
</form>
<?php
  endif; /* end The Loop */
  SRP_PrintPageEnd($srp_leftcolumnwidth);
?>
