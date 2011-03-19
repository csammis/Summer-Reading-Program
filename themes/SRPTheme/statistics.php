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

$srp_leftcolumnwidth = 100;

$action = $_POST['action'];

// Handle the CSV action by redirecting to the dynamically generated content
if ($action == 'csv')
{
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="OPL Teens user report.csv"');
    echo SRP_GetAllUsersCSV();
    exit();
}

SRP_PrintPageStart($srp_leftcolumnwidth);

if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
?>
<h2><?php the_title(); ?></h2>
<div>&nbsp;</div>

<div>
<span class="SRPStatLabel">Number of registered users:</span>&nbsp;<span class="SRPStatValue"><?php echo SRP_GetUserCount();?></span>
&nbsp;
<form id="SRPCreateCSV" method="POST" action="<?php echo get_permalink(get_the_ID()); ?>">
<input type="hidden" name="action" value="csv" />
<input type="submit" value="Create registered user CSV" />
</form>
</div>

<div><span class="SRPStatLabel">Number of reviews posted:</span>&nbsp;<span class="SRPStatValue"><?php echo SRP_GetReviewCount();?></span></div>
<?php
    $minutes = SRP_SelectAllMinutes();
    $hours = floor($minutes / 60);
?>
<div><span class="SRPStatLabel">Total number of minutes / pages logged:</span>&nbsp;<span class="SRPStatValue"><?php echo "$minutes ($hours hours)"; ?></span></div>
<div>&nbsp;</div>
<div><span class="SRPStatLabel">Users who have submitted reviews (from most reviews to least):</span><br />
  <ol>
  <?php
    $names = SRP_SelectUsersWithReviews();
    foreach ($names as $name)
    {
      echo "   <li>$name</li>\n";
    }
  ?>
  </ol>
</div>
<div><span class="SRPStatLabel">Users who have submitted at least one hour (from most hours to least):</span><br />
  <ol>
  <?php
    $names = SRP_SelectUsersWithHours(1);
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
    $names = SRP_SelectUsersByGrade();
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
    
    $counts = SRP_SelectSchoolsByMostReviewers('spring');
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
    $counts = SRP_SelectSchoolsByMostReviewers('fall');
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
<?php

endif; /* end The Loop */
SRP_PrintPageEnd($srp_leftcolumnwidth);

?>
