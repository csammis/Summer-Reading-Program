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

/*
 * SRP_PublishPosts
 * Sets the specified posts to 'publish' status.
 */
function SRP_PublishPosts($ids)
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
function SRP_ResetDatabase()
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

    $delete_postmeta  = "DELETE FROM $wpdb->postmeta ";
    $delete_postmeta .= "WHERE POST_ID IN (SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = %s)";
    
    $retval = $wpdb->query($wpdb->prepare($delete_postmeta, 'post', 'publish'));
    if ($retval === FALSE)
    {
        echo 'Error executing DELETE FROM postmeta';
        $wpdb->print_error();
        echo "\n";
        return;
    }

    $delete_posts = "DELETE FROM $wpdb->posts WHERE post_type = %s AND post_status = %s";
    $retval = $wpdb->query($wpdb->prepare($delete_posts, 'post', 'publish'));
    if ($retval === FALSE)
    {
        echo 'Error executing DELETE FROM posts';
        $wpdb->print_error();
        echo "\n";
        return;
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

?>
