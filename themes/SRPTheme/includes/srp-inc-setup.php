<?php
/*
* srp-inc-setup.php
* Run-once methods to set up additional tables in the WP space that support
* the SRP installation.  ALL METHODS MUST BE NON-DESTRUCTIVE IF RUN TWICE.

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

//  SRP_OneClickSetup
//  SRP_CreatePages
//  SRP_CreateSinglePage

function SRP_OneClickSetup()
{
    global $wpdb;
    
    $update_options = "UPDATE $wpdb->options SET OPTION_VALUE = %s WHERE OPTION_NAME = %s";
    
    // Set the permalink structure
    $wpdb->query($wpdb->prepare($update_options, '/%postname%/', 'permalink_structure'));
    
    // Insert all the base pages
    SRP_CreatePages();
    
    // Set frontpage.php to the Front Page Display
    $pageid = $wpdb->get_var($wpdb->prepare("SELECT POST_ID FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s",
                                            '_wp_page_template', 'frontpage.php'));
    $wpdb->query($wpdb->prepare($update_options, 'page', 'show_on_front'));
    $wpdb->query($wpdb->prepare($update_options, $pageid, 'page_on_front'));
}

/*
 * SRP_CreatePages
 * The best function.
 */
function SRP_CreatePages()
{
    SRP_CreateSinglePage('Comment Poster', 'postcomment.php', '3');
    SRP_CreateSinglePage('Contact', 'contact.php', '0,1');
    SRP_CreateSinglePage('Home', 'frontpage.php', '0,1,2');
    SRP_CreateSinglePage('How It Works', 'howitworks.php', '0,1');
    SRP_CreateSinglePage('Pending Reviews', 'approvals.php', '2');
    SRP_CreateSinglePage('Prize Drawing', 'prizedrawing.php', '2');
    SRP_CreateSinglePage('Register', 'user_reg.php', '0');
    SRP_CreateSinglePage('Reset user time', 'hoursreset.php', '3');
    SRP_CreateSinglePage('Retrive Password', 'retrievepassword.php', '3');
    SRP_CreateSinglePage('Reviews', 'reviewpage.php', '0,1,2');
    SRP_CreateSinglePage('Statistics', 'statistics.php', '2');
    SRP_CreateSinglePage('Submit Time', 'submitbooks.php', '1');
    SRP_CreateSinglePage('Submit Review', 'submitreview.php', '1');
    SRP_CreateSinglePage('Update Profile', 'editprofile.php', '3');
}

/*
 * SRP_CreateSinglePage
 * Creates a single WP page with the given title, template, and page access value.
 */
function SRP_CreateSinglePage($title, $template, $page_access)
{
    // Require SRP_SelectUrlOfTemplatedPage
    require_once('srp-inc-utility.php');
    
    if (SRP_SelectUrlOfTemplatedPage($template) === FALSE)
    {
        $pagedata = array('page_template' => $template,
                        'post_status' => 'publish',
                        'post_type' => 'page',
                        'post_title' => $title,
                        'post_content' => "$title content here.");
        $pageid = wp_insert_post($pagedata);
        if ($pageid == 0)
        {
            die("Error inserting $title page.");
        }
        
        // Insert the page_access metadata
        if (strlen($page_access) > 0)
        {
            update_post_meta($pageid, 'page_access', $page_access);
        }
    }
}

?>
