<?php
/*
* srp-inc-search.php
* Review retrieval functions.

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

//  SRP_PostSearch
//  SRP_GetPostsByUser
//  SRP_GetCommentsOnPost

function SRP_PostSearch($author, $title, $rating, $genre, $grade, $startAt = 0, $limit = 10)
{
    global $wpdb;

    $bUseAuthor = strlen($author) > 0;
    $bUseTitle  = strlen($title)  > 0;
    $bUseRating = strlen($rating) > 0 && is_numeric($rating);
    $bUseGenre  = strlen($genre)  > 0 && is_numeric($genre);
    $bUseGrade  = strlen($grade)  > 0 && is_numeric($grade);

    $limitPlus = $limit + 1;

    $where = '';

    $params = array();

    $select  = "SELECT post.id AS ID, post.post_date AS date, post.post_content AS content, post.post_author AS author, ";
    $select .= "  meta_author.meta_value AS book_author, meta_title.meta_value AS title, ";
    $select .= "  meta_rating.meta_value AS rating, meta_genre.meta_value AS genre, meta_grade.meta_value AS grade, ";
    $select .= "  post.comment_count AS comments ";
    $select .= "FROM $wpdb->posts post ";

    /////// AUTHOR ///////
    if ($bUseAuthor === TRUE)
    {
        $select .= "INNER JOIN ";
    }
    else
    {
        $select .= "LEFT OUTER JOIN ";
    }  
    $select .= "$wpdb->postmeta meta_author ON (meta_author.post_id = post.id AND meta_author.meta_key = %s";
    $params[] = 'book_author';
    if ($bUseAuthor === TRUE)
    {
        $select .= " AND meta_author.meta_value REGEXP '%s'";
        $params[] = $author;
        $where .= " AND meta_author.meta_value IS NOT NULL";
    }
    $select .= ") ";

    /////// TITLE ///////
    if ($bUseTitle === TRUE)
    {
        $select .= "INNER JOIN ";
    }
    else
    {
        $select .= "LEFT OUTER JOIN ";
    }
    $select .= "$wpdb->postmeta meta_title ON (meta_title.post_id = post.id AND meta_title.meta_key = %s";
    $params[] = 'book_title';
    if ($bUseTitle === TRUE)
    {
        $select .= " AND meta_title.meta_value REGEXP '%s'";
        $params[] = $title;
        $where .= " AND meta_title.meta_value IS NOT NULL";
    }
    $select .= ") ";

    /////// RATING ///////
    if ($bUseRating === TRUE)
    {
        $select .= "INNER JOIN ";
    }
    else
    {
        $select .= "LEFT OUTER JOIN ";
    }
    $select .= "$wpdb->postmeta meta_rating ON (meta_rating.post_id = post.id AND meta_rating.meta_key = %s";
    $params[] = 'book_rating';
    if ($bUseRating === TRUE)
    {
        $select .= " AND meta_rating.meta_value = %d";
        $params[] = $rating;
        $where .= " AND meta_rating.meta_value IS NOT NULL";
    }
    $select .= ") ";

    /////// GENRE ///////
    if ($bUseGenre === TRUE)
    {
        $select .= "INNER JOIN ";
    }
    else
    {
        $select .= "LEFT OUTER JOIN ";
    }
    $select .= "$wpdb->postmeta meta_genre ON (meta_genre.post_id = post.id AND meta_genre.meta_key = %s";
    $params[] = 'book_genre';
    if ($bUseGenre === TRUE)
    {
        $select .= " AND meta_genre.meta_value = %s";
        $params[] = $genre;
        $where .= " AND meta_genre.meta_value IS NOT NULL";
    }
    $select .= ") ";
    
    /////// GRADE ///////
    if ($bUseGrade === TRUE)
    {
        $select .= 'INNER JOIN ';
    }
    else
    {
        $select .= 'LEFT OUTER JOIN ';
    }
    $select .= "$wpdb->usermeta meta_grade ON (meta_grade.user_id = post.post_author AND meta_grade.meta_key = %s";
    $params[] = 'school_grade';
    if ($bUseGrade === TRUE)
    {
        $select .= ' AND meta_grade.meta_value = %s';
        $params[] = $grade;
        $where .= ' AND meta_grade.meta_value IS NOT NULL';
    }
    $select .= ') ';

    $select .= "WHERE (post.post_type = %s AND post.post_status = %s) $where ORDER BY post.post_date DESC LIMIT $startAt, $limitPlus ";
    $params[] = 'post';
    $params[] = 'publish';

    $wpdb->show_errors();
    $query = $wpdb->prepare($select, $params);

    $IDs      = $wpdb->get_col($query, 0);
    $dates    = $wpdb->get_col($query, 1);
    $contents = $wpdb->get_col($query, 2);
    $authors  = $wpdb->get_col($query, 3);
    $bauthors = $wpdb->get_col($query, 4);
    $btitles  = $wpdb->get_col($query, 5);
    $ratings  = $wpdb->get_col($query, 6);
    $genres   = $wpdb->get_col($query, 7);
    $grades   = $wpdb->get_col($query, 8);
    $comments = $wpdb->get_col($query, 9);

    $bHasMore = false;
    if (count($IDs) > $limit)
    {
        $bHasMore = true;
    }

    $retval = array();
    for ($i = 0; $i < count($IDs) && $i < $limit; $i++)
    {
        $review = array('id' => $IDs[$i],
                        'date' => $dates[$i],
                        'content' => $contents[$i],
                        'authorID' => $authors[$i],
                        'book_author' => $bauthors[$i],
                        'book_title' => $btitles[$i],
                        'book_rating' => $ratings[$i],
                        'book_genre' => $genres[$i],
                        'author_grade' => $grades[$i],
                        'comment_count' => $comments[$i],
                        'has_more' => $bHasMore);
        $retval[] = $review;
    }
    return $retval;
}

/*
 * SRP_GetPostsByUser
 * Returns an associative array with all posts by a given user.
 */
