<?php
/*
* srp-inc-prizes.php
* Prize-related functionality

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

//  SRP_SelectNReviewersFromLastWeek
//  SRP_SelectGrandPrizeWinners
//  SRP_SendReviewerDrawingPrizeEmail
//  SRP_HoursToPrizeName
//  SRP_HoursToPrizeCode
//  SRP_NextPrizeFromHours
//  SRP_AwardUserHourBasedPrize
//  SRP_SetUserPrizeMilestone
//  SRP_GetPrizeSettings
//  SRP_AwardHourlyPrizesWithinBoundary
//  SRP_SetGrandPrizeEntriesWithinBoundary
//  SRP_GetGrandPrizeName

require_once('srp-inc-utility.php');

/*
 * SRP_SelectNReviewersFromLastWeek
 * This does not actually select from last week but from last drawing time.
 */
function SRP_SelectNReviewersFromLastWeek($nReviewers)
{
    global $wpdb;
    $last_drawing = get_option("SRP_LastDrawing", "");
  
    $admin_userids = '';
    $select = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key LIKE %s AND meta_value LIKE %s";
    $query = $wpdb->get_results($wpdb->prepare($select, '%_capabilities', '%administrator%'));
    for ($i = 0; $i < count($query); $i++)
    {
        if ($i > 0)
        {
            $admin_userids .= ',';
        }
        $admin_userids .= $query[$i]->user_id;
    }
  
    $params[] = 'post';
    $params[] = 'publish';
    $params[] = $admin_userids;
  
    $select  = "SELECT DISTINCT u.id as ID ";
    $select .= "FROM $wpdb->users u ";
    $select .= "INNER JOIN $wpdb->posts p ON u.id = p.post_author ";
    $select .= "WHERE (p.post_type = %s AND p.post_status = %s) AND u.id NOT IN (%s) ";
    $select .= "AND p.post_date_gmt <= UTC_TIMESTAMP() ";
    if (strlen($last_drawing) > 0)
    {
        $select .= "AND p.post_date_gmt > %s ";
        $params[] = $last_drawing;
    }

    $select .= "ORDER BY u.id";

    $query = $wpdb->prepare($select, $params);
    $id_col = $wpdb->get_col($query, 0);

    $id_size = count($id_col);
    if ($id_size < $nReviewers)
    {
        $nReviewers = $id_size;
    }
    
    $winner_ids = array();

    while (count($winner_ids) < $nReviewers)
    {
        $random_id = $id_col[rand(0, $id_size - 1)];
        if (!in_array($random_id, $winner_ids))
        {
            $winner_ids[] = $random_id;
        }
    }

    $timestamp = $wpdb->get_var("SELECT UTC_TIMESTAMP()");
    update_option("SRP_LastDrawing", $timestamp);

    return $winner_ids;
}

function SRP_SelectGrandPrizeWinners()
{
    global $wpdb;

    $select  = "SELECT um2.meta_value, u.id ";
    $select .= "FROM $wpdb->users u ";
    $select .= "INNER JOIN $wpdb->usermeta um ON (um.user_id = u.id AND um.meta_key LIKE %s) ";
    $select .= "INNER JOIN $wpdb->usermeta um2 ON (um2.user_id = u.id AND um2.meta_key = %s) ";
    $select .= "ORDER BY um2.meta_value";

    $query = $wpdb->prepare($select, 'srp_milestone%', 'srp_grandprize');
    $prize_col = $wpdb->get_col($query, 0);
    $id_col = $wpdb->get_col($query, 1);

    $prizes = array();
    for ($i = 0; $i < count($prize_col); $i++)
    {
        $prizes[$prize_col[$i]][] = $id_col[$i];
    }
    return $prizes;
}

/*
 * SRP_HoursToPrizeName
 * Gets the name of the prize for the specified prize ID.
 */
function SRP_HoursToPrizeName($prizeid)
{
    return get_srptheme_option("srp_hprizename$prizeid");
}

/*
 * SRP_HoursToPrizeCode
 * Gets the verification code for the specified prize ID.
 */
function SRP_HoursToPrizeCode($prizeid)
{
    return get_srptheme_option("srp_hprizecode$prizeid");
}

/*
 * SRP_NextPrizeFromHours
 * Returns an associative array representing the next prize to be won from the specified number of hours.
 */
function SRP_NextPrizeFromHours($hours)
{
    $prizes = SRP_GetPrizeSettings();
    
    $hours2ids = array();
    foreach ($prizes as $id => $values)
    {
        $key = $values['hours'] - 0;
        $hours2ids[$key] = $id;
    }
    
    ksort($hours2ids);
    foreach ($hours2ids as $key => $value)
    {
        if ($hours < $key)
        {
            $foundId = $value;
            break;
        }
    }
    
    if (isset($foundId))
    {
        return $prizes[$foundId];
    }
    
    return 'SRP Error:  no prizes configured';
}

function SRP_SetUserPrizeMilestone($userid, $milestone)
{
    update_user_meta($userid, 'srp_milestone' . $milestone, '1');
}

