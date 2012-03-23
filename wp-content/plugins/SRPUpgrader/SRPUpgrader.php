<?php
/*
Plugin Name: SRP Upgrader
Description:  Activate this plugin to convert an SRP installation to the latest version.
*/

register_activation_hook (WP_PLUGIN_DIR . '/SRPUpgrader/SRPUpgrader.php', 'srp_upgrader_activated');

global $SRP_VERSION;
$SRP_VERSION = '2012';

function srp_upgrader_activated()
{
    global $SRP_VERSION;
    global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $version_table_name = $wpdb->prefix . 'srp_version';

    $from_version = '2010'; // This was the first version of SRP and should be the default if the table doesn't exist.

    if ($wpdb->get_var("SHOW TABLES LIKE '$version_table_name'") == $version_table_name)
    {
        // Get the last update by upgrade_time and figure out what the version was. If it's $SRP_VERSION we're done.

        $select = "SELECT to_version FROM $version_table_name ORDER BY upgrade_time DESC LIMIT 1";
        $tmp_from_version = $wpdb->get_var($select);
        if (strlen($tmp_from_version) > 0)
        {
            $from_version = $tmp_from_version;
        }

        if ($from_version == $SRP_VERSION)
        {
            return;
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

    // The 2010 version was a special case of "not-writing-for-upgradability"
    if ($from_version == '2010')
    {
        // (1) Wipe out the srp_options and old theme settings in the opt table
        delete_option('SRPTheme');
        delete_option('widget_srpreviewwidget');
        delete_option('sidebars_widgets');

        // (2) Import new genres
        update_option('SRPTheme', array(
            'srp_genre1'  => 'Adventure',   //adventure
            'srp_genre2'  => 'Fantasy',     //fantasy
            'srp_genre3'  => 'Fiction',     //fiction
            'srp_genre4'  => 'Historical',  //historical
            'srp_genre5'  => 'Horror',      //horror
            'srp_genre6'  => 'Mystery',     //mystery
            'srp_genre7'  => 'Non-fiction', //nonfiction
            'srp_genre8'  => 'Romance',     //romance
            'srp_genre9'  => 'Science Fiction', //scifi
            'srp_genre10' => 'Thriller',    //thriller
            'nextgenreid' => 11));


        // (3) Map genres from 2010 -> new versions on the postmeta table
        $genreUpdate = "UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_key = %s AND meta_value = %s";
        $wpdb->query($wpdb->prepare($genreUpdate, '1', 'book_genre', 'adventure'));
        $wpdb->query($wpdb->prepare($genreUpdate, '2', 'book_genre', 'fantasy'));
        $wpdb->query($wpdb->prepare($genreUpdate, '3', 'book_genre', 'fiction'));
        $wpdb->query($wpdb->prepare($genreUpdate, '4', 'book_genre', 'historical'));
        $wpdb->query($wpdb->prepare($genreUpdate, '5', 'book_genre', 'horror'));
        $wpdb->query($wpdb->prepare($genreUpdate, '6', 'book_genre', 'mystery'));
        $wpdb->query($wpdb->prepare($genreUpdate, '7', 'book_genre', 'nonfiction'));
        $wpdb->query($wpdb->prepare($genreUpdate, '8', 'book_genre', 'romance'));
        $wpdb->query($wpdb->prepare($genreUpdate, '9', 'book_genre', 'scifi'));
        $wpdb->query($wpdb->prepare($genreUpdate, '10', 'book_genre', 'thriller'));
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
    $retval = $wpdb->query($wpdb->prepare($delete_usermeta));
    if ($retval === FALSE)
    {
        echo 'Error executing DELETE FROM usermeta';
        $wpdb->print_error();
        echo "\n";
        return;
    }

    // Remove users
    $delete_users = "DELETE FROM $wpdb->users WHERE id NOT IN ($admin_userids)";
    $retval = $wpdb->query($wpdb->prepare($delete_users));
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

}

?>
