<?php
/*
* functions-messages.php
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

function SRP_PrintMessagesOptions()
{
    require_once('srp.class.messages.php');
    $SrpMessage = new SRPMessages;
    if (!$SrpMessage->dbSelect())
    {
        die('The SRP messages object could not be retrieved from the database (loc = 8FAMGK)');
    }
?>
<div>
The Summer Reading Program site will send e-mails and display messages that may be specific to your library.  You can customize those messages here.  Messages may include special tags (indicated by <strong>%%<em>tagname</em>%%</strong>) which will be replaced with information when the message is displayed or sent.
<br /><br />
All messages can use the following tags (and any special tags listed beside the message):<br />
<ul>
<li><strong>%%libraryname%%</strong> - the setting "Library Name" found under the General settings.</li>
</ul>
</div>

<div>&nbsp;</div>
<table class="form-table" style="width: auto">
<tr><th scope="row"><p>E-mail sent to users who win an hourly prize:</p></th>
<td><textarea rows="7" cols="60" name="srp_hourlyemail"><?php echo $SrpMessage->getHourlyEmail(); ?></textarea></td>
<td>Special tags:<ul><li>%%prizename%%</li><li>%%prizecode%%</li></ul></td>
</tr>
<tr><th scope="row"><p>Front page notice to users who have won a prize:</p></th>
<td><textarea rows="7" cols="60" name="srp_hourlynotice"><?php echo $SrpMessage->getHourlyPrizeNotice(); ?></textarea></td>
<td>Special tags: none</td>
</tr>
<tr><th scope="row"><p>E-mail sent to users who win a review prize drawing:</p></th>
<td><textarea rows="7" cols="60" name="srp_weeklyemail"><?php echo $SrpMessage->getWeeklyEmail(); ?></textarea></td>
<td>Special tags: none</td>
</tr>
<tr><th scope="row"><p>User registration agreement:</p></th>
<td><textarea rows="7" cols="60" name="srp_regagreement"><?php echo $SrpMessage->getRegistrationAgreement(); ?></textarea></td>
<td>Special tags: none</td>
</tr>
<tr><th scope="row"><p>Footer area:</p></th>
<td><textarea rows="7" cols="60" name="srp_footertext"><?php echo $SrpMessage->getFooterText(); ?></textarea></td>
<td>HTML is allowed, &lt;script&gt; and &lt;iframe&gt; are stripped.</td>
</tr>
</table>


<script language="javascript">
    function confirmSubmission()
    {
        return true;
    }
</script>
<?php
}
?>
