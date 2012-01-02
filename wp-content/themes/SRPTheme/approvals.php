<?php
/*
* Template Name: SRP Approval Page
* Approves or denies pending reviews.

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
SRP_AuthRedirect($SRP_AUTH_ADMIN);

require_once('includes/srp-inc-utility.php');
require_once('includes/srp-inc-template.php');


if ($_POST['action'] == 'process')
{
    require_once('includes/srp-inc-admin.php');
    
	$op_ids = array();
	foreach (array_keys($_POST) as $key)
	{
		if (substr($key, 0, 16) == 'SRP_ApprovePost_')
		{
			$op_ids[] = substr($key, 16);
		}
	}
	
	if (isset($_POST['SRPApprovePosts']))
	{
		SRP_PublishPosts($op_ids);
	}
	else if (isset($_POST['SRPDeletePosts']))
	{
		SRP_DeletePosts($op_ids);
	}
}

$srp_leftcolumnwidth = 100;

SRP_PrintPageStart($srp_leftcolumnwidth);
if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */

?>
<div id="post-<?php the_ID(); ?>" <?php if (function_exists("post_class")) post_class(); else print 'class="post"'; ?>>
<h2><?php the_title(); ?></h2>
<div>&nbsp;</div>
<?php
    $query = new WP_Query(array('nopaging' => 1, 'post_status' => 'pending', 'caller_get_posts' => $current_user->ID));
	if ($query->have_posts())
	{
		$bPrintTableEnd = true;
?>
<script type="text/javascript">
bAllChecked = false;
function checkAll()
{
	var form = document.getElementById('SRPApprove');
	bAllChecked = !bAllChecked;
	for (var i = 0; i < form.elements.length; i++)
	{
		form.elements[i].checked = bAllChecked;
	}
}
</script>

<form method="post" id="SRPApprove" action="<?php echo get_permalink(get_the_ID()); ?>">
<input type="hidden" name="action" value="process" />
<table>
<tr>
	<th><input type="checkbox" name="checkall" onClick="javascript:checkAll();" /></th>
	<th>Date posted</th>
	<th>Title / Author</th>
	<th>Content</th>
</tr>
<?php
	}
	
    while ($query->have_posts()) :
        $query->the_post();
        $post_id = get_the_ID();
?>
<tr>
<td><input type="checkbox" name="<?php echo "SRP_ApprovePost_$post_id"; ?>" /></td>
<td><?php echo get_date_from_gmt(get_the_time('Y-m-d H:i:s'), 'F jS, Y'); ?></td>
<td><em><?php echo get_post_meta($post_id, 'book_title', true); ?></em>, <?php echo get_post_meta($post_id, 'book_author', true); ?></td>
<td><?php echo strip_tags(get_the_content()); ?></td>
</tr>
<?php
    endwhile;
	if ($bPrintTableEnd == true)
	{
?>
</table>
<p><input type="submit" name="SRPApprovePosts" value="Approve all checked posts" />&nbsp;&nbsp;&nbsp;<input type="submit" name="SRPDeletePosts" value="Delete all checked posts" /></p>
</form>
<?php
	}
    wp_reset_query();
?>
</div>
<?php
endif; /* end The Loop */
SRP_PrintPageEnd($srp_leftcolumnwidth);
?>
