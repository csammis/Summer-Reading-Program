<?php
/*
* Template Name: SRP JQuery Processor
* A generic JQuery processor, used internally.

This WordPress plugin was developed for the Olathe Public Library, Olathe, KS
http://www.olathelibrary.org

Copyright (c) 2011, Chris Sammis
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

if (!isset($_POST['action']))
{
    die('No access to this page is allowed.');
}

$retval = 'Unknown function ' . esc_attr(stripslashes($_POST['action']));

switch ($_POST['action'])
{
    case 'g2p':
    {
        require_once('includes/srp-inc-prizes.php');
        $grade = $_POST['grade'];
        $retarray = SRP_GetGrandPrizesForGrade($grade);
        $retval = '';
        foreach ($retarray as $id => $prize)
        {
            $retval .= "$id\n$prize[name]\n";
        }
    }
    break;
}

echo $retval;

?>
