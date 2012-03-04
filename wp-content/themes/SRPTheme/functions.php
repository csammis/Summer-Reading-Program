<?php
/*
* functions.php
* This file contains common registration and theme preference functions

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

$arclite_theme_data = get_theme_data(TEMPLATEPATH.'/style.css');
define('THEME_VERSION', trim($arclite_theme_data['Version']) );

if(!defined("PHP_EOL")) define("PHP_EOL", strtoupper(substr(PHP_OS,0,3) == "WIN") ? "\r\n" : "\n");

function setup_options()
{
    //remove_options();

    /*update_option( 'SRPTheme' , apply_filters('theme_default_settings', array(
                    'theme_version' => THEME_VERSION,
                    'gmail_reply_to' => $srp_gmail_reply_to,
                    'gmail_account' => $srp_gmail_account,
                    'gmail_password' => $srp_gmail_password,
                    'grand_prizes' => $srp_grand_prizes,
                    'program_active' => $srp_program_active
                ))
    );*/
}

function remove_options()
{
    delete_option('SRPTheme');
}

function get_srptheme_option($option)
{
    $get_srptheme_options = get_option('SRPTheme');
    return esc_attr(stripslashes($get_srptheme_options[$option]));
}

function is_srptheme_option_set($option)
{
    $get_srptheme_options = get_option('SRPTheme');
    return isset($get_srptheme_options[$option]);
}

function print_srptheme_option($option)
{
    $get_srptheme_options = get_option('SRPTheme');
    echo esc_attr(stripslashes($get_srptheme_options[$option]));
}

function get_srptheme_message($message)
{
    $retval = '';
    
    if (is_srptheme_option_set($message))
    {
        $retval = get_srptheme_option($message);
        if ($message == 'srp_footertext')
        {
            $retval = preg_replace('/&quot;/', '"', $retval);
            $retval = preg_replace('/&gt;/', '>', $retval);
            $retval = preg_replace('/&lt;/', '<', $retval);
            $retval = preg_replace('/<iframe[*.?]>/', '', $retval);
            $retval = preg_replace('/<script[*.?]>/', '', $retval);
        }
    }
    else
    {
        if ($message == 'srp_hourlyemail')
        {
            $retval  = "Good work!\n\nYou've read enough pages or minutes to earn a %%prizename%%. ";
            $retval .= "Please come pick up your prize at %%libraryname%% by August 1. Remember to bring your verification code: %%prizecode%%\n\n";
            $retval .= "-- %%libraryname%%\n\n";
            $retval .= "Note: Prizes may be substituted in the event that the supply runs out.";
        }
        else if ($message == 'srp_hourlynotice')
        {
            $retval  = 'If you have not picked up your prizes, please come to the library and collect ';
            $retval .= 'them by August 1.  Remember to bring the verification code listed next to each prize.';
        }
        else if ($message == 'srp_weeklyemail')
        {
            $retval  = "We've picked your recent review as a winning entry in our weekly drawing!\n\n";
            $retval .= "Please come pick up your prize at %%libraryname%% by August 1. Remember to bring your verification code: 985.\n\n";
            $retval .= "-- %%libraryname%%";
        }
        else if ($message == 'srp_regagreement')
        {
            $retval = 'I am a resident of Johnson County, Kansas, and am able to pick up any prizes I win at %%libraryname%%.';
        }
        else if ($message == 'srp_footertext')
        {
            $retval  = "<p>\n";
            $retval .= '<a href="http://www.library.org/">Library Main Page</a>&nbsp;|&nbsp;' . "\n";
            $retval .= '<a href="http://catalog.library.org">Library Catalog</a>&nbsp;|&nbsp;' . "\n";
            $retval .= '<a href="http://www.library.org/teens/">Library Teens\' Page</a><br /><br />' . "\n\n";
            
            $retval .= '<strong>Library Main</strong>: 201 E Park St., Everytown, KS 00000 (555.555.5555)<br />' . "\n";
            $retval .= '<strong>Library Branch</strong>: 12990 S South Rd., Everytown, KS 00000 (555.555.5556)<br /><br />' . "\n";
            $retval .= '</p>' . "\n";
            $retval .= '<p>&nbsp;</p>' . "\n";
            $retval .= '<p>All art and graphics are used by permission of their respective creators and may not be reproduced or copied in any way.</p>';
        }
        else
        {
            $retval = 'unknown message';
        }
    }
    
    if ($message != 'srp_footertext')
    {
        $retval = esc_attr($retval);
    }

    return $retval;
}

