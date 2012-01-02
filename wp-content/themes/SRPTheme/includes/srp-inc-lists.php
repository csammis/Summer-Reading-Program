<?php
/*
* srp-inc-lists.php
* Functions which maintain static(ish) lists.

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

//  SRP_GetAllSchoolNames
//  SRP_GetGenreName


/*
 * SRP_GetAllSchoolNames
 * Returns an id => name array of all school names configured by the administrator.
 */
function SRP_GetAllSchoolNames()
{
    $options = get_option('SRPTheme');
    ksort($options);
    $optionkeys = array_keys($options);
    
    $targetkey = 'srp_school';
    
    $sid2name = array();
    // Associate names and IDs to each other
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $key = $optionkeys[$i];
        if (strpos($key, $targetkey) === 0 && strpos($key, 'show') === false)
        {
            $matches = array();
            preg_match('/srp_school([0-9]+)group([0-9]+)/', $key, $matches);
            $gid = $matches[2]; $sid = $matches[1];
            $sid2name[$sid] = $options[$key];
        }
    }
    
    return $sid2name;
}

/*
 * SRP_GetGenreName
 * Return the name of the specified genre.
 */
function SRP_GetGenreName($genreId)
{
    return get_srptheme_option("srp_genre$genreId");
}

?>
