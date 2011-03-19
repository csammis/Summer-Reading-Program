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
function SRP_GetAllUsersCSV()
{
    global $wpdb;

    $select  = "SELECT DISTINCT u.id AS ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_grade.meta_value AS grade_value, ";
    $select .= "                u.user_email AS email, ";
    $select .= "                um_school.meta_value AS school_value ";
    $select .= "FROM $wpdb->users u ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_lname ON (um_lname.user_id = u.id AND um_lname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_grade ON (um_grade.user_id = u.id AND um_grade.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_school ON (um_school.user_id = u.id AND um_school.meta_key = %s) ";
    $select .= "ORDER BY um_fname.meta_key";

    $query = $wpdb->prepare($select, 'first_name', 'last_name', 'school_grade', 'school_name_fall');
    $id_col = $wpdb->get_col($query, 0);
    $fname_col = $wpdb->get_col($query, 1);
    $lname_col = $wpdb->get_col($query, 2);
    $grade_col = $wpdb->get_col($query, 3);
    $email_col = $wpdb->get_col($query, 4);
    $school_col = $wpdb->get_col($query, 5);

    $sid2name = SRP_GetAllSchoolNames();

    $retval = "Name,Grade,Email,School\n";
    for ($i = 0; $i < count($id_col); $i++)
    {
        $firstname = $fname_col[$i];
        $lastname = $lname_col[$i];
        $retval .= "$firstname $lastname,";
        $retval .= $grade_col[$i] . ',';
        $retval .= $email_col[$i] . ',';
        $retval .= $sid2name[$school_col[$i]] . "\n";
    }

    return $retval;
}

/*
 * SRP_SelectAllMinutes
 * Returns the number of minutes logged by SRP users.
 * Uses metadata values:
 *  srp_minutes
 */
function SRP_SelectAllMinutes()
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
 * Returns the number of SRP users, not including administrators by default.
 */
function SRP_GetUserCount($bIncludeAdmins = false)
{
    global $wpdb;

    if ($bIncludeAdmins == false)
    {
        $select = "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key LIKE %s AND meta_value NOT LIKE %s";
        return $wpdb->get_var($wpdb->prepare($select, '%_capabilities', '%administrator%'));
    }

    $select = "SELECT COUNT(*) FROM $wpdb->users";
    return $wpdb->get_var($wpdb->prepare($select));
}

/*
 * SRP_GetReviewCount
 * Returns the number of published reviews.
 */
function SRP_GetReviewCount()
{
    global $wpdb;
    
    $select = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s";
    return $wpdb->get_var($wpdb->prepare($select, 'post', 'publish'));
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
function SRP_SelectUsersWithReviews()
{
    global $wpdb;
    
    $select  = "SELECT DISTINCT u.id as ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_grade.meta_value AS grade_value ";
    $select .= "FROM $wpdb->users u ";
    $select .= "INNER JOIN $wpdb->posts p ON u.id = p.post_author ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_lname ON (um_lname.user_id = u.id AND um_lname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_grade ON (um_grade.user_id = u.id AND um_grade.meta_key = %s) ";
    $select .= "WHERE (p.post_type = %s AND p.post_status = %s) ";
    $select .= "ORDER BY u.id ";

    $query = $wpdb->prepare($select, 'first_name', 'last_name', 'school_grade', 'post', 'publish');
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
function SRP_SelectUsersWithHours($hourlimit = 0)
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
    $interval = SRP_DaysBetweenDates('2010-5-22', $now->format('Y-m-d'));
    $hours_max = $interval * 24;

    for ($i = 0; $i < count($id_col); $i++)
    {
        $firstname = $fname_col[$i];
        $lastname = $lname_col[$i];
        $grade = $grade_col[$i];
        if (!isset($grade))
            $grade = '&lt;not set&gt;';
        $hours = floor($minutes_col[$i] / 60.0);
        if ($hours >= $hourlimit)
        {
            $str = $firstname . ' ' . $lastname . ", grade $grade [$hours hours]";
            if ($hours > $hours_max)
            {
                $str = '<span style="color:red">' . $str . " (email " . $email_col[$i] . " or phone " . $phone_col[$i] . ")</span>";
            }
            $names[$hours] = $str;
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
function SRP_SelectUsersByGrade()
{
    global $wpdb;
    $select  = "SELECT DISTINCT u.id as ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_grade.meta_value AS grade_value ";
    $select .= "FROM $wpdb->users u ";
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
 * SRP_SelectSchoolsByMostReviews
 * Returns a map whose keys are school IDs and values are number of users reporting that school.
 * Uses metadata values:
 *  school_name_spring
 *  school_name_fall
 */
function SRP_SelectSchoolsByMostReviewers($school = 'spring')
{
    global $wpdb;
    $select  = "SELECT um.meta_value FROM $wpdb->usermeta um ";
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

?>
