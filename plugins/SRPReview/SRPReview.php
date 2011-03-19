<?php
/*
Plugin Name: SRP
Plugin URI: http://csammisrun.net/
Description: A plugin that registers various widgets for the Summer Reading Program
Version: 2.0
Author: Chris Sammis
Author URI: http://csammisrun.net/
*/

class SRPReviewWidget extends WP_Widget
{
    function SRPReviewWidget()
    {
        $widget_ops = array('classname' => 'SRPReviewWidget', 'description' => __('A listing of recent book reviews.'));
        $this->WP_Widget('SRPReviewWidget', __('SRP Reviews'), $widget_ops);
    }
    
    function PrintStars($count)
    {
        $imgdir = WP_PLUGIN_URL . '/SRPReview/';
        $title = "$count star rating";
        $empty_stars = 5 - $count;
        for ($i = 0; $i < $count; $i++)
        {
            echo '<img height="20" width="20" src="' . $imgdir . '/star_gold.png" alt="' . $title . '" title="' . $title . '" />';
        }
        
        for ($i = 0; $i < $empty_stars; $i++)
        {
            echo '<img height="20" width="20" src="' . $imgdir . '/star_empty.png" alt="' . $title . '" title="' . $title . '" />';
        }
    }
    
    function widget($args, $instance)
    {
        extract($args);
        
        $postcount = empty($instance['count']) ? 5  : $instance['count'];
        if ($postcount == 0)
            $postcount = 1;
            
        $query = new WP_Query(array('showposts' => $postcount, 'nopaging' => 0, 'post_status' => 'publish', 'caller_get_posts' => 1));
        echo $before_widget;
        $title = apply_filters('widget_title', empty($instance['title']) ? __('Recent reviews') : $instance['title']);
        if ($title)
        {
            echo '<div class="titlewrap"><h4><span>' . $title . '</span></h4></div>';
        }
        
        if (! $query->have_posts())
        {
            echo 'No reviews yet...';
        }
        else
        {
            echo '<ul>';
            while ($query->have_posts()) :
                $query->the_post();
                $post_id = get_the_ID();
                $genre_name = SRP_GetGenreName(get_post_meta($post_id, 'book_genre', true));
?>
<li class="SRPReviewSlug">
<div class="SRPTitle"><em><?php echo get_post_meta($post_id, 'book_title', true); ?></em>, <?php echo get_post_meta($post_id, 'book_author', true); ?></div>
<div class="SRPGenre"><span class="SRPRatingText">Genre:</span> <?php echo $genre_name; ?></div>
<div class="SRPRating"><span class="SRPRatingText">Rating:</span> <?php $this->PrintStars(get_post_meta($post_id, 'book_rating', true)); ?></div>
<div class="SRPReview"><?php echo strip_tags(get_the_content()); ?></div>
<div class="SRPAuthor">Reviewed by <?php the_author(); ?> (grade <?php the_author_meta('school_grade'); ?>) 
                       on <?php echo get_date_from_gmt(get_the_time('Y-m-d H:i:s'), 'F jS, Y'); ?></div>
</li>
<?php
            endwhile;
            echo '</ul>';
        }
        echo $after_widget;
        wp_reset_query(); // WP_Query cleanup
    }
    
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['count'] = $new_instance['count'];
        return $instance;
    }
    
    function form($instance) {
        $instance = wp_parse_args((array)$instance, array(
                                  'title' => __('Recent reviews'),
                                  'count' => 5
                                 ));
?>
<p>
<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label> 
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
  name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id('count'); ?>">Number of recent reviews to show:</label> 
<input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" 
  name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $instance['count']; ?>" />
</p>
<?php
    } // end form()
} // end class

/** Register the plugin into the system **/

function SRP_ReviewWidgetInit()
{
    register_widget('SRPReviewWidget');
}
add_action('widgets_init', 'SRP_ReviewWidgetInit');

function SRP_RestrictAdmin()
{
  global $current_user;
  get_currentuserinfo();

  if (is_user_logged_in() && !current_user_can('administrator'))
  {
    wp_die('You are not allowed to access this part of the site');
  }
}
add_action('admin_init', 'SRP_RestrictAdmin', 1);
?>
