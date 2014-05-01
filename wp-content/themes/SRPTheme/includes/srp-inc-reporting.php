<?php
/*
* srp-inc-reporting.php
* Functions related to statistics about the SRP site and its users.

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

//  SRP_GetAllUsersCSV
//  SRP_SelectAllMinutes
//  SRP_GetUserCount
//  SRP_GetReviewCount
//  SRP_GetReviewCountByUser
//  SRP_SelectUsersWithReviews
//  SRP_SelectUsersWithHours
//  SRP_SelectUsersByGrade
//  SRP_SelectSchoolsByMostReviewers

/*
 * SRP_GetAllUsersCSV
 * Returns a comma-separated string for all SRP users with columns Name, Grade, Email, School
 * Uses metadata values:
 *  first_name
 *  last_name
 *  school_grade
 *  school_name_fall
 * Uses SRP methods:
 *  printFallSchoolName
 */
function SRP_GetAllUsersCSV($pivot)
{
    global $wpdb;

    require_once('srp-inc-prizes.php');

    $select  = "SELECT DISTINCT u.id AS ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_grade.meta_value AS grade_value, ";
    $select .= "                u.user_email AS email, ";
    $select .= "                um_school.meta_value AS school_value ";
    $select .= "FROM $wpdb->users u ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'u.id', $pivot);
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_lname ON (um_lname.user_id = u.id AND um_lname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_grade ON (um_grade.user_id = u.id AND um_grade.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_school ON (um_school.user_id = u.id AND um_school.meta_key = %s) ";
    $select .= "ORDER BY u.id";

    $query = $wpdb->prepare($select, 'first_name', 'last_name', 'school_grade', 'school_name_fall');
    $id_col = $wpdb->get_col($query, 0);
    $fname_col = $wpdb->get_col($query, 1);
    $lname_col = $wpdb->get_col($query, 2);
    $grade_col = $wpdb->get_col($query, 3);
    $email_col = $wpdb->get_col($query, 4);
    $school_col = $wpdb->get_col($query, 5);

    $sid2name = SRP_GetAllSchoolNames();

    $retval = "Name,ID,Grade,Email,School,GrandPrize\n";
    for ($i = 0; $i < count($id_col); $i++)
    {
        $id = $id_col[$i];
        $firstname = $fname_col[$i];
        $lastname = $lname_col[$i];
        $retval .= "$firstname $lastname,$id,";
        $retval .= $grade_col[$i] . ',';
        $retval .= $email_col[$i] . ',';
        $retval .= $sid2name[$school_col[$i]] . ',';
        $retval .= SRP_GetGrandPrizeName($id) . "\n";
    }

    return $retval;
}
  
/*
 * SRP_SelectAllMinutes
 * Returns the number of minutes logged by SRP users.
 * Uses metadata values:
 *  srp_minutes
 */
function SRP_SelectAllMinutes($pivot)
{
    global $wpdb;
    
    $select = "SELECT um.meta_value FROM $wpdb->usermeta um WHERE um.meta_key = %s";
    $metavalue_col = $wpdb->get_col($wpdb->prepare($select, 'srp_minutes'));
    $minutes = 0;
    foreach ($metavalue_col as $value)
    {
        $minutes = $minutes + $value;
    }
    
    return round($minutes);
}

/*
 * SRP_GetUserCount
 * Returns the number of SRP users, optionally not including administrators
 */
function SRP_GetUserCount($bIncludeAdmins, $pivot)
{
    global $wpdb;

    if ($bIncludeAdmins == false)
    {
        $select  = "SELECT COUNT(1) FROM $wpdb->usermeta umc ";
        $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'umc.user_id', $pivot);
        $select .= "WHERE umc.meta_key LIKE %s AND umc.meta_value NOT LIKE %s";
        return $wpdb->get_var($wpdb->prepare($select, '%_capabilities', '%administrator%'));
    }

    $select  = "SELECT COUNT(1) FROM $wpdb->users u ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'u.id', $pivot);
    return $wpdb->get_var($wpdb->prepare($select));
}

function SRP_GetConfirmedUserCount($pivot)
{
    global $wpdb;
    $select  = "SELECT COUNT(1) FROM $wpdb->usermeta caps ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'caps.user_id', $pivot);
    $select .= "LEFT OUTER JOIN $wpdb->usermeta confirmed ON caps.user_id = confirmed.user_id AND confirmed.meta_key = %s ";
    $select .= "WHERE caps.meta_key LIKE %s AND caps.meta_value NOT LIKE %s AND confirmed.meta_value IS NULL";

    return $wpdb->get_var($wpdb->prepare($select, 'confirmation_id', '%_capabilities', '%administrator%'));
}

/*
 * SRP_GetReviewCount
 * Returns the number of published reviews.
 */
function SRP_GetReviewCount($pivot)
{
    global $wpdb;
    
    $select  = "SELECT COUNT(1) FROM $wpdb->posts p ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'p.post_author', $pivot);
    $select .= 'WHERE p.post_type = %s AND p.post_status = %s ';
    $select .= "AND p.post_author NOT IN (SELECT user_id FROM $wpdb->usermeta WHERE meta_key LIKE %s AND meta_value LIKE %s)";
    return $wpdb->get_var($wpdb->prepare($select, 'post', 'publish', '%_capabilities', '%administrator%'));
}

/*
 * SRP_GetReviewCountByUser
 * Returns the number of reviews authored by a specific user ID,
 * optionally specifiying a post status ('publish' by default).
 */
function SRP_GetReviewCountByUser($userid, $status = 'publish')
{
    global $wpdb;
    
    $select = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s AND post_author = %d";
    return $wpdb->get_var($wpdb->prepare($select, 'post', $status, $userid));
}

/*
 * SRP_SelectUsersWithReviews
 * Returns an array of users who have published reviews, sorted descending by number of reviews.
 * Uses metadata values:
 *  first_name
 *  last_name
 *  school_grade
 * Uses SRP methods:
 *  SRP_GetReviewCountByUser
 */
function SRP_SelectUsersWithReviews($pivot)
{
    global $wpdb;
    
    $select  = "SELECT DISTINCT u.id as ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_grade.meta_value AS grade_value ";
    $select .= "FROM $wpdb->users u ";
    $select .= "INNER JOIN $wpdb->posts p ON u.id = p.post_author ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'u.id', $pivot);
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_lname ON (um_lname.user_id = u.id AND um_lname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_grade ON (um_grade.user_id = u.id AND um_grade.meta_key = %s) ";
    $select .= "WHERE (p.post_type = %s AND p.post_status = %s) AND u.id NOT IN ";
    $select .= "(SELECT user_id FROM $wpdb->usermeta WHERE meta_key LIKE %s AND meta_value LIKE %s) ";
    $select .= "ORDER BY u.id ";

    $query = $wpdb->prepare($select, 'first_name', 'last_name', 'school_grade',
                            'post', 'publish', '%_capabilities', '%administrator%');
    $id_col = $wpdb->get_col($query, 0);
    $fname_col = $wpdb->get_col($query, 1);
    $lname_col = $wpdb->get_col($query, 2);
    $grade_col = $wpdb->get_col($query, 3);
    
    // $names becomes a list-of-lists indexed by post counts
    $names = array();
    for ($i = 0; $i < count($id_col); $i++)
    {
        $firstname = $fname_col[$i];
        $lastname = $lname_col[$i];
        $grade = $grade_col[$i];
        if (!isset($grade))
            $grade = '&lt;not set&gt;';
        $post_count = SRP_GetReviewCountByUser($id_col[$i]);
        $names[$post_count][] = $firstname . ' ' . $lastname . ", grade $grade [$post_count reviews]";
    }

    krsort($names);
    
    $retval = array();
    // For each distinct post count...
    foreach ($names as $name)
    {
        // For each user that has that post count...
        foreach ($name as $n)
        {
            // Add that user to the return list
            $retval[] = $n;
        }
    }
    
    return $retval;
}

/*
 * SRP_SelectUsersWithHours
 * Returns a list of users who have logged at least $hourlimit hours (default 0).
 * If a user has logged more hours than there are days in the program, its entry in the
 * return list is wrapped with <span style="font-color:red"> and includes their contact info.
 * Uses metadata values:
 *  first_name
 *  last_name
 *  school_grade
 *  phone
 *  srp_minutes
 */
function SRP_SelectUsersWithHours($hourlimit, $pivot)
{
    global $wpdb;

    $select  = "SELECT DISTINCT u.id as ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_grade.meta_value AS grade_value, ";
    $select .= "                um_minutes.meta_value AS minutes_value, ";
    $select .= "                u.user_email AS email, ";
    $select .= "                um_phone.meta_value AS phone_value ";
    $select .= "FROM $wpdb->users u ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'u.id', $pivot);
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_lname ON (um_lname.user_id = u.id AND um_lname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_grade ON (um_grade.user_id = u.id AND um_grade.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_phone ON (um_phone.user_id = u.id AND um_phone.meta_key = %s) ";
    $select .= "INNER JOIN $wpdb->usermeta um_minutes ON (um_minutes.user_id = u.id AND um_minutes.meta_key = %s) ";
    $select .= "ORDER BY u.id";

    $names = array();
    $query = $wpdb->prepare($select, 'first_name', 'last_name', 'school_grade', 'phone', 'srp_minutes');
    $id_col = $wpdb->get_col($query, 0);
    $fname_col   = $wpdb->get_col($query, 1);
    $lname_col   = $wpdb->get_col($query, 2);
    $grade_col   = $wpdb->get_col($query, 3);
    $minutes_col = $wpdb->get_col($query, 4);
    $email_col   = $wpdb->get_col($query, 5);
    $phone_col   = $wpdb->get_col($query, 6);

    // Determine whether the user is completely out-of-bounds for hours logged
    $now = new DateTime();
    $interval = SRP_DaysBetweenDates('2011-5-31', $now->format('Y-m-d'));
    $hours_max = $interval * 24;

    for ($i = 0; $i < count($id_col); $i++)
    {
        $firstname = $fname_col[$i];
        $lastname = $lname_col[$i];
        $grade = $grade_col[$i];
        if (!isset($grade))
            $grade = '&lt;not set&gt;';
        $minutes = $minutes_col[$i];
        $hours = floor($minutes_col[$i] / 60.0);
        //if ($hours >= $hourlimit)
        {
            $email = $email_col[$i];
            $str  = $firstname . ' ' . $lastname . ", grade $grade [$minutes minutes] ($hours hours)";
            $str .= " email: $email";
            //if ($hours > $hours_max)
            //{
            //    $str = '<span style="color:red">' . $str . " (email " . $email_col[$i] . " or phone " . $phone_col[$i] . ")</span>";
            //}
            $names[$minutes] = $str;
        }
    }

    krsort($names);
    
    return $names;
}

/*
 * SRP_SelectUsersByGrade
 * Returns a list of lists of users, keyed by grade number ascending.
 * Uses metadata values:
 *  first_name
 *  last_name
 *  school_grade
 */
function SRP_SelectUsersByGrade($pivot)
{
    global $wpdb;
    $select  = "SELECT DISTINCT u.id as ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_grade.meta_value AS grade_value ";
    $select .= "FROM $wpdb->users u ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'u.id', $pivot);
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_lname ON (um_lname.user_id = u.id AND um_lname.meta_key = %s) ";
    $select .= "INNER JOIN $wpdb->usermeta um_grade ON (um_grade.user_id = u.id AND um_grade.meta_key = %s) ";
    
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_confirm ON (um_confirm.user_id = u.id AND um_confirm.meta_key = %s) ";
    $select .= "WHERE um_confirm.user_id IS NULL ";
    
    $select .= "ORDER BY u.id";

    $names = array();
    $query = $wpdb->prepare($select, 'first_name', 'last_name', 'school_grade', 'confirmation_id');
    $id_col = $wpdb->get_col($query, 0);
    $fname_col = $wpdb->get_col($query, 1);
    $lname_col = $wpdb->get_col($query, 2);
    $grade_col = $wpdb->get_col($query, 3);
    for ($i = 0; $i < count($id_col); $i++)
    {
        $firstname = $fname_col[$i];
        $lastname = $lname_col[$i];
        $grade = $grade_col[$i]; if (!isset($grade)) $grade = '&lt;not set&gt;';
        $names[$grade][] = $firstname . ' ' . $lastname;
    }

    ksort($names);

    return $names;
}

/*
 * SRP_SelectUsersByPrize
 * Returns a list of lists of users, keyed by grand prize selection ID ascending.
 * Uses metadata values:
 *  first_name
 *  last_name
 *  ...something
 */
function SRP_SelectUsersByPrize($pivot)
{
    global $wpdb;
    $select  = "SELECT DISTINCT u.id as ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_prize.meta_value AS prize_value ";
    $select .= "FROM $wpdb->users u ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'u.id', $pivot);
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_lname ON (um_lname.user_id = u.id AND um_lname.meta_key = %s) ";
    $select .= "INNER JOIN $wpdb->usermeta um_prize ON (um_prize.user_id = u.id AND um_prize.meta_key = %s) ";
    
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_confirm ON (um_confirm.user_id = u.id AND um_confirm.meta_key = %s) ";
    $select .= "WHERE um_confirm.user_id IS NULL ";
    
    $select .= "ORDER BY u.id";

    $names = array();
    $query = $wpdb->prepare($select, 'first_name', 'last_name', 'srp_grandprize', 'confirmation_id');
    $id_col = $wpdb->get_col($query, 0);
    $fname_col = $wpdb->get_col($query, 1);
    $lname_col = $wpdb->get_col($query, 2);
    $prize_col = $wpdb->get_col($query, 3);
    for ($i = 0; $i < count($id_col); $i++)
    {
        $firstname = $fname_col[$i];
        $lastname = $lname_col[$i];
        $prize = get_srptheme_option("srp_gprize$prize_col[$i]");
        $names[$prize][] = $firstname . ' ' . $lastname;
    }

    ksort($names);

    return $names;
}

/*
 * SRP_SelectSchoolsByMostReviews
 * Returns a map whose keys are school IDs and values are number of users reporting that school.
 * Uses metadata values:
 *  school_name_spring
 *  school_name_fall
 */
function SRP_SelectSchoolsByMostReviewers($school, $pivot)
{
    global $wpdb;
    $select  = "SELECT um.meta_value FROM $wpdb->usermeta um ";
    $select .= SRP_CreatePivotJoin($wpdb->usermeta, 'um.user_id', $pivot);
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_confirm ON (um_confirm.user_id = um.user_id AND um_confirm.meta_key = %s) ";
    $select .= "WHERE um_confirm.user_id IS NULL AND um.meta_key = %s";
    $query = $wpdb->prepare($select, 'confirmation_id', "school_name_$school");
    $metavalue_col = $wpdb->get_col($query, 0);
    $counts = array();
    foreach ($metavalue_col as $value)
    {
        $counts[$value] = $counts[$value] + 1;
    }

    ksort($counts);

    return $counts;
}

function SRP_CreatePivotJoin($tablename, $userid, $pivot)
{
    if ($pivot == 0)
    {
        return '';
    }

    return "INNER JOIN $tablename ump ON (ump.user_id = $userid AND ump.meta_key = 'srp_pickup' AND ump.meta_value = $pivot) ";
}
  
?>
