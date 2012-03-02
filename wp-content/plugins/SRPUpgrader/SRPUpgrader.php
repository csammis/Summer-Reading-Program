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


    $upgrade_year = intval($from_version);
    switch ($upgrade_year)
    {
    case 2010:
        {
            die('how did i get here?');

            // The 2010 version was a special case of "not-writing-for-upgradability"
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

            // (4) Denormalize post author information
            $query  = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
            $query .= " SELECT p.ID, 'author_info', CONCAT(umn.meta_value, ' (grade ', umg.meta_value, ')') ";
            $query .= " FROM $wpdb->posts p ";
            $query .= " INNER JOIN $wpdb->usermeta umn ON p.post_author = umn.user_id AND umn.meta_key = %s ";
            $query .= " INNER JOIN $wpdb->usermeta umg ON p.post_author = umg.user_id AND umg.meta_key = %s ";
            $wpdb->query($wpdb->prepare($query, 'first_name', 'school_grade'));
        }
        // Intentional fallthrough
    case 2011:
        {
            // 2011 was the last year SRP stored data in the wp_options table

            $srpopt = get_option('SRPTheme');
            ksort($srpopt);

            //// Messages - fixed displays whose contents are configurable
            $messages_tablename = $wpdb->prefix . 'srp_messages';
            $table_exists = strlen($wpdb->get_var("SHOW TABLES LIKE '$messages_tablename'")) > 0;
            if ($table_exists === false)
            {
                $create_messages = "CREATE TABLE $messages_tablename (
                                            id mediumint(9) not null auto_increment,
                                            message_name tinytext default '' not null,
                                            message_value text default '' not null,
                                            unique key id (id));";
                dbDelta($create_messages);
                $insert_message = "INSERT INTO $messages_tablename (message_name, message_value) VALUES (%s, %s)";
                $messages2011 = array('srp_footertext', 'srp_hourlyemail', 'srp_hourlynotice', 'srp_weeklyemail', 'srp_regagreement');
                foreach ($messages2011 as $msgkey)
                {
                    $wpdb->query($wpdb->prepare($insert_message, $msgkey, $srpopt[$msgkey]));
                }
            }

            //// Genres - The configurable list of genres available to choose for books
            $genres_tablename = $wpdb->prefix . 'srp_genres';
            $table_exists = strlen($wpdb->get_var("SHOW TABLES LIKE '$genres_tablename'")) > 0;
            if ($table_exists === false)
            {
                $create_genres = "CREATE TABLE $genres_tablename (
                                            id mediumint(9) not null auto_increment,
                                            genre_name tinytext default '' not null,
                                            unique key id (id));";
                dbDelta($create_genres);
                $insert_genre = "INSERT INTO $genres_tablename (genre_name) VALUES (%s)";
                foreach ($srpopt as $key => $val)
                {
                    $pos = strpos($key, 'srp_genre');
                    if ($pos === false || $pos != 0)
                    {
                        continue;
                    }

                    $wpdb->query($wpdb->prepare($insert_genre, $val));
                }
            }

            //// Schools and school groups - The configurable list of OPL feeder schools and their groupings
            $schools_tablename = $wpdb->prefix . 'srp_school';
            $table_exists = strlen($wpdb->get_var("SHOW TABLES LIKE '$schools_tablename'")) > 0;
            if ($table_exists === false)
            {
                $groups_tablename = $wpdb->prefix . 'srp_schoolgroup';
                $create_groups = "CREATE TABLE $groups_tablename (
                                            id mediumint(9) not null auto_increment,
                                            group_name tinytext default '' not null,
                                            group_order mediumint(9) default 1 not null,
                                            primary key (id));";
                dbDelta($create_groups);
                
                $create_schools = "CREATE TABLE $schools_tablename (
                                            id mediumint(9) not null auto_increment,
                                            school_name tinytext default '' not null,
                                            semester mediumint(9) default 1 not null,
                                            primary key (id));";
                dbDelta($create_schools);


                $ssgrel_tablename = $wpdb->prefix . 'srp_schoolgroup_school_rel';
                $create_ssgrel = "CREATE TABLE $ssgrel_tablename (
                                            group_id mediumint(9) not null,
                                            school_id mediumint(9) not null,
                                            seq_num mediumint(9) not null,
                                            primary key (group_id, school_id));";
                dbDelta($create_ssgrel);

                // This is pretty awful and is basically the reason I decided to upgrade to tables...

                // Create an associative array of groups (id -> name) and school data (groupid -> schoolid -> name + semester)
                foreach ($srpopt as $key => $val)
                {
                    $pos = strpos($key, 'srp_school');
                    if ($pos === false)
                    {
                        $pos = strpos($key, 'srp_group');
                        if ($pos === false)
                        {
                            continue;
                        }
                    }

                    if (strpos($key, 'school') === false)
                    {
                        // This is a group name
                        $groupid = substr($key, strlen('srp_group'));
                        $groupname = $val;
                        $gid = $groupid + 0;
                        $groups[$gid] = $groupname;
                    }
                    else
                    {
                        // This is school data
                        $matches = array();
                        preg_match('/srp_school([0-9]+)group([0-9]+)/', $key, $matches);
                        $gid = $matches[2] + 0;
                        $sid = $matches[1] + 0;
                        if (strpos($key, 'show') === false)
                        {
                            $schools[$gid][$sid]['name'] = $val;
                        }
                        else
                        {
                            $schools[$gid][$sid]['show'] = $val;
                        }
                    }
                }

                // Insert that mess into the new tables
                $groups_insert  = "INSERT INTO $groups_tablename (id, group_name, group_order) VALUES (%s, %s, %s)";
                $schools_insert = "INSERT INTO $schools_tablename (id, school_name, semester) VALUES (%s, %s, %s)";
                $ssgrel_insert   = "INSERT INTO $ssgrel_tablename (group_id, schooL_id, seq_num) VALUES (%s, %s, %s)";

                ksort($groups);
                foreach ($groups as $oldgid => $gname)
                {
                    // Insert the group (2011 group order was determined by ID. Yeah.)
                    $wpdb->query($wpdb->prepare($groups_insert, $oldgid, $gname, $oldgid));

                    $seq_num = 1;
                    ksort($schools[$oldgid]);
                    foreach ($schools[$oldgid] as $oldsid => $school_data)
                    {
                        // Mangle up the semester a bit so that zeros aren't involved and "Both" (1) is the default
                        $semester = $school_data['show'];
                        switch ($semester)
                        {
                            case 0: $semester = 2; break; // spring
                            case 1: $semester = 3; break; // fall
                            case 2: $semester = 1; break; // both
                        }

                        // Insert the school
                        $wpdb->query($wpdb->prepare($schools_insert, $oldsid, $school_data['name'], $semester));

                        // Insert the rel row
                        $wpdb->query($wpdb->prepare($ssgrel_insert, $oldgid, $oldsid, $seq_num));

                        $seq_num++;
                    }
                }
            } // end schools/groups
            
            $prizes_tablename = $wpdb->prefix . 'srp_prizes';
            $table_exists = strlen($wpdb->get_var("SHOW TABLES LIKE '$prizes_tablename'")) > 0;
            if ($table_exists === false)
            {
                $prizes_create = "CREATE TABLE $prizes_tablename (
                                            id mediumint(9) not null auto_increment,
                                            prize_name tinytext default '' not null,
                                            hour_threshold mediumint(9) not null default 0,
                                            prize_code tinytext default '' not null,
                                            primary key (id));";
                dbDelta($prizes_create);

                $gprize_tablename = $wpdb->prefix . 'srp_gprizes';
                $gprize_create = "CREATE TABLE $gprize_tablename (
                                            id mediumint(9) not null auto_increment,
                                            prize_name tinytext default '' not null,
                                            primary key (id));";
                dbDelta($gprize_create);

                $gprize_grade_tablename = $wpdb->prefix . 'srp_gprize_grade';
                $gprize_grade_create = "CREATE TABLE $gprize_grade_tablename (
                                                  gprize_id mediumint(9) not null default 0,
                                                  grade mediumint(9) not null default 0,
                                                  primary key (gprize_id, grade));";
                dbDelta($gprize_grade_create);

                foreach ($srpopt as $key => $val)
                {
                    $pos = strpos($key, 'srp_hprize');
                    if ($pos === false || $pos != 0)
                    {
                        $pos = strpos($key, 'srp_gprize');
                        if ($pos === false || $pos != 0)
                        {
                            continue;
                        }

                        if (strpos($key, '_', strlen('srp_gprize')) !== false)
                        {
                            continue;
                        }

                        $grandPrizeId = substr($key, strlen('srp_gprize'));
                        if (!is_numeric($grandPrizeId))
                        {
                            $grandPrizeId = substr($grandPrizeId, 0, strpos($grandPrizeId, 'grade'));
                            $gprizes[$grandPrizeId]['grades'] = $val;
                        }
                        else
                        {
                            $gprizes[$grandPrizeId]['name'] = $val;
                        }
                    }
                    else
                    {
                        $matches = array();
                        preg_match('/srp_hprize([a-z]+)([0-9]+)/', $key, $matches);
                        $type = $matches[1];
                        $id = $matches[2];
                        $prizes[$id][$type] = $val;
                    }
                }

                $prizes_insert = "INSERT INTO $prizes_tablename (prize_name, hour_threshold, prize_code) VALUES (%s, %s, %s)";
                $gprize_insert = "INSERT INTO $gprize_tablename (id, prize_name) VALUES (%s, %s)";
                $gprize_grade_insert = "INSERT INTO $gprize_grade_tablename (gprize_id, grade) VALUES (%s, %s)";
                
                foreach ($prizes as $id => $prize)
                {
                    $wpdb->query($wpdb->prepare($prizes_insert, $prize['name'], $prize['hours'], $prize['code']));
                }

                $i = 1;
                foreach ($gprizes as $id => $gprize)
                {
                    $wpdb->query($wpdb->prepare($gprize_insert, $i, $gprize['name']));
                    $grades = $gprize['grades'];
                    foreach (explode(',', $grades) as $grade)
                    {
                        $grade += 6;
                        $wpdb->query($wpdb->prepare($gprize_grade_insert, $i, $grade));
                    }
                    $i++;
                }
            }

            $themeopt_tablename = $wpdb->prefix . 'srp_themeopt';
            $table_exists = strlen($wpdb->get_var("SHOW TABLES LIKE '$themeopt_tablename'")) > 0;
            if ($table_exists === false)
            {
                $themeopt_create = "CREATE TABLE $themeopt_tablename (
                                              id mediumint(9) not null auto_increment,
                                              option_name tinytext not null default '',
                                              option_value tinytext not null default '',
                                              primary key (id));";
                dbDelta($themeopt_create);

                $themeopt_insert = "INSERT INTO $themeopt_tablename (option_name, option_value) VALUES (%s, %s)";

                // From the General page
                $wpdb->query($wpdb->prepare($themeopt_insert, 'library_name', $srpopt['library_name']));
                $wpdb->query($wpdb->prepare($themeopt_insert, 'google_analytics_id', $srpopt['ga_id']));
                $wpdb->query($wpdb->prepare($themeopt_insert, 'program_active', $srpopt['program_active']));
                $wpdb->query($wpdb->prepare($themeopt_insert, 'review_max_length', $srpopt['max_length']));

                $color1 = isset($srpopt['srp_backcolor1']) ? $srpopt['srp_backcolor1'] : '1E6088';
                $color2 = isset($srpopt['srp_backcolor2']) ? $srpopt['srp_backcolor2'] : '3D2D1E';
                $color3 = isset($srpopt['srp_backcolor3']) ? $srpopt['srp_backcolor3'] : 'B5D1E6';
                $wpdb->query($wpdb->prepare($themeopt_insert, 'color_header_footer', $color1));
                $wpdb->query($wpdb->prepare($themeopt_insert, 'color_sides', $color2));
                $wpdb->query($wpdb->prepare($themeopt_insert, 'color_body', $color3));
                
                $wpdb->query($wpdb->prepare($themeopt_insert, 'header_img_url', $srpopt['srp_headerimg']));
                $wpdb->query($wpdb->prepare($themeopt_insert, 'footer_img_url', $srpopt['srp_footerimg']));


                // Grand Prize page
                $gprize_every = isset($srpopt['srp_gprize_every']) ? $srpopt['srp_gprize_every'] : 16;
                $wpdb->query($wpdb->prepare($themeopt_insert, 'grand_prize_interval', $gprize_every));
                $gprize_max = isset($srpopt['srp_gprize_numentries']) ? $srpopt['srp_gprize_numentries'] : 3;
                $wpdb->query($wpdb->prepare($themeopt_insert, 'grand_prize_max_entries', $gprize_max));

                // Email page
                $wpdb->query($wpdb->prepare($themeopt_insert, 'gmail_account_name', $srpopt['gmail_account']));
                $wpdb->query($wpdb->prepare($themeopt_insert, 'gmail_account_pass', $srpopt['gmail_password']));
                $wpdb->query($wpdb->prepare($themeopt_insert, 'gmail_send_email_as', $srpopt['gmail_reply_to']));
                
            }

            //TODO: User options, post information

        }
        // Intentional fallthrough
    default:
        {
            // nothing here
        }
    }
    
    delete_option('SRP_LastDrawing');

    /********** Upgrade functionality common to all versions **********/

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
