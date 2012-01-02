<?php
 get_header();
?>

<!-- main wrappers -->
<div id="main-wrap1">
 <div id="main-wrap2">

  <!-- main page block -->
  <div id="main" class="block-content">
   <div class="mask-main rightdiv">
    <div class="mask-left">

     <!-- first column -->
     <div class="col1">
      <div id="main-content">

       <?php if (have_posts()) : ?>
       <?php while (have_posts()) : the_post(); ?>

        <!-- post -->
        <div id="post-<?php the_ID(); ?>" <?php if (function_exists("post_class")) post_class(); else print 'class="post"'; ?>>

          <div class="post-header">
           <h3 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php echo 'Permanent Link:'; echo ' '; the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
           <p class="post-date">
            <span class="month"><?php the_time('M'); ?></span>
            <span class="day"><?php the_time('j'); ?></span>
           </p>
           <p class="post-author">
            <span class="info"><?php printf('Posted by %s in %s','<a href="'. get_author_posts_url(get_the_author_ID()) .'" title="'. sprintf("Posts by %s", attribute_escape(get_the_author())).' ">'. get_the_author() .'</a>',get_the_category_list(', '));
            ?> | <?php comments_popup_link('No Comments', '1 Comment', '% Comments', 'comments', 'Comments off'); ?>  <?php edit_post_link('Edit',' | '); ?>
            </span>
           </p>
          </div>

          <div class="post-content clearfix">

          <?php
           $posttags = get_the_tags();
           if ($posttags) { ?>
            <p class="tags"> <?php the_tags('Tags:'.' ', ', ', ''); ?></p>
          <?php } ?>
          </div>

        </div>
        <!-- /post -->


       <?php endwhile; ?>

       <div class="navigation" id="pagenavi">
       <?php if(function_exists('wp_pagenavi')) : ?>
        <?php wp_pagenavi() ?>
 	   <?php else : ?>
        <div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
        <div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
        <div class="clear"></div>
       <?php endif; ?>
       </div>
       <?php else : ?>
       <h2><?php _e("Not Found"); ?></h2>
       <p class="error"><?php _e("Sorry, but you are looking for something that isn't here."); ?></p>
       <?php get_search_form(); ?>
       <?php endif; ?>

      </div>
     </div>
     <!-- /first column -->
     <?php get_sidebar(); ?>
     <?php include(TEMPLATEPATH . '/sidebar-secondary.php'); ?>

    </div>
   </div>
   <div class="clear-content"></div>
  </div>
  <!-- /main page block -->

 </div>
</div>
<!-- /main wrappers -->

<?php get_footer(); ?>
