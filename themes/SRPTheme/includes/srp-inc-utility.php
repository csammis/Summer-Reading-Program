<?php
/* 
* srp-inc-utility.php
* Various utility functions.

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

//  SRP_DaysBetweenDates
//  SRP_SendEmail
//  SRP_SelectUrlOfTemplatedPage
//  SRP_SelectPageIdsForNav
//  SRP_FormatMessage
//  SRP_GetLocalDate

require_once('srp-inc-users.php');

/*
 * SRP_DaysBetweenDates
 * Returns the number of days between $date1 and $date2.
 */
function SRP_DaysBetweenDates($date1, $date2)
{ 
    $current = $date1; 
    $datetime2 = date_create($date2); 
    $count = 0; 
    while(date_create($current) < $datetime2)
    { 
        $current = gmdate("Y-m-d", strtotime("+1 day", strtotime($current))); 
        $count++; 
    } 
    return $count; 
}

/*
 * SRP_SendEmail
 * Wraps the PHPMailer object to send an email via GMail.  Uses three SRP theme options:
 *  gmail_account
 *  gmail_password
 *  gmail_reply_to
 */
function SRP_SendEmail($subject, $body, $email)
{
    require_once('class.phpmailer.php');
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = "ssl";
    $mail->Host       = "smtp.gmail.com";
    $mail->Port       = 465;
    $mail->Username   = get_srptheme_option('gmail_account');
    $mail->Password   = get_srptheme_option('gmail_password');

    $reply_to = get_srptheme_option('gmail_reply_to');
    if (strlen($reply_to) == 0)
    {
        $reply_to = get_srptheme_option('gmail_account');
    }
    $mail->SetFrom($reply_to, $reply_to);
    $mail->AddReplyTo($reply_to, $reply_to);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);

    $mail->AddAddress($email, "Summer Reading Program participant");

    if (!$mail->Send())
    {
        echo 'couldn\'t send mail';
    }
}

/*
 * SRP_SelectUrlOfTemplatedPage
 * Selects the WP page with the specified page template and returns its URI.
 */
function SRP_SelectUrlOfTemplatedPage($templatename)
{
    global $wpdb;
    
    if (substr($templatename, strlen($templatename) - 4) != '.php')
    {
        $templatename .= '.php';
    }
    
    $select = "SELECT POST_ID FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s";
    $page_id = $wpdb->get_var($wpdb->prepare($select, '_wp_page_template', $templatename));
    
    if (isset($page_id))
    {
        return get_page_link($page_id);
    }
    
    return FALSE;
}

/*
 * SRP_SelectPageIdsForNav
 */
function SRP_SelectPageIdsForNav()
{
    global $wpdb;
    
    $page_access_value = '0';
    if (is_user_logged_in())
    {
        global $current_user;
        get_currentuserinfo();
        if (SRP_IsUserAdministrator())
        {
            $page_access_value = '2'; // Administrator
        }
        else
        {
            $page_access_value = '1'; // Regular logged-in user
        }
    }
    
    $select  = "SELECT p.id, p.post_title, tp.meta_value FROM $wpdb->posts p ";
    $select .= "INNER JOIN $wpdb->postmeta pm ON (pm.post_id = p.id AND pm.meta_key = %s AND pm.meta_value LIKE %s) ";
    $select .= "INNER JOIN $wpdb->postmeta tp ON (tp.post_id = p.id AND tp.meta_key = %s) ";
    $select .= "WHERE (p.post_type = %s AND p.post_status = %s) ORDER BY p.post_title";
    
    $query = $wpdb->prepare($select, 'page_access', "%$page_access_value%", '_wp_page_template', 'page', 'publish');

    $IDs       = $wpdb->get_col($query, 0);
    $names     = $wpdb->get_col($query, 1);
    $templates = $wpdb->get_col($query, 2);
    
    $retval = array();
    for ($i = 0; $i < count($IDs); $i++)
    {
        if ($templates[$i] == 'contact.php')
        {
            $contact_id = $IDs[$i];
            $contact_name = $names[$i];
        }
        else
        {
            $retval[$names[$i]] = get_page_link($IDs[$i]);
        }
    }
    
    if (isset($contact_id))
    {
        $retval[$contact_name] = get_page_link($contact_id);
    }
    
    return $retval;
}

/*
 * SRP_FormatMessage
 * Formats an SRP theme message with the specificed associative array of tags.
 */
function SRP_FormatMessage($message, $tags = array())
{
    $retval = get_srptheme_message($message);
    
    // Common tags
    $tags['libraryname'] = get_srptheme_option('library_name');
    
    // Specified tags
    foreach ($tags as $key => $value)
    {
        $tagname = "%%$key%%";
        $retval = str_replace($tagname, $value, $retval);
    }
    
    return $retval;
}

/*
 * SRP_GetLocalDate
 * Returns a local date (using get_option('gmt_offset')) from a GMT date
 */
function SRP_GetLocalDate($date)
{
    $d = date_create($date);
    $d = date_add($d, new DateInterval("PT" + get_option('gmt_offset') + "H"));
    return $d->format('F jS, Y');
}

?>