/*
 * SRP_StoreSimpleDynamicOptions
 * Stores dynamic options like genres, prizes, etc.
 */
function SRP_StoreSimpleDynamicOptions($options, $postarray, $prefkey, $nextidkey)
{
    // Remove all the matching keys and overwrite with the POST values. This takes care of values removed
    // by the user which are no longer in the POST array.
    $newoptions = array();
    $optionkeys = array_keys($options);
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $genrepos = strpos($optionkeys[$i], $prefkey);
        if ($genrepos === false || $genrepos != 0)
        {
            $newoptions[$optionkeys[$i]] = $options[$optionkeys[$i]];
        }
    }
    $options = $newoptions;
    
    $nextid = $postarray[$nextidkey];
    $options[$nextidkey] = $nextid;
    $postkeys = array_keys($postarray);
    for ($i = 0; $i < count($postkeys); $i++)
    {
        $genrepos = strpos($postkeys[$i], $prefkey);
        if ($genrepos !== false && $genrepos == 0)
        {
            $val = '';
            if (is_array($postarray[$postkeys[$i]]))
            {
                $val = implode(',', $postarray[$postkeys[$i]]);
            }
            else
            {
                $val = esc_attr(stripslashes($postarray[$postkeys[$i]]));
            }

            $options[$postkeys[$i]] = $val; // esc_attr(stripslashes($postarray[$postkeys[$i]]));
        }
    }
    
    return $options;
}