function SRP_SendReviewerDrawingPrizeEmail($userid)
{
    $userinfo = get_userdata($userid);
    $email_body = SRP_FormatMessage('srp_weeklyemail', $tags);
    $email_body = str_replace("\n", '<br />', $email_body);

    SRP_SendEmail("Summer Reading Program:  You've won a prize!", $email_body, $userinfo->user_email);
}

function SRP_GetPrizeSettings()
{
    $options = get_option('SRPTheme');
    ksort($options);
    
    $optionkeys = array_keys($options);
    $prizes = array();
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $key = $optionkeys[$i];
        $pos = strpos($key, 'srp_hprize');
        if ($pos === false || $pos != 0)
        {
            continue;
        }
        
        $matches = array();
        preg_match('/srp_hprize([a-z]+)([0-9]+)/', $key, $matches);
        $type = $matches[1];
        $id = $matches[2];
        $value = $options[$key];
        $prizes[$id][$type] = $value;
    }
    
    return $prizes;
}

/*
 * SRP_AwardHourlyPrizesWithinBoundary
 * Awards prizes, if any, configured to be sent between $previousMinutes and $newMinutes
 */
function SRP_AwardHourlyPrizesWithinBoundary($userid, $previousMinutes, $newMinutes)
{
    $previousHours = $previousMinutes / 60.0;
    $newHours = $newMinutes / 60.0;
    
    $prizes = SRP_GetPrizeSettings();
    
    $hours2ids = array();
    foreach ($prizes as $id => $values)
    {
        $key = $values['hours'] - 0;
        $hours2ids[$key] = $id;
    }
    
    ksort($hours2ids);
    $prizesWon = array();
    foreach ($hours2ids as $hour => $id)
    {
        if ($previousHours < $hour && $hour <= $newHours)
        {
            SRP_AwardUserHourBasedPrize($userid, $id);
        }
    }
}

/*
 * SRP_AwardHourlyPrizesWithinBoundary
 * Sets the usermeta information and sends the email awarding the user of winning the specified prize.
 */
function SRP_AwardUserHourBasedPrize($userid, $prizeid)
{
    if (get_usermeta($userid, 'srp_noemail') != '1')
    {
        $userinfo = get_userdata($userid);
        
        $tags = array('prizename' => '<strong>' . SRP_HoursToPrizeName($prizeid) . '</strong>',
                      'prizecode' => '<strong>' . SRP_HoursToPrizeCode($prizeid) . '</strong>');
                      
        $email_body = SRP_FormatMessage('srp_hourlyemail', $tags);
        $email_body = str_replace("\n", '<br />', $email_body);
        
        SRP_SendEmail("Summer Reading Program:  You've won a prize!", $email_body, $userinfo->user_email);
    }

    $prizes  = get_usermeta($userid, 'srp_prizeswon');
    $prizes .= "$prizeid;";
    update_user_meta($userid, 'srp_prizeswon', $prizes);
}

function SRP_SetGrandPrizeEntriesWithinBoundary($userid, $previousMinutes, $newMinutes)
{
    $srp_gprize_every = get_srptheme_option('srp_gprize_every');
    $every_minutes = $srp_gprize_every * 60;
    
    $srp_gprize_numentries = get_srptheme_option('srp_gprize_numentries');
    for ($i = 0; $i < $srp_gprize_numentries; $i++)
    {
        $target = $every_minutes * ($i + 1);
        if ($previousMinutes < $target && $target <= $newMinutes)
        {
            SRP_SetUserPrizeMilestone($userid, $i);
        }
    }
}

/**
 * SRP_GetGrandPrizeName
 * Returns the name of the grand prize that the specified user registered with
 */
function SRP_GetGrandPrizeName($userid)
{
    $prizeid = get_usermeta($userid, 'srp_grandprize');
    return get_srptheme_option("srp_gprize$prizeid");
}

function SRP_GetGrandPrizesForGrade($grade)
{
    if ($grade < 6 || $grade > 12)
    {
        return array();
    }

    $options = get_option('SRPTheme');
    ksort($options);

    $optionkeys = array_keys($options);
    $gprizes = array();
    $badids = array();
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $key = $optionkeys[$i];
        $pos = strpos($key, 'srp_gprize');
        if ($pos === false || $pos != 0)
        {
            continue;
        }

        // There are a few keys that start with srp_gprize that we don't care about here;
        // they have underscores in their name.  Skip past those keys.
        if (strpos($key, '_', strlen('srp_gprize')) !== false)
        {
            continue;
        }

        $grandPrizeId = substr($key, strlen('srp_gprize'));
        if (!is_numeric($grandPrizeId))
        {
            $grandPrizeId = substr($grandPrizeId, 0, strpos($grandPrizeId, 'grade'));
            
            if ($options[$key] != '')
            {
                $bIsOK = false;
                foreach (explode(',', $options[$key]) as $val)
                {
                    if (($grade - 6) == $val)
                    {
                        // This prize is OK for the desired grade
                        $bIsOK = true;
                    }
                }

                if ($bIsOK === false)
                {
                    $badids[$grandPrizeId] = '1';
                }
            }
        }
        else
        {
            $gprizes[$grandPrizeId]['name'] = $options[$key];
        }
    }

    // Now that all the grand prizes have been collected, remove those that are not applicable to this grade
    return array_diff_key($gprizes, $badids);
}

?>
