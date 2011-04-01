<?php
/*
* Template Name: SRP Comment Poster
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

if (!isset($_POST['postid']))
{
    die('No access to this page is allowed.');
}

$postid = $_POST['postid'];
$commenttext = $_POST['commenttext'];
$username = $_POST['username'];

$comment_data = array(
    'comment_post_ID' => $postid,
    'comment_content' => $commenttext,
    'comment_author' => $username,
    'comment_date_gmt' => gmdate('Y-m-d H:i:s'),
    'comment_approved' => 1
);

//print_r($comment_data);

echo wp_insert_comment($comment_data);

?>