function srptheme_update_options()
{
	check_admin_referer('theme-settings');
	if (!current_user_can('edit_themes'))
    {
        wp_die('You are not authorised to perform this operation.');
    }

	$options = get_option('SRPTheme');
    
    $prefmode = $_POST['active_show'];
    if ($prefmode == 'email')
    {
        require_once('includes/srp-obj-email.php');

        $email = new SRPEmailSettings;
        if (!$email->dbSelect())
        {
            die('Could not retrieve email information from database (loc = 8F0NMJ)');
        }

        if (isset($_POST['gmail_reply_to'])) $email->setGoogleAccountSendAs($_POST['gmail_reply_to']);
        if (isset($_POST['gmail_account']))  $email->setGoogleAccountName($_POST['gmail_account']);
        if (isset($_POST['gmail_password'])) $email->setGoogleAccountPass($_POST['gmail_password']);

        if (!$email->dbUpdate())
        {
            die('Could not update email information in the database (loc = 8F0NOU)');
        }
    }
    else if ($prefmode == 'messages')
    {
        die('Not a good idea right now.');
        if (isset($_POST['srp_hourlyemail'])) $options['srp_hourlyemail'] = $_POST['srp_hourlyemail'];
        if (isset($_POST['srp_hourlynotice'])) $options['srp_hourlynotice'] = $_POST['srp_hourlynotice'];
        if (isset($_POST['srp_regagreement'])) $options['srp_regagreement'] = $_POST['srp_regagreement'];
        if (isset($_POST['srp_weeklyemail'])) $options['srp_weeklyemail'] = $_POST['srp_weeklyemail'];
        if (isset($_POST['srp_footertext'])) $options['srp_footertext'] = $_POST['srp_footertext'];
    }
    else if ($prefmode == 'prizes')
    {
        die('Not a good idea right now.');
        if (isset($_POST['srp_gprize_every'])) $options['srp_gprize_every'] = $_POST['srp_gprize_every'];
        if (isset($_POST['srp_gprize_numentries'])) $options['srp_gprize_numentries'] = $_POST['srp_gprize_numentries'];

        $options = SRP_StoreSimpleDynamicOptions($options, $_POST, 'srp_hprize', 'nexthprizeid');
        $options = SRP_StoreSimpleDynamicOptions($options, $_POST, 'srp_gprize', 'nextgprizeid');
    }
    else if ($prefmode == 'general')
    {
        require_once('includes/srp-obj-theme.php');

        $theme = new SRPThemeSettings;
        if (!$theme->dbSelect())
        {
            die('Could not retrieve theme information from database (loc = 8F0KKS)');
        }
        
        if (isset($_POST['ga_id']))          $theme->setGoogleAnalyticsID($_POST['ga_id']);
        if (isset($_POST['srp_headerimg']))  $theme->setHeaderImageUrl($_POST['srp_headerimg']);
        if (isset($_POST['srp_footerimg']))  $theme->setFooterImageUrl($_POST['srp_footerimg']);
        if (isset($_POST['srp_backcolor1'])) $theme->setHeaderFooterColor($_POST['srp_backcolor1']);
        if (isset($_POST['srp_backcolor2'])) $theme->setSideColor($_POST['srp_backcolor2']);
        if (isset($_POST['srp_backcolor3'])) $theme->setBodyColor($_POST['srp_backcolor3']);
        if (isset($_POST['max_length']))     $theme->setMaxReviewLength($_POST['max_length']);
        if (isset($_POST['library_name']))   $theme->setLibraryName($_POST['library_name']);

        if (!$theme->dbUpdate())
        {
            die('Could not update theme information into database (loc = 8F0L1J)');
        }

        // Handle the switches available from the general settings
        if (isset($_POST['program_active']))
        {
            $options['program_active'] = $_POST['program_active'];
            if ($_POST['program_active'] == 1)
            {
                $options['program_open_date'] = time();
            }
        }

        if (isset($_POST['srp_reset']))
        {
            require_once('includes/srp-inc-admin.php');
            SRP_ResetDatabase();
            unset($options['program_open_date']);
            delete_option('SRP_LastDrawing');
        }

        if (isset($_POST['srp_oneclicksetup']))
        {
            require_once('includes/srp-inc-setup.php');
            SRP_OneClickSetup();
        }
    }
    else if ($prefmode == 'schools')
    {
        die('Not a good idea right now.');
        // Remove all the srp_group and srp_school keys and overwrite with the POST values. This takes care of values removed
        // by the user which are no longer in the POST array.
        $newoptions = array();
        $optionkeys = array_keys($options);
        for ($i = 0; $i < count($optionkeys); $i++)
        {
            $schoolpos = strpos($optionkeys[$i], 'srp_school');
            $grouppos = strpos($optionkeys[$i], 'srp_group');
            if ( ($schoolpos === false || $schoolpos != 0) && ($grouppos === false || $grouppos != 0) )
            {
                $newoptions[$optionkeys[$i]] = $options[$optionkeys[$i]];
            }
        }
        $options = $newoptions;
        
        $nextgroupid = $_POST['nextgroupid'];
        $nextschoolid = $_POST['nextschoolid'];
        $options['nextgroupid'] = $nextgroupid;
        $options['nextschoolid'] = $nextschoolid;
        $postkeys = array_keys($_POST);
        for ($i = 0; $i < count($postkeys); $i++)
        {
            $schoolpos = strpos($postkeys[$i], 'srp_school');
            $grouppos = strpos($postkeys[$i], 'srp_group');
            if ( ($schoolpos !== false && $schoolpos == 0) || ($grouppos !== false && $grouppos == 0) )
            {
                $options[$postkeys[$i]] = esc_attr(stripslashes($_POST[$postkeys[$i]]));
            }
        }
    }
    else if ($prefmode == 'genres')
    {
        die('Not a good idea right now.');
        $options = SRP_StoreSimpleDynamicOptions($options, $_POST, 'srp_genre', 'nextgenreid');
    }

	update_option('SRPTheme', $options);
	wp_redirect(admin_url("themes.php?page=theme-settings&show=$prefmode&updated=true"));
}

