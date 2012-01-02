<?php
/*
* functions-prizes.php
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

function SRP_PrintPrizeOptions()
{
    require_once('srp-inc-template.php');
    SRP_PrintJavascriptNumberValidator();
    
    //csnote DO NOT START ANY ID AT ZERO.  PHP HAS MAJOR ISSUES WITH ZERO.  SERIOUSLY.
    $pid = is_srptheme_option_set('nexthprizeid') ? get_srptheme_option('nexthprizeid') : 1;
?>
<div>Participants can earn prizes for accumulating reading time. Hourly prizes are given automatically after a number of reading hours are accumulated. For example, a participant may earn a prize after each interval of 4 hours of reading. After a prize has been earned, verification codes are emailed to prize winners in order to claim their prizes.</div>
<div>&nbsp;</div>
<!-- Set up the form variables for nondecreasing prize IDs -->
<input type="hidden" id="nexthprizeid" name="nexthprizeid" value="<?php echo $pid; ?>" />
<!-- Javascript for prize table manipulation -->
<script language="javascript">
function addPrizeRow(prizeId, prizeHours, prizeName, prizeCode)
{
    if (prizeName === undefined)
    {
        prizeName = "";
    }
    if (prizeCode === undefined)
    {
        prizeCode = "";
    }
    if (prizeHours === undefined)
    {
        prizeHours = "";
    }
    
    if (prizeId === undefined)
    {
        // Get the next group index
        var nextPrizeIdField = document.getElementById('nexthprizeid');
        prizeId = parseInt(nextPrizeIdField.getAttribute('value'));
        nextPrizeIdField.setAttribute('value', prizeId + 1);
    }
    
    var parent = document.getElementById("srp_allprizes");
    
    var prizegroup = document.createElement("div");
    prizegroup.id = 'srp_prizerow' + prizeId;
    prizegroup.setAttribute('class', 'srp_prize');
    prizegroup.setAttribute('style',
                            'border-left:solid 0.2em black; padding-left:0.5em; margin-bottom:0.75em');

    var hoursprologue = document.createElement("span");
    hoursprologue.innerHTML = "After&nbsp;";
    prizegroup.appendChild(hoursprologue);
    var hoursinput = document.createElement("input");
    hoursinput.setAttribute('class', 'text');
    hoursinput.setAttribute('type', 'text');
    hoursinput.setAttribute('size', 5);
    hoursinput.setAttribute('name', 'srp_hprizehours' + prizeId);
    hoursinput.setAttribute('value', prizeHours);
    hoursinput.addEventListener('keypress', validateNumber, false);
    prizegroup.appendChild(hoursinput);
    var nameprologue = document.createElement("span");
    nameprologue.innerHTML = "&nbsp;hours, award prize&nbsp;";
    prizegroup.appendChild(nameprologue);
    var nameinput = document.createElement("input");
    nameinput.setAttribute('class', 'text');
    nameinput.setAttribute('type', 'text');
    nameinput.setAttribute('size', 40);
    nameinput.setAttribute('name', 'srp_hprizename' + prizeId);
    nameinput.setAttribute('value', prizeName);
    prizegroup.appendChild(nameinput);
    var codeprologue = document.createElement("span");
    codeprologue.innerHTML = "&nbsp;with verification code&nbsp;";
    prizegroup.appendChild(codeprologue);
    var codeinput = document.createElement("input");
    codeinput.setAttribute('class', 'text');
    codeinput.setAttribute('type', 'text');
    codeinput.setAttribute('size', 10);
    codeinput.setAttribute('name', 'srp_hprizecode' + prizeId);
    codeinput.setAttribute('value', prizeCode);
    codeinput.setAttribute('maxlength', 6);
    prizegroup.appendChild(codeinput);
    var removespan = document.createElement("span");
    removespan.setAttribute('style', 'margin-left:2em; font-size:smaller; vertical-align:middle');
    removespan.innerHTML = "&nbsp;&nbsp;(<a href=\"javascript:deletePrizeRow(" + prizeId + ")\">Remove prize</a>)";
    prizegroup.appendChild(removespan);

    parent.appendChild(prizegroup);
}

function deletePrizeRow(prizeId)
{
    var parent = document.getElementById("srp_allprizes");
    for (var i = 0; i < parent.childNodes.length; i++)
    {
        var div = parent.childNodes[i];
        if (div.id == ("srp_prizerow" + prizeId))
        {
            parent.removeChild(div);
            break;
        }
    }
}
</script>
<!-- Javascript for loading data (calls are autogenerated) -->
<script language="javascript">
function loadExistingPrizes()
{
<?php
    $options = get_option('SRPTheme');
    ksort($options);
    
    $optionkeys = array_keys($options);
    $prizes = array();
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $key = $optionkeys[$i];
        $pos = strpos($key, 'srp_hprize');
        if ($pos === false || $pos != 0)
        {
            continue;
        }
        
        $matches = array();
        preg_match('/srp_hprize([a-z]+)([0-9]+)/', $key, $matches);
        $type = $matches[1];
        $id = $matches[2];
        $value = $options[$key];
        $prizes[$id][$type] = $value;
    }
    
    ksort($prizes);
    foreach ($prizes as $id => $values)
    {
        echo "addPrizeRow($id, $values[hours], '$values[name]', '$values[code]');\n";
    }
?>
}

window.addEventListener('load', loadExistingPrizes, false);
</script>

<div id="srp_allprizes"></div> <!-- container for DOM-manipulated genre entries -->
<div><a href="javascript:addPrizeRow();">Add hourly prize</a></div>


<script language="javascript">
    function confirmSubmission()
    {
        return true;
    }
</script>
<?php
}
?>
