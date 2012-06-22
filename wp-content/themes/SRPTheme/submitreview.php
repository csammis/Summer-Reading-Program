<?php
/*
* Template Name: SRP Review Submission
* Controls to submit a review of a book

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

require_once('includes/srp-inc-users.php');
SRP_AuthRedirect($SRP_AUTHENTICATED);

require_once('includes/srp-inc-template.php');
require_once('includes/srp-inc-utility.php');

$srp_leftcolumnwidth = 100;

$action_type = $_POST['action'];
if (empty($action_type))
{
    $action_type = 'start';

	$submitreview_info = get_usermeta($current_user->ID, 'submitreview_info');
	if (strlen($submitreview_info) > 0)
	{
		$info = explode('=', $submitreview_info);
		$srp_title = urldecode($info[0]);
		$srp_author = urldecode($info[1]);
		$srp_genre = urldecode($info[2]);
	}
	delete_usermeta($current_user->ID, 'submitreview_info');
}

switch ($action_type)
{
    case 'postreview':
        /** Collect and validate input **/
        $reqfields = '';
        $srp_title = esc_attr(stripslashes($_POST['srp_title']));
        if (strlen($srp_title) == 0) $reqfields .= 'srp_title:';
        $srp_author = esc_attr(stripslashes($_POST['srp_author']));
        if (strlen($srp_author) == 0) $reqfields .= 'srp_author:';
        $srp_review = substr(esc_attr(stripslashes($_POST['srp_review'])), 0, get_srptheme_option('max_length'));
        if (strlen($srp_review) == 0) $reqfields .= 'srp_review:';
        $srp_rating = esc_attr(stripslashes($_POST['srp_rating']));
        if (strlen($srp_rating) == 0) $reqfields .= 'srp_rating:';
		$srp_genre = esc_attr(stripslashes($_POST['srp_genre']));
        if (strlen($srp_genre) == 0) $reqfields .= 'srp_genre:';
        
        if (strlen($reqfields) != 0)
        {
            $errorcode = 'reqfields';
        }
        else
        {
            // Figure out how many reviews this person has posted
            $query = new WP_Query(array('nopaging' => 1, 'author' => $current_user->ID));
            $newpost_title = $current_user->ID . '-review-' . $query->post_count;
            // Create a new post with the review information tagged in
            $post = array(
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'post_content' => $srp_review,
                        'post_date_gmt' => gmdate('Y-m-d H:i:s'),
                        'post_status' => 'pending',
                        'post_title' => $newpost_title,
                        'post_type' => 'post'
                    );
            $postid = wp_insert_post($post);
            if ($postid <= 0)
            {
                $errorcode = 'could not insert post: ' . $postid;
            }
            else
            {
                add_post_meta($postid, 'book_title', $srp_title, true) or update_post_meta($postid, 'book_title', $srp_title);
                add_post_meta($postid, 'book_author', $srp_author, true) or update_post_meta($postid, 'book_author', $srp_author);
                add_post_meta($postid, 'book_rating', $srp_rating, true) or update_post_meta($postid, 'book_rating', $srp_rating);
				add_post_meta($postid, 'book_genre', $srp_genre, true) or update_post_meta($postid, 'book_genre', $srp_genre);
                // For lack of anything better to do, head back to the index page
                header('Location: ' . site_url('/'));
                exit();
            }
        }

    case 'start':        
        SRP_PrintPageStart($srp_leftcolumnwidth);
        if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
?>
        <div id="post-<?php the_ID(); ?>" <?php if (function_exists("post_class")) post_class(); else print 'class="post"'; ?>>
        <h2><?php the_title(); ?></h2>
        <div class="post-content clearfix"><?php the_content('Read the rest of this page &raquo;'); ?></div>
<?php
        if (isset($errorcode))
        {
            $errormsg = '<div class="errormsg">Whoops, your review couldn\'t be posted: ';
            switch ($errorcode)
            {
                case 'reqfields':
                    $errormsg .= 'Some fields were left blank.';
                    break;
                default:
                    $errormsg .= 'An unknown error has occured: ' . $errorcode;
                    break;
            }
            $errormsg .= '</div>';
            echo $errormsg;
        }