function SRP_GetPostsByUser($userid, $status)
{
    global $wpdb;
    
    $select  = "SELECT post.id AS ID, post.post_date AS date, ";
    $select .= "       meta_author.meta_value AS book_author, meta_title.meta_value AS title, post.comment_count AS comments ";
    $select .= "FROM $wpdb->posts post ";
    
    $select .= "INNER JOIN $wpdb->postmeta meta_author ON (meta_author.post_id = post.id AND meta_author.meta_key = %s) ";
    $params[] = 'book_author';
    $select .= "INNER JOIN $wpdb->postmeta meta_title ON (meta_title.post_id = post.id AND meta_title.meta_key = %s) ";
    $params[] = 'book_title';
    
    $select .= "WHERE post.post_type = %s AND post.post_status = %s and post.post_author = %s ORDER BY post.post_date DESC";
    $params[] = 'post';
    $params[] = $status;
    $params[] = $userid;
    
    $wpdb->show_errors();
    $query = $wpdb->prepare($select, $params);

    $IDs      = $wpdb->get_col($query, 0);
    $dates    = $wpdb->get_col($query, 1);
    $bauthors = $wpdb->get_col($query, 2);
    $btitles  = $wpdb->get_col($query, 3);
    $comments = $wpdb->get_col($query, 4);
    
    $retval = array();
    for ($i = 0; $i < count($IDs); $i++)
    {
        $review = array('id' => $IDs[$i],
                        'date' => $dates[$i],
                        'book_author' => $bauthors[$i],
                        'book_title' => $btitles[$i],
                        'comment_count' => $comments[$i]);
        $retval[] = $review;
    }
    return $retval;
}

/*
 * SRP_GetCommentsOnPost
 * Returns an associative array of all the comments on the specified post ID.
 */
function SRP_GetCommentsOnPost($postid)
{
    global $wpdb;

    $select  = "SELECT c.comment_ID, c.comment_date_gmt, c.comment_content ";
    $select .= "FROM $wpdb->comments c WHERE c.comment_post_id = %s ORDER BY c.comment_date_gmt";

    $query = $wpdb->prepare($select, $postid);

    $IDs      = $wpdb->get_col($query, 0);
    $dates    = $wpdb->get_col($query, 1);
    $contents = $wpdb->get_col($query, 2);

    $retval = array();
    for ($i = 0; $i < count($IDs); $i++)
    {
        $comment = array('id' => $IDs[$i], 'date' => $dates[$i], 'content' => $contents[$i]);
        $retval[] = $comment;
    }
    return $retval;
}

?>
