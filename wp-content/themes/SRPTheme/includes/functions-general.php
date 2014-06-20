<?php
/*
* functions-general.php
* This file should be included from functions.php

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

function SRP_PrintGeneralOptions()
{
    $program_active = get_srptheme_option('program_active');
    $max_length = get_srptheme_option('max_length');
    if (strlen($max_length) == 0)
    {
        $max_length = 500;
    }
    
    $jscolor_url = get_bloginfo('template_directory') . '/jscolor/jscolor.js';
?>
<div>Customize the Summer Reading Program theme with your library's name and choice of colors and images.  You may also set up a Google Anayltics tracking number to count visitors to the program's site.</div>
<div>&nbsp;</div>

<script type="text/javascript" src="<?php echo $jscolor_url; ?>"></script>
<table class="form-table" style="width: auto">
<tr><th scope="row">Library name:</th>
<td><input type="text" name="library_name" id="library_name" value="<?php echo get_srptheme_option('library_name'); ?>" size="40" /></td>
</tr>
<tr><th scope="row">URL of header image:</th>
<td><input type="text" name="srp_headerimg" id="srp_headerimg" 
           value="<?php echo get_srptheme_appearance('header'); ?>" size="80" /></td>
</tr>
<tr><th scope="row">Header image height in pixels:</th>
<td><input type="text" name="srp_headerimgheight" id="srp_headerimgheight" 
           value="<?php echo get_srptheme_option('srp_headerimgheight'); ?>" size="5" />px tall x 778px wide</td>
</tr>
<tr><th scope="row">URL of mobile header image:</th>
<td><input type="text" name="srp_mobileheaderimg" id="srp_mobileheaderimg"
           value="<?php echo get_srptheme_appearance('mobileheader'); ?>" size="80" /> - 165px tall x 325px wide</td>
</tr>
<tr><th scope="row">URL of footer image:</th>
<td><input type="text" name="srp_footerimg" id="srp_footerimg" 
           value="<?php echo get_srptheme_appearance('footer'); ?>" size="80" /></td>
</tr>
<tr><th scope="row">Footer image height in pixels:</th>
<td><input type="text" name="srp_footerimgheight" id="srp_footerimgheight" 
           value="<?php echo get_srptheme_option('srp_footerimgheight'); ?>" size="5" />px tall x 778px wide</td>
</tr>
<tr><th scope="row">Header / Footer color:</th>
<td><input class="color" name="srp_backcolor1" id="srp_backcolor1" value="<?php echo get_srptheme_appearance('backcolor1'); ?>" /></td>
</tr>
<tr><th scope="row">Side color:</th>
<td><input class="color" name="srp_backcolor2" id="srp_backcolor2" value="<?php echo get_srptheme_appearance('backcolor2'); ?>" /></td>
</tr>
<tr><th scope="row">Body color:</th>
<td><input class="color" name="srp_backcolor3" id="srp_backcolor3" value="<?php echo get_srptheme_appearance('backcolor3'); ?>" /></td>
</tr>
<tr><th scope="row">Limit review length to this many characters:</th>
<td><input type="textx" name="max_length" id="max_length" value="<?php echo $max_length; ?>" /></td>
</tr>

</table>
<div>&nbsp;</div>
<div>If you want to track visitors to your site, register an account with <a href="http://www.google.com/analytics/" target="new">Google Analytics</a> and enter the tracking ID here.  Visits to each SRP page will be tracked separately.</div>
<div><input type="text" name="ga_id" id="ga_id" value="<?php echo get_srptheme_option('ga_id'); ?>" size="40" /></div>
<div>&nbsp;</div>
<div>If you want to include a form for users to send questions to the SRP email account on the Contact page, create and enter your <a href="https://www.google.com/recaptcha/admin/create" target="new">Google ReCAPTCHA</a> keys. The Summer Reading Program requires the use of a ReCAPTCHA system to submit questions in order to reduce spam.</div>
<div>Public key: <input type="text" name="rc_pub_key" id="rc_pub_key" value="<?php echo get_srptheme_option('recaptcha_public'); ?>" size="40" /></div>
<div>Private key: <input type="text" name="rc_priv_key" id="rc_priv_key" value="<?php echo get_srptheme_option('recaptcha_private'); ?>" size="40" /></div>
<div>&nbsp;</div>
<hr />
<div>&nbsp;</div>
<div>The following settings control the contents of the Summer Reading Program site.  <strong>Please read each instruction carefully before saving changes.</strong></div>
<div>&nbsp;</div>
<div>The Summer Reading Program may be <em>open</em> (users may register accounts, log in, and submit time read and reviews) or <em>closed</em> (users may browse reviews but not create any new content).  Administrators may log in at any time.  The program is initially closed.</div>
  <div>&nbsp;</div><div><strong>Please note:</strong> the Program calculates how many hours are allowed to be logged per user based on how long the program has been open. Closing and opening the program midsummer will reset this counter, creating serious usability issues.  It is not recommended that the program be closed except at the end of the summer.</div>
<div>&nbsp;</div>
<div>The Program was last opened on <?php echo date('F j, Y, g:i a', get_srptheme_option('program_open_date')); ?></div>
<div>&nbsp;</div>
<div>The Summer Reading Program is &nbsp;
<input type="radio" name="program_active" id="program_active" value="1" <?php if ($program_active == 1) echo 'checked'; ?> /> open
    for the year 20<input type="text" name="srp_program_year" id="srp_program_year" size="4" /> &nbsp;
<input type="radio" name="program_active" id="program_active" value="0" <?php if ($program_active == 0) echo 'checked'; ?> /> closed
</div>

<div>&nbsp;</div>
<div>The Summer Reading Program theme expects a certain set of pages with specific attributes.  Check this box in order to create that set of pages.  Doing this more than once will cause no problems.</div>
<div>&nbsp;</div>
<div><input type="checkbox" name="srp_oneclicksetup" id="srp_oneclicksetup" /> Create base set of pages?</div>


<script language="javascript">
    function confirmSubmission()
    {
<?php if ($program_active == 0) { ?>
        var resetField = document.getElementById('program_active');
        if (resetField.value == 1)
        {
            var program_year = document.getElementById('srp_program_year');
            if (program_year.value.length == 0)
            {
                alert('To open the SRP site, you must enter the current year');
                return false;
            }
        }
<?php } ?>
        
        return true;
    }
</script>
<?php
}
?>
