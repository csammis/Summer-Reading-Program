<?php
/*
* Template Name: SRP Sponsors
* Simple template with sponsorship information

This WordPress plugin was developed for the Olathe Public Library, Olathe, KS
http://www.olathelibrary.org

Copyright (c) 2012, Chris Sammis
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

require_once('includes/srp-inc-template.php');

$srp_leftcolumnwidth = 100;

SRP_PrintPageStart($srp_leftcolumnwidth);
if (have_posts()) : the_post(); /* start The Loop so we can get the page ID */
?>
<div id="post-<?php the_ID(); ?>" <?php if (function_exists("post_class")) post_class(); else print 'class="post"'; ?>>
<h2><?php the_title(); ?></h2>
<div class="post-content clearfix"><?php the_content('Read the rest of this page &raquo;'); ?></div>
</div>
<?php
endif; /* end The Loop */
SRP_PrintPageEnd($srp_leftcolumnwidth);
?>
