<?php
/*
* srp-inc-admin.php
* Administrative functionality - review approval, database reset, etc.

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

//  SRP_PublishPosts
//  SRP_DeletePosts
//  SRP_ResetDatabase
//  SRP_StartNewYear

/*
 * SRP_PublishPosts
 * Sets the specified posts to 'publish' status.
 */
function SRP_PublishPosts($ids, $comments)
{
    if (count($ids) == 0)
    {
        return;
    }
    
    global $wpdb;
    
    $update = "UPDATE $wpdb->posts SET post_status = 'publish' WHERE ID IN (";
    for ($i = 0; $i < count($ids); $i++)
    {
        if ($i > 0)
        {
            $update .= ',';
        }
        $update .= "%s";
    }
    $update .= ')';
    
    $wpdb->query($wpdb->prepare($update, $ids));

    foreach ($comments as $id => $comment)
    {
        if (isset($comment) && strlen(trim($comment)) > 0)
        {
            $comment_data = array(
                'comment_post_ID' => $id,
                'comment_content' => $comment,
                'comment_author' => 'admin',
                'comment_date_gmt' => gmdate('Y-m-d H:i:s'),
                'comment_approved' => 1
            );

            if (wp_insert_comment($comment_data) <= 0)
            {
                die("Failed to insert new comment on post ID $id, try again later.");
            }
        }
    }
}

/*
 * SRP_DeletePosts
 * Deletes the specified posts and metainformation.
 */
function SRP_DeletePosts($ids)
{
    if (count($ids) == 0)
    {
        return;
    }
    
    global $wpdb;
    
    $delete_meta = "DELETE FROM $wpdb->postmeta WHERE POST_ID IN (";
    for ($i = 0; $i < count($ids); $i++)
    {
        if ($i > 0)
        {
            $delete_meta .= ',';
        }
        $delete_meta .= "%s";
    }
    $delete_meta = ')';
    
    $wpdb->query($wpdb->prepare($delete_meta, $ids));
    
    $delete = "DELETE FROM $wpdb->posts WHERE ID IN (";
    for ($i = 0; $i < count($ids); $i++)
    {
        if ($i > 0)
        {
            $delete .= ',';
        }
        $delete .= "%s";
    }
    $delete .= ')';
    
    $wpdb->query($wpdb->prepare($delete, $ids));
}

/*
 * SRP_ResetDatabase
 * Deletes published posts, non-admin users, and usermeta and postmeta for both.
 */
function SRP_ResetDatabase($removeReviews = true)
{
    global $wpdb;

    // Thanks to MySQL not being able to delete from a table referenced in a subselect,
    // administrator user IDs are gathered from the usermeta table here and used in NOT IN lists below.
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

    if ($removeReviews === true)
    {
        $delete_postmeta  = "DELETE FROM $wpdb->postmeta ";
        $delete_postmeta .= "WHERE POST_ID IN (SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = %s AND post_author NOT IN ($admin_userids))";
    
        $retval = $wpdb->query($wpdb->prepare($delete_postmeta, 'post', 'publish', $admin_userids));
        if ($retval === FALSE)
        {
            echo 'Error executing DELETE FROM postmeta';
            $wpdb->print_error();
            echo "\n";
            return;
        }

        $delete_posts = "DELETE FROM $wpdb->posts WHERE post_type = %s AND post_status = %s AND post_author NOT IN ($admin_userids)";
        $retval = $wpdb->query($wpdb->prepare($delete_posts, 'post', 'publish', $admin_userids));
        if ($retval === FALSE)
        {
            echo 'Error executing DELETE FROM posts';
            $wpdb->print_error();
            echo "\n";
            return;
        }
    }

    $delete_usermeta = "DELETE FROM $wpdb->usermeta WHERE user_id NOT IN ($admin_userids)";
    $retval = $wpdb->query($wpdb->prepare($delete_usermeta));
    if ($retval === FALSE)
    {
        echo 'Error executing DELETE FROM usermeta';
        $wpdb->print_error();
        echo "\n";
        return;
    }

    $delete_users = "DELETE FROM $wpdb->users WHERE id NOT IN ($admin_userids)";
    $retval = $wpdb->query($wpdb->prepare($delete_users));
    if ($retval === FALSE)
    {
        echo 'Error executing DELETE FROM users';
        $wpdb->print_error();
        echo "\n";
        return;
    }
}

