<?php
/*
* Template Name: SRP Review Page
* Lists and search controls for book reviews

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
SRP_AuthRedirect($SRP_UNAUTHENTICATED);

require_once('includes/srp-inc-search.php');
require_once('includes/srp-inc-template.php');
require_once('includes/srp-inc-lists.php');
require_once('includes/srp-inc-utility.php');

$s_author = esc_attr(stripslashes($_REQUEST['s_author']));
$s_title  = esc_attr(stripslashes($_REQUEST['s_title']));
$s_rating = esc_attr(stripslashes($_REQUEST['s_rating']));
$s_genre  = esc_attr(stripslashes($_REQUEST['s_genre']));
$s_grade  = esc_attr(stripslashes($_REQUEST['s_grade']));

$startPost = $_REQUEST['lastPost'];
if (!is_numeric($startPost) || $startPost < 0)
{
	$startPost = 0;
}

$limit = 10;

$posts = SRP_PostSearch($s_author, $s_title, $s_rating, $s_genre, $s_grade, $startPost, $limit);

SRP_PrintPageStart(75);
if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
?>
        <div id="post-<?php the_ID(); ?>" <?php if (function_exists("post_class")) post_class(); else print 'class="post"'; ?>>
        <h2><?php the_title(); ?></h2>
        <div class="post-content clearfix"><?php the_content('Read the rest of this page &raquo;'); ?></div>
        <div>&nbsp;</div>
<?php
	if (count($posts) == 0)
	{
?>
        <div class="errormsg">No reviews found</div>
<?php
	} 
	else
	{
?>
        <div class="reviewbox">
<?php
		$bHasMore = false;
		for($i = 0; $i < count($posts); $i++)
		{
			$post = $posts[$i];
			$authorID = $post['authorID'];
			$bHasMore = $post['has_more'];
?>
            <div class="SRPReviewListing">
                <div class="SRPTitleListing"><em><?php echo $post['book_title']; ?></em>, <?php echo $post['book_author']; ?></div>
                <div class="SRPGenreListing"><span class="SRPRatingText">Genre:</span> <?php echo SRP_GetGenreName($post['book_genre']); ?></div>
                <div class="SRPRatingListing"><span class="SRPRatingText">Rating:</span> <?php SRP_PrintStars($post['book_rating']); ?></div>
                <div class="SRPReviewListingContent"><?php echo strip_tags($post['content']); ?></div>
                <div class="SRPAuthorListing">
                    Reviewed by <?php echo get_usermeta($authorID, 'first_name'); ?>
                    (grade <?php echo $post['author_grade']; ?>)
                    on <?php echo get_date_from_gmt(date('Y-m-d H:i:s', strtotime($post['date'])), 'F jS, Y'); ?>
                </div>
                <div id="SRPCommentContainer-<?php echo $post['id']; ?>" style="margin:0.2em;border-top:0.1em dashed gray;">
                <?php
                    if ($post['comment_count'] > 0)
                    {
                        $comments = SRP_GetCommentsOnPost($post['id']);
                        for ($j = 0; $j < count($comments); $j++)
                        {
                            $comment = $comments[$j];
                ?>
                    <div class="SRPAdminComment"><?php echo $comment['content'];?> <em>posted <?php
                        echo get_date_from_gmt(date('Y-m-d H:i:s', strtotime($comment['date'])), 'F jS, Y'); ?></em></div>
                <?php
                        }
                    }
                ?>
                
                </div>
                <?php if (SRP_IsUserAdministrator()) : ?>
                <!-- Javascript method to add a new comment inline -->
                <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
                <script language="javascript">
                function addNewComment(postId, jqueryUrl)
                {
                    var commentDisplay = '';

                    var commentTextField = document.getElementById("comment-field-" + postId);
                    commentText = commentTextField.value;
                    commentTextField.value = '';
                    commentText = $.trim(commentText);
                    if (commentText == '')
                    {
                        return false;
                    }
                    
                    // Do jquery post to insert new comment to this page
                    $.post(jqueryUrl,
                        { "postid" : postId, "commenttext" : commentText, "username" : 'admin' },
                        function (data) {
                            commentDisplay = data;
                    
                            var parent = document.getElementById("SRPCommentContainer-" + postId);

                            var commentGroup = document.createElement('div');
                            commentGroup.setAttribute('class', 'SRPAdminComment');
                            var commentTextSpan = document.createElement('span');
                            commentTextSpan.innerHTML = commentDisplay;
                            commentGroup.appendChild(commentTextSpan);
                            parent.appendChild(commentGroup);
                        });

                    return false;
                }
                </script>

                <div class="SRPAdminNewComment" id="form-new-comment-<?php echo $post['id']; ?>">
                    <!-- <form onSubmit="javascript:addNewComment( < ?php echo $post['id']; ?>)"> -->
                    <div>
                    <textarea style="width:90%" id="comment-field-<?php echo $post['id']; ?>"></textarea>
                    </div>
                    <div><input type="submit" value="Add comment"
                        onClick="javascript:addNewComment(<?php echo $post['id']; ?>, '<?php echo site_url('/') . "/comment-poster/";?>');" /></div>
                    <!-- </form> -->
                </div>
                <?php endif; ?>
            </div>
<?php
		}
?>
        </div> <!-- reviewbox -->
<?php   if ($bHasMore || $startPost != 0) : ?>
        <div class="SRPReviewNav">
<?php
            if ($bHasMore)
            {
                $lastPost = $startPost + $limit;
                $olderUrl = SRP_SelectUrlOfTemplatedPage('reviewpage') .
                            "/?lastPost=$lastPost&s_author=$s_author&s_title=$s_title&s_rating=$s_rating&s_genre=$s_genre&s_grade=$s_grade";
?>
        <a href="<?php echo $olderUrl; ?>">&lt;&lt; Older Reviews</a>
<?php
            }

            if ($startPost != 0)
            {
                $lastPost = $startPost - $limit;
                $newerUrl = SRP_SelectUrlOfTemplatedPage('reviewpage') .
                            "/?lastPost=$lastPost&s_author=$s_author&s_title=$s_title&s_rating=$s_rating&s_genre=$s_genre&s_grade=$s_grade";
?>
        <a href="<?php echo $newerUrl; ?>">Newer Reviews &gt;&gt;</a>
<?php       } ?>
        </div> <!-- SRPReviewNav -->
<?php
        endif;
    }
?>
        </div>
<?php
endif; /* end The Loop */
SRP_PrintPageEndWithSearch(SRP_SelectUrlOfTemplatedPage('reviewpage'));
?>
