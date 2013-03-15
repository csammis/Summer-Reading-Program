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

if(!defined("PHP_EOL")) define("PHP_EOL", strtoupper(substr(PHP_OS,0,3) == "WIN") ? "\r\n" : "\n");

function setup_options()
{
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

function get_srptheme_appearance($name)
{
    $retval = '';
    if ($name == 'header')
    {
        if (is_srptheme_option_set('srp_headerimg'))
        {
            $retval = get_srptheme_option('srp_headerimg');
        }
        else
        {
            $retval = get_bloginfo('template_directory') . '/images/headerimg.png';
        }
    } 
    else if ($name == 'footer')
    {
        if (is_srptheme_option_set('srp_footerimg'))
        {
            $retval = get_srptheme_option('srp_footerimg');
        }
        else
        {
            $retval = get_bloginfo('template_directory') . '/images/footerimg.png';
        }
    }
    else if ($name == 'backcolor1')
    {
        if (is_srptheme_option_set('srp_backcolor1'))
        {
            $retval = get_srptheme_option('srp_backcolor1');
        }
        else
        {
            $retval = '1E6088';
        }
    }
    else if ($name == 'backcolor2')
    {
        if (is_srptheme_option_set('srp_backcolor2'))
        {
            $retval = get_srptheme_option('srp_backcolor2');
        }
        else
        {
            $retval = '3D2D1E';
        }
    }
    else if ($name == 'backcolor3')
    {
        if (is_srptheme_option_set('srp_backcolor3'))
        {
            $retval = get_srptheme_option('srp_backcolor3');
        }
        else
        {
            $retval = 'B5D1E6';
        }
    }
    
    return $retval;
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
        else if ($message == 'srp_submitagreement')
        {
            $retval = 'Reminder: the book reviews you post must be your own work (%%explainlink:Why is this important?%%)';
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
	if (!current_user_can('edit_themes')) wp_die('You are not authorised to perform this operation.');
	$options = get_option('SRPTheme');
    
    $prefmode = $_POST['active_show'];
    if ($prefmode == 'email')
    {
        if (isset($_POST['gmail_reply_to'])) $options['gmail_reply_to'] = $_POST['gmail_reply_to'];
        if (isset($_POST['gmail_account'])) $options['gmail_account'] = $_POST['gmail_account'];
        if (isset($_POST['gmail_password'])) $options['gmail_password'] = $_POST['gmail_password'];
    }
    else if ($prefmode == 'messages')
    {
        if (isset($_POST['srp_hourlyemail'])) $options['srp_hourlyemail'] = $_POST['srp_hourlyemail'];
        if (isset($_POST['srp_hourlynotice'])) $options['srp_hourlynotice'] = $_POST['srp_hourlynotice'];
        if (isset($_POST['srp_regagreement'])) $options['srp_regagreement'] = $_POST['srp_regagreement'];
        if (isset($_POST['srp_weeklyemail'])) $options['srp_weeklyemail'] = $_POST['srp_weeklyemail'];
        if (isset($_POST['srp_footertext'])) $options['srp_footertext'] = $_POST['srp_footertext'];
        if (isset($_POST['srp_submitagreement'])) $options['srp_submitagreement'] = $_POST['srp_submitagreement'];
    }
    else if ($prefmode == 'prizes')
    {
        if (isset($_POST['srp_gprize_every'])) $options['srp_gprize_every'] = $_POST['srp_gprize_every'];
        if (isset($_POST['srp_gprize_numentries'])) $options['srp_gprize_numentries'] = $_POST['srp_gprize_numentries'];

        $options = SRP_StoreSimpleDynamicOptions($options, $_POST, 'srp_hprize', 'nexthprizeid');
        $options = SRP_StoreSimpleDynamicOptions($options, $_POST, 'srp_gprize', 'nextgprizeid');
    }
    else if ($prefmode == 'general')
    {
        if (isset($_POST['ga_id'])) $options['ga_id'] = $_POST['ga_id'];
        if (isset($_POST['rc_pub_key'])) $options['recaptcha_public'] = $_POST['rc_pub_key'];
        if (isset($_POST['rc_priv_key'])) $options['recaptcha_private'] = $_POST['rc_priv_key'];

        if (isset($_POST['program_active']))
        {
            $options['program_active'] = $_POST['program_active'];
            if ($_POST['program_active'] == 1)
            {
                $options['program_open_date'] = time();
            }
        }
        
        if (isset($_POST['srp_headerimg'])) $options['srp_headerimg'] = $_POST['srp_headerimg'];
        if (isset($_POST['srp_footerimg'])) $options['srp_footerimg'] = $_POST['srp_footerimg'];
        if (isset($_POST['srp_headerimgheight'])) $options['srp_headerimgheight'] = $_POST['srp_headerimgheight'];
        if (isset($_POST['srp_footerimgheight'])) $options['srp_footerimgheight'] = $_POST['srp_footerimgheight'];
        if (isset($_POST['srp_backcolor1'])) $options['srp_backcolor1'] = $_POST['srp_backcolor1'];
        if (isset($_POST['srp_backcolor2'])) $options['srp_backcolor2'] = $_POST['srp_backcolor2'];
        if (isset($_POST['srp_backcolor3'])) $options['srp_backcolor3'] = $_POST['srp_backcolor3'];
        if (isset($_POST['max_length'])) $options['max_length'] = $_POST['max_length'];
        
        if (isset($_POST['library_name'])) $options['library_name'] = $_POST['library_name'];
        
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
?>
<style type="text/css">
<?php
    $style_uri = get_bloginfo('stylesheet_url');

    if (SRP_IsMobile())
    {
        $style_uri = str_replace('style.css', 'mobile.style.css', $style_uri);
    }
?>
@import     "<?php echo $style_uri; ?>";
@import     "<?php echo get_bloginfo('template_url') . '/options/side-default.css'; ?>";
#page       { background:#<?php echo get_srptheme_appearance('backcolor1'); ?>; }
body        { background:#<?php echo get_srptheme_appearance('backcolor2'); ?>; }
#main-wrap1 { background:#<?php echo get_srptheme_appearance('backcolor3'); ?>; }
#main-wrap2 { background:#<?php echo get_srptheme_appearance('backcolor3'); ?>; }
#main       { background:#<?php echo get_srptheme_appearance('backcolor3'); ?>; }
</style>
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