?>
        <script language="javascript">
        function displayAndRestrictTextLength(taObj, counterId)
        {
            var maxLen = <?php echo get_srptheme_option('max_length'); ?>;
            
            var counter = createObject(counterId);
            if (taObj.value.length > maxLen) taObj.value = taObj.value.substring(0, maxLen);
            if (counter)
            {
                var lenRemaining = maxLen - taObj.value.length;
                if (lenRemaining <= 0)
                {
                    counter.setAttribute('style', 'color:red; font-weight:bold;');
                }
                else
                {
                    counter.setAttribute('style', 'font-weight:bold');
                }
                counter.innerHTML = lenRemaining;
            }
            return true;
        }
        
        function createObject(objId)
        {
            if (document.getElementById) return document.getElementById(objId);
            else if (document.layers) return eval("document." + objId);
            else if (document.all) return eval("document.all." + objId);
            else return eval("document." + objId);
        }
        
        window.onLoad = displayAndRestrictTextLength;
        </script>
<?php
		if (get_srptheme_option('program_active') == 0)
		{
?>
		<div>&nbsp;</div>
		<div>Sorry, the Summer Reading Program is closed!</div>
<?php
		}
		else
		{
?>
			<div>&nbsp;</div>
			<div class="reviewbox">
            <form id="SRPReview" method="post" action="<?php echo get_permalink(get_the_ID()); ?>">
            <input type="hidden" name="action" value="postreview" />
            <input type="hidden" name="page_id" value="<?php the_ID(); ?>" />
            <p>
            <label <?php if (strpos($reqfields, 'srp_title:') !== FALSE) echo 'class="errormsg"';?>>Title:<br />
            <input type="text" name="srp_title" id="srp_title" class="SRPInput"
                   value="<?php echo esc_attr(stripslashes($srp_title)); ?>" size="40" />
            </label>
            </p>
            <p>
            <label <?php if (strpos($reqfields, 'srp_author:') !== FALSE) echo 'class="errormsg"';?>>Author:<br />
            <input type="text" name="srp_author" id="srp_author" class="SRPInput"
                   value="<?php echo esc_attr(stripslashes($srp_author)); ?>" size="40" />
            </label>
            </p>
            <p>
            <label <?php if (strpos($reqfields, 'srp_review:') !== FALSE) echo 'class="errormsg"';?>>Review:<br />
            <span class="srp_forminfo">Please use appropriate language in your reviews.</span><br />
            <textarea rows="5" cols="40" name="srp_review" id="srp_review" class="SRPInput"
                      onKeyUp="return displayAndRestrictTextLength(this, 'srp_counter');"><?php echo esc_attr(stripslashes($srp_review)); ?></textarea><br />
            <span class="srp_forminfo"><span style="font-weight:bold" id="srp_counter"><?php echo get_srptheme_option('max_length'); ?></span> characters remaining</span>
            </label>
            </p>
            <p>
            <label <?php if (strpos($reqfields, 'srp_genre:') !== FALSE) echo 'class="errormsg"';?>>Which best describes this book?<br />
            <?php SRP_PrintGenreSelector('srp_genre', $srp_genre); ?>
            </label>
            </p>
            <p>
            <label <?php if (strpos($reqfields, 'srp_rating:') !== FALSE) echo 'class="errormsg"';?>>Rating:<br />
            <select name="srp_rating" class="SRPInput">
                <option value="5" <?php if ($srp_rating == 5) echo 'selected="selected"';?>>5 - Great!</option>
                <option value="4" <?php if ($srp_rating == 4) echo 'selected="selected"';?>>4 - Pretty good</option>
                <option value="3" <?php if ($srp_rating == 3) echo 'selected="selected"';?>>3 - Not bad</option>
                <option value="2" <?php if ($srp_rating == 2) echo 'selected="selected"';?>>2 - Wouldn't recommend it</option>
                <option value="1" <?php if ($srp_rating == 1) echo 'selected="selected"';?>>1 - Terrible</option>
            </select>
            </label>
            </p>
            <div><?php echo SRP_FormatMessage('srp_submitagreement'); ?></div>
            <p>
            <input type="submit" value="Post review" onClick="this.disabled=true; this.value='Posting...'; this.form.submit();" />
            </p>
            </form>
			</div>
<?php
		} // end closed check
?>
		</div>
<?php
		
        endif; /* end The Loop */
        SRP_PrintPageEnd($srp_leftcolumnwidth, 'srp_title');
        break;
}
?>