function SRP_StartNewYear($year)
{
    global $wpdb;

    require_once(ABSPATH . "wp-admin/includes/upgrade.php");

    if (!isset($year) || strlen($year) == 0)
    {
        return false;
    }

    if (strlen($year) == 2)
    {
        $year = "20$year";
    }

    // Step 1: check for the existance of the version table
    $version_table_name = $wpdb->prefix . "srp_version";

    // This was the first version of SRP and should be the default if the table doesn't exist.
    $from_version = '2010';

    if ($wpdb->get_var("SHOW TABLES LIKE '$version_table_name'") == $version_table_name)
    {
        // Get the last update by upgrade_time and figure out what the version was. If it's $year we're done.

        $select = "SELECT to_version FROM $version_table_name ORDER BY upgrade_time DESC LIMIT 1";
        $tmp_from_version = $wpdb->get_var($select);
        if (strlen($tmp_from_version) > 0)
        {
            $from_version = $tmp_from_version;
        }

        if ($from_version == $year)
        {
            return true;
        }
    }
    else
    {
        // Create the srp_version table
        $create_table = "CREATE TABLE $version_table_name (
                            id mediumint(9) NOT NULL AUTO_INCREMENT,
                            prev_version tinytext DEFAULT '' NOT NULL,
                            to_version tinytext DEFAULT '' NOT NULL,
                            upgrade_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                            UNIQUE KEY id (id)
                     );";
        dbDelta($create_table);
    }

    delete_option('SRP_LastDrawing');

    // Create an upgrade user
    $upgrade_userid = username_exists('SRP_Upgrade_User');
    if (!$upgrade_userid)
    {
        $random_password = wp_generate_password(12, false);
        $upgrade_userid = wp_create_user('SRP_Upgrade_User', $random_password, 'donotdelete2@thisuser.com');
        $wpdb->query($wpdb->prepare("UPDATE $wpdb->usermeta SET meta_value = %s WHERE user_id = %s AND meta_key = %s",
            'a:1:{s:13:"administrator";b:1;}', $upgrade_userid, $wpdb->prefix . 'capabilities'));
    }

    // All upgrades MUST remove the previous version's non-admin users. This is How It Is.
    // ---------- //

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

    // Delete all prior year posts by the upgrade user
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id IN (SELECT ID FROM $wpdb->posts WHERE post_author = %s)", $upgrade_userid));
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->posts WHERE post_author = %s", $upgrade_userid));

    // Save the previous year's posts by denormalizing post author information and attributing to the upgrade user
    $query  = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
    $query .= " SELECT p.ID, 'author_info', CONCAT(umn.meta_value, ' (grade ', umg.meta_value, ')') ";
    $query .= " FROM $wpdb->posts p ";
    $query .= " INNER JOIN $wpdb->usermeta umn ON p.post_author = umn.user_id AND umn.meta_key = %s ";
    $query .= " INNER JOIN $wpdb->usermeta umg ON p.post_author = umg.user_id AND umg.meta_key = %s ";
    $wpdb->query($wpdb->prepare($query, 'first_name', 'school_grade'));

    // Attribute all posts to the upgrade user
    $wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_author = %s WHERE post_type = %s AND post_status = %s",
        $upgrade_userid, 'post', 'publish'));

    // Remove usermeta
    $delete_usermeta = "DELETE FROM $wpdb->usermeta WHERE user_id NOT IN ($admin_userids)";
    $retval = $wpdb->query($delete_usermeta);
    if ($retval === FALSE)
    {
        echo 'Error executing DELETE FROM usermeta';
        $wpdb->print_error();
        echo "\n";
        return;
    }

    // Remove users
    $delete_users = "DELETE FROM $wpdb->users WHERE id NOT IN ($admin_userids)";
    $retval = $wpdb->query($delete_users);
    if ($retval === FALSE)
    {
        echo 'Error executing DELETE FROM users';
        $wpdb->print_error();
        echo "\n";
        return;
    }

    // ---------- //

    // Insert a row indicating the upgrade happened
    $wpdb->insert($version_table_name,
        array('upgrade_time' => current_time('mysql'), 'prev_version' => $from_version, 'to_version' => $SRP_VERSION));

    return true;
}

?>