function srptheme_theme_settings()
{
    if (!current_user_can('edit_themes'))
    {
        return;
    }
?>
<div id="theme-settings" class="wrap">
<?php screen_icon(); ?>
<h2><?php _e('SRP Theme settings', 'srptheme'); ?></h2>
<?php if (isset($_GET['updated'])): ?><div class="updated fade below-h2"><p>Settings saved.</p></div><?php endif; ?>
<?php
    $show = $_GET['show'];
    if (!isset($show))
    {
        $show = 'general';
    }
    
    $base_url = admin_url('themes.php?page=theme-settings');
?>
<!-- settings menu -->
<ul class="subsubsub"> 
    <li><a href="<?php echo "$base_url&show=general"; ?>" <?php if ($show == 'general') echo ' class="current"'; ?>>General</span></a> |</li>
    <li><a href="<?php echo "$base_url&show=messages"; ?>" <?php if ($show == 'messages') echo ' class="current"'; ?>>Messages</span></a> |</li>
    <li><a href="<?php echo "$base_url&show=email"; ?>" <?php if ($show == 'email') echo ' class="current"'; ?>>E-mail</span></a> |</li>
    <li><a href="<?php echo "$base_url&show=prizes"; ?>" <?php if ($show == 'prizes') echo ' class="current"'; ?>>Prizes</span></a> |</li>
    <li><a href="<?php echo "$base_url&show=schools"; ?>" <?php if ($show == 'schools') echo ' class="current"'; ?>>Schools</span></a> |</li>
    <li><a href="<?php echo "$base_url&show=genres"; ?>" <?php if ($show == 'genres') echo ' class="current"'; ?>>Genres</span></a></li>
</ul>
<div class="clear"></div>
<form action="<?php echo admin_url('admin-post.php?action=srptheme_update'); ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="active_show" value="<?php echo $show; ?>" />
<?php wp_nonce_field('theme-settings'); ?>
<div id="theme-settings">

<!---- General preferences ---->
<?php if ($show == 'general'): ?>
<?php require_once('includes/functions-general.php'); SRP_PrintGeneralOptions(); ?>
<?php endif; ?>

<!---- Message preferences ---->
<?php if ($show == 'messages'): ?>
<?php require_once('includes/functions-messages.php'); SRP_PrintMessagesOptions(); ?>
<?php endif; ?>

<!---- Email preferences ---->
<?php if ($show == 'email'): ?>
<?php require_once('includes/functions-email.php'); SRP_PrintEmailOptions(); ?>
<?php endif; ?>

<!---- Prize preferences ---->
<?php if ($show == 'prizes'): ?>
    <h3>Hourly prizes</h3>
    <?php require_once('includes/functions-prizes.php'); SRP_PrintPrizeOptions(); ?>
    <div>&nbsp;</div>
    <h3>Grand prizes</h3>
    <?php require_once('includes/functions-grandprize.php'); SRP_PrintGrandPrizeOptions(); ?>
<?php endif; ?>

<!---- School preferences ---->
<?php if ($show == 'schools'): ?>
<?php require_once('includes/functions-school.php'); SRP_PrintSchoolOptions(); ?>
<?php endif; // schools ?>

<!---- Genre preferences ---->
<?php if ($show == 'genres'): ?>
<?php require_once('includes/functions-genre.php'); SRP_PrintGenreOptions(); ?>
<?php endif; ?>
        
    <div>&nbsp;</div>
    </div>
    <p><input type="submit" class="button-primary" name="submit" value="Save Changes" onClick="return confirmSubmission();" /></p>
</form>
<hr />
<div class="clear"></div>
</div>
<?php
}

function srptheme_addmenu()
{
	$page = add_theme_page('SRP Theme settings', 'SRP Theme settings', 'edit_themes', 'theme-settings', 'srptheme_theme_settings');
}

function setup_css()
{
    global $SrpTheme;
?>
<style type="text/css">
@import     "<?php echo get_bloginfo('stylesheet_url'); ?>";
@import     "<?php echo get_bloginfo('template_url') . '/options/side-default.css'; ?>";
#page       { background:#<?php echo $SrpTheme->getHeaderFooterColor(); ?>; }
body        { background:#<?php echo $SrpTheme->getSideColor(); ?>; }
#main-wrap1 { background:#<?php echo $SrpTheme->getBodyColor(); ?>; }
#main-wrap2 { background:#<?php echo $SrpTheme->getBodyColor(); ?>; }
#main       { background:#<?php echo $SrpTheme->getBodyColor(); ?>; }
</style>
<!--[if lte IE 6]>
<style type="text/css" media="screen">
@import "<?php bloginfo('template_url'); ?>/ie6.css";
</style>
<![endif]-->
<?php
}

/** Straight-up code **/

add_action('admin_menu', 'srptheme_addmenu');
add_action('admin_post_srptheme_update', 'srptheme_update_options');
add_action('wp_head', 'setup_css', 2);
add_filter( 'show_admin_bar', '__return_false' );
remove_action('init', 'wp_admin_bar_init');


if (!get_option('SRPTheme')) setup_options();

// register sidebars
if (function_exists('register_sidebar'))
{
    register_sidebar(array(
        'name' => 'Default sidebar',
        'id' => 'sidebar-1',
		'before_widget' => '<li class="block widget %2$s" id="%1$s"><div class="box"> <div class="wrapleft"><div class="wrapright"><div class="tr"><div class="bl"><div class="tl"><div class="br the-content">',
		'after_widget' => '</div></div></div></div></div></div> </div></li>',
		'before_title' => '<div class="titlewrap"><h4><span>',
		'after_title' => '</span></h4></div>'
    ));
}

?>
