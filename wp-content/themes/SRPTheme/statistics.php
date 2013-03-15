<?php
/*
* Template Name: SRP Statistics
* Provides the statistics page for the summer reading program administrators

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
require_once('includes/srp-inc-reporting.php');
require_once('includes/srp-inc-lists.php');

$action = '';
$pivot = 0;

if (isset($_POST['action']))
{
	$action = $_POST['action'];
}

if (isset($_POST['pivot']))
{
	$pivot = $_POST['pivot'];
}

$pivotname = '';
switch ($pivot)
{
	case 1: $pivotname  = 'Olathe Main Library'; break;
	case 2: $pivotname  = 'Indian Creek Branch'; break;
	default: $pivotname = 'all locations'; break;
}

// Handle the CSV action by redirecting to the dynamically generated content
if ($action == 'csv')
{
    header('Content-type: text/csv');
    header("Content-Disposition: attachment; filename=\"OPL Teens user report ($pivotname).csv\"");
    echo SRP_GetAllUsersCSV($pivot);
    exit();
}

SRP_PrintPageStart(100);

$pagetitle = '';
$pageid = '';

if (have_posts()) :
	the_post(); /* start The Loop so we can get the page ID */
	$pagetitle = get_the_title();
	$pageid = get_the_ID();
endif;



// Calculate stats for the selected pivot location
$usercount = SRP_GetUserCount(false, $pivot);
$confcount = SRP_GetConfirmedUserCount($pivot);
$usernumbers = "$usercount ($confcount have confirmed)";

$minutes = SRP_SelectAllMinutes($pivot);
$hours = floor($minutes / 60);
$totaltime = "$minutes ($hours hours)";

$program_open_date = get_srptheme_option('program_open_date');
$days_open = ((time() - $program_open_date) / 60 / 60 / 24);
$reading_hours_open = round($days_open * 20, 2);


?>
<h2><?php echo $pagetitle; ?></h2>
<div>&nbsp;</div>
<h4>Displaying statistics for users registered to pick up at <?php echo $pivotname; ?></h4>
<div>
<form id="SRPSwitchPivot" method="POST" action="<?php echo get_permalink($pageid);?>">
Switch to: <select name="pivot">
<option value="0">All locations</option>
<option value="1">Olathe Main Library</option>
<option value="2">Indian Creek Branch</option>
</select>
&nbsp;
<input type="Submit" value="Switch" />
</form>
</div>
<div>&nbsp;</div>
<div>&nbsp;</div>

<div>
<span class="SRPStatLabel">Number of registered users:</span>&nbsp;
<span class="SRPStatValue"><?php echo $usernumbers;?></span>&nbsp;
<form id="SRPCreateCSV" method="POST" action="<?php echo get_permalink($pageid); ?>">
<input type="hidden" name="action" value="csv" />
<input type="hidden" name="pivot" value="<?php echo $pivot; ?>" />
<input type="submit" value="Create registered user CSV" />
</form>
</div>
  
<div>
<span class="SRPStatLabel">Number of reviews posted:</span>&nbsp;
<span class="SRPStatValue"><?php echo SRP_GetReviewCount($pivot);?></span>
</div>

<div>
<span class="SRPStatLabel">Total number of minutes / pages logged:</span>&nbsp;
<span class="SRPStatValue"><?php echo $totaltime; ?></span>
</div>

<div>&nbsp;</div>

<div>
<span class="SRPStatLabel">The Summer Reading Program is allowing <?php echo $reading_hours_open; ?> hours per user to be logged since program open
(20 hours out of each 24 hour period).</span>
</div>

<div>&nbsp;</div>

<div>
<span class="SRPStatLabel">Users who have submitted reviews (from most reviews to least):</span><br />
<ol>
<?php
$names = SRP_SelectUsersWithReviews($pivot);
foreach ($names as $name)
{
    echo "   <li>$name</li>\n";
}
?>
</ol>
</div>

<div>
<span class="SRPStatLabel">Users who have submitted at least one minute (from most minutes to least):</span><br />
<ol>
<?php
$names = SRP_SelectUsersWithHours(1, $pivot);
foreach ($names as $name)
{
    echo "   <li>$name</li>\n";
}
?>
</ol>
</div>

<div><span class="SRPStatLabel">Number of users by grade:</span><br />
<ul>
<?php
$names = SRP_SelectUsersByGrade($pivot);
foreach (array_keys($names) as $grade)
{
    echo "    <li>Grade $grade: " . count($names[$grade]) . " </li>\n";
}
?>
</ul>
</div>

<div><span class="SRPStatLabel">Number of users by school attendance in spring:</span><br />
<ol>
<?php
$sid2name = SRP_GetAllSchoolNames();

$counts = SRP_SelectSchoolsByMostReviewers('spring', $pivot);
$total = 0;
foreach ($counts as $school => $count)
{
    echo '   <li>' . $sid2name[$school] . ": $count</li>\n";
    $total += $count;
}
echo "  <li>TOTAL: $total</li>\n";
?>
</ol>
</div>

<div><span class="SRPStatLabel">Number of users by school attendance in fall:</span><br />
<ol>
<?php
$counts = SRP_SelectSchoolsByMostReviewers('fall', $pivot);
$total = 0;
foreach ($counts as $school => $count)
{
    echo '   <li>' . $sid2name[$school] . ": $count</li>\n";
    $total += $count;
}
echo "  <li>TOTAL: $total</li>\n";
?>
</ol>
</div>

<?php SRP_PrintPageEnd(); ?>
