<?php
/*
* srp-inc-users.php
* User maintainance functions.

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

//  SRP_IsUserAdministrator
//  SRP_SendPasswordResetEmail
//  SRP_SelectUser
//  SRP_UpdateUserMinutes
//  SRP_GetReviewerInformation
//  SRP_VerifyUserAccount
//  SRP_IsUserConfirmed
//  SRP_ConfirmUser
//  SRP_AuthRedirect

$SRP_UNAUTHENTICATED = 0;
$SRP_AUTHENTICATED = 1;
$SRP_AUTH_ADMIN = 2;

/*
 * SRP_IsUserAdministrator
 * Returns a value indicating whether the currently logged-in user is an administrator.
 */
function SRP_IsUserAdministrator()
{
    return is_user_logged_in() && current_user_can('administrator');
}

function SRP_SendPasswordResetEmail($userid)
{
    require_once(ABSPATH . WPINC . '/registration.php');
    $newpassword = uniqid();
    $userdata = array('ID' => $userid, 'user_pass' => $newpassword);
    wp_update_user($userdata);

    $body  = "A password reset has been processed for your Summer Reading Program profile. Please use this new password to log in to the Summer Reading website, then go to Update Profile to change your password back to something of your choosing.<br /><br />";
    $body .= "Your new password is:  $newpassword<br /><br />";
    $userinfo = get_userdata($userid);
    SRP_SendEmail("Summer Reading Program password reset", $body, $userinfo->user_email);
}

function SRP_SendNewEmail($userid, $password, $confirmid)
{
    require_once(ABSPATH . WPINC . '/registration.php');
    
    $confirmurl = site_url('/') . "/?action=confirmuser&id=$userid&confirmid=$confirmid";
    
    $body  = "Please follow this link to confirm your new Summer Reading Program account.<br /><br />";
    $body .= "<a href=\"$confirmurl\">$confirmurl</a><br /><br />";
    
    $userinfo = get_userdata($userid);
    SRP_SendEmail("Summer Reading Program - confirm your account", $body, $userinfo->user_email);
}

function SRP_SelectUser($username, $firstname)
{
    global $wpdb;
    $select  = "SELECT DISTINCT u.ID AS ID FROM $wpdb->users u ";
    $select .= "INNER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "WHERE u.user_login = %s AND um_fname.meta_value = %s";

    $query = $wpdb->prepare($select, 'first_name', $username, $firstname);
    $id_col = $wpdb->get_col($query, 0);
    if (count($id_col) == 0)
    {
        return -1;
    }
    else return $id_col[0];
}

function SRP_UpdateUserMinutes($userid, $userminutes)
{
    require_once('srp-inc-prizes.php');
  
    update_user_meta($userid, 'srp_minutes', $userminutes);
    SRP_AwardHourlyPrizesWithinBoundary($userid, 0, $userminutes);
    
    global $wpdb;
    $select = "DELETE FROM $wpdb->usermeta WHERE user_id = %s AND meta_key = %s";
    $wpdb->query($wpdb->prepare($delete, $userid, 'srp_milestone%'));
    SRP_SetGrandPrizeEntriesWithinBoundary($userid, 0, $userminutes);
}

function SRP_GetReviewerInformation($userid, $bIncludePhone = false, $bIncludeEmail = false)
{
    global $wpdb;

    $select  = "SELECT DISTINCT u.id AS ID, ";
    $select .= "                um_fname.meta_value AS fname_value, ";
    $select .= "                um_lname.meta_value AS lname_value, ";
    $select .= "                um_phone.meta_value AS phone_value, ";
    $select .= "                u.user_email AS email ";
    $select .= "FROM $wpdb->users u ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_fname ON (um_fname.user_id = u.id AND um_fname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_lname ON (um_lname.user_id = u.id AND um_lname.meta_key = %s) ";
    $select .= "LEFT OUTER JOIN $wpdb->usermeta um_phone ON (um_phone.user_id = u.id AND um_phone.meta_key = %s) ";
    $select .= "WHERE u.id = %s ";
    $select .= "ORDER BY um_fname.meta_key";

    $query = $wpdb->prepare($select, 'first_name', 'last_name', 'phone', $userid);
    $id_col = $wpdb->get_col($query, 0);
    $fname_col = $wpdb->get_col($query, 1);
    $lname_col = $wpdb->get_col($query, 2);
    $phone_col = $wpdb->get_col($query, 3);
    $email_col = $wpdb->get_col($query, 4);
    for ($i = 0; $i < count($id_col); $i++)
    {
        $firstname = $fname_col[$i];
        $lastname = $lname_col[$i];
        $retval = $firstname . ' ' . $lastname;
        if ($bIncludePhone == true)
        {
            $retval .= ' [Phone:&nbsp;&nbsp;' . $phone_col[$i] . ']';
        }
        if ($bIncludeEmail == true)
        {
            $retval .= ' [Email:&nbsp;&nbsp;' . $email_col[$i] . ']';
        }
        return $retval;
    }
}

