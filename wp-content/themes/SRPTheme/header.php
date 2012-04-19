<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php
require_once('includes/srp-inc-utility.php');
require_once('includes/srp-inc-template.php');
require_once('includes/srp-inc-search.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php wp_title('&laquo;', true, 'right'); if (get_query_var('cpage') ) echo ' Page '.get_query_var('cpage').' &laquo; ';?> <?php bloginfo('name'); ?></title>
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/favicon.ico" />
<?php if (is_singular() && get_option('thread_comments')) wp_enqueue_script( 'comment-reply' ); ?>
<?php wp_head(); ?>
<?php
    global $SRP_RIGHTWIDTH;
    global $SRP_LEFTWIDTH;

    $height = get_srptheme_option('srp_headerimgheight');

    // Generate the CSS for the column widths
    echo "<style type=\"text/css\">\n";
    if (strlen($height) > 0)
    {
        
        $headerheight = $height; //csnote there used to be a height offset of 17px here
        $height = $height - 15;
        $height .= 'px';
        $headerheight .= 'px';
        echo "#nav-wrap1 { top:$height; }\n";
        echo "#header { height:$headerheight; }\n";
        echo "#header .block-content{ height:$headerheight; }\n";
    }
    echo "#page.with-sidebar .mask-main .mask-left {\n";
    echo "  right:$SRP_RIGHTWIDTH%;\n";
    echo "}\n";
    echo "#page.with-sidebar .mask-main .col1 {\n";
    echo "  width:$SRP_LEFTWIDTH%;\n";
    echo "  left:$SRP_RIGHTWIDTH%;\n";
    echo "}\n";
    echo "#page.with-sidebar .mask-main .col2 {\n";
    if ($SRP_RIGHTWIDTH == 0)
        echo "  visibility:collapse;\n";
    else
    {
        echo "  width:$SRP_RIGHTWIDTH%;\n";
        echo "  left:$SRP_RIGHTWIDTH%;\n";
    }
    echo "}\n</style>\n";
?>
</head>
<body <?php if (is_home()) { ?>class="home"<?php } else { ?>class="inner"<?php } ?>>
<!-- page wrap -->
<div id="page" class="with-sidebar">

<!-- header -->
<div id="header-wrap">
    <div id="header" class="block-content">
        <div id="pagetitle"><?php SRP_PrintHeaderImg(); ?></div>
        <!-- main navigation -->
        <div id="nav-wrap1">
            <div id="nav-wrap2">
                <ul id="nav">
                <?php
                    $page_link_array = SRP_SelectPageIdsForNav();
                    foreach ($page_link_array as $page_name => $page_link)
                    {
                        echo '<li class="page_item"><a class="fadeThis" href="' . $page_link . "\"><span>$page_name</span></a></li>\n";
                    }

                    if (is_user_logged_in())
                    {
                        $list .= '<li><a class="fadeThis" href="' . wp_logout_url(site_url('/')) . '"><span>Log out</span></a></li>' . "\n";
                    }
                    echo $list;
                ?>
                </ul>
            </div>
        </div>
        <!-- /main navigation -->
    </div>
</div>
<!-- /header -->
