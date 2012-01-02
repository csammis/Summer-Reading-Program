<?php
/*
* functions-email.php
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

function SRP_PrintEmailOptions()
{
?>
<div>The Summer Reading Program site uses a Google account in order to send e-mail to prize winners.  You can create a <a href="http://mail.google.com/mail/signup" target="new">free Google account</a> for your library's Summer Reading Program.  It is recommended to <em><strong>not</strong></em> use an existing Google account since the password must be stored.</div>
<div>&nbsp;</div>
<table class="form-table" style="width: auto">
<tr><th scope="row">Gmail account name:</th>
<td>
<input class="text" type="text" size="40" name="gmail_account" value="<?php esc_attr(print_srptheme_option('gmail_account')); ?>" />
</td>
</tr>
<tr><th scope="row">Gmail account password:</th>
<td>
<input class="text" type="password" size="40" name="gmail_password" value="<?php esc_attr(print_srptheme_option('gmail_password')); ?>" />
</td>
</tr>
<tr><th scope="row">Send e-mails on behalf of this address (users will send their replies to this address):</th>
<td>
<input class="text" type="text" size="40" name="gmail_reply_to" value="<?php esc_attr(print_srptheme_option('gmail_reply_to')); ?>" />
</td>
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