function SRP_VerifyUserAccount($username, $grade_num, $school_fall)
{
    global $wpdb;
    
    $select  = "SELECT DISTINCT u.id AS ID ";
    $select .= "FROM $wpdb->users u ";
    $select .= "INNER JOIN $wpdb->usermeta um_grade ON (um_grade.user_id = u.id AND um_grade.meta_key = %s AND um_grade.meta_value = %s) ";
    $select .= "INNER JOIN $wpdb->usermeta um_school ON (um_school.user_id = u.id AND um_school.meta_key = %s AND um_school.meta_value = %s) ";
    $select .= "WHERE u.user_login = %s";

    $query = $wpdb->prepare($select, 'school_grade', $grade_num, 'school_name_fall', $school_fall, $username);
    $id_col = $wpdb->get_col($query, 0);
    return count($id_col) == 0 ? -1 : $id_col[0];
}

/*
 * SRP_IsUserConfirmed
 * Returns a value indicating whether or not the user has been confirmed
 */
function SRP_IsUserConfirmed($username)
{
    global $wpdb;
    
    $selectgrade  = "SELECT um_grade.meta_value AS GRADE ";
    $selectgrade .= "FROM $wpdb->users u ";
    $selectgrade .= "INNER JOIN $wpdb->usermeta um_grade ON (um_grade.user_id = u.id AND um_grade.meta_key = %s) ";
    $selectgrade .= "WHERE u.user_login = %s";
    $query = $wpdb->prepare($selectgrade, 'school_grade', $username);
    $grade_col = $wpdb->get_col($query, 0);
    $grade = $grade_col[0];
    
    $select  = "SELECT DISTINCT u.id AS ID ";
    $select .= "FROM $wpdb->users u ";
    $select .= "INNER JOIN $wpdb->usermeta um_confirm ON (um_confirm.user_id = u.id AND um_confirm.meta_key = %s) ";
    $select .= "WHERE u.user_login = %s";
    
    $query = $wpdb->prepare($select, 'confirmation_id', $username);
    $id_col = $wpdb->get_col($query, 0);
    return count($id_col) == 0 ? true : false;
}

/*
 * SRP_ConfirmUser
 * If the passed confirmid is correct, removes the confirmation_id from the database and returns TRUE.
 */
function SRP_ConfirmUser($userid, $confirmid)
{
    global $wpdb;
    
    $select  = "SELECT DISTINCT u.id AS ID ";
    $select .= "FROM $wpdb->users u ";
    $select .= "INNER JOIN $wpdb->usermeta um_confirm ON (um_confirm.user_id = u.id AND um_confirm.meta_key = %s AND um_confirm.meta_value = %s) ";
    $select .= "WHERE u.id = %s";
    
    $query = $wpdb->prepare($select, 'confirmation_id', $confirmid, $userid);
    $id_col = $wpdb->get_col($query, 0);
    if (count($id_col) == 1)
    {
        $delete = "DELETE FROM $wpdb->usermeta WHERE user_id = %s AND meta_key = %s";
        $wpdb->query($wpdb->prepare($delete, $userid, 'confirmation_id'));
        return true;
    }
    
    return false;
}

/*
 * SRP_AuthRedirect
 * Compares the current user's authorization level and redirects to the frontpage if it doesn't match the requested level.
 */
function SRP_AuthRedirect($userclass)
{
    global $SRP_UNAUTHENTICATED, $SRP_AUTHENTICATED, $SRP_AUTH_ADMIN;
    
    $bRedirect = false;
    if ($userclass == $SRP_UNAUTHENTICATED)
    {
        // Nothing to do here
    }
    else if ($userclass == $SRP_AUTHENTICATED)
    {
        $bRedirect = !is_user_logged_in();
    }
    else if ($userclass == $SRP_AUTH_ADMIN)
    {
        require_once('srp-inc-utility.php');
        $bRedirect = !SRP_IsUserAdministrator();
    }

    if ($bRedirect === true)
    {
        header('Location: ' . site_url('/'));
        exit();
    }
}

?>
