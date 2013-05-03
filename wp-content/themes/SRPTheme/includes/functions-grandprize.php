<?php
/*
* functions-grandprize.php
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

function SRP_PrintGrandPrizeOptions()
{
    //csnote DO NOT START ANY ID AT ZERO.  PHP HAS MAJOR ISSUES WITH ZERO.  SERIOUSLY.
    $gid = is_srptheme_option_set('nextgprizeid') ? get_srptheme_option('nextgprizeid') : 1;
    $entry = is_srptheme_option_set('srp_gprize_every') ? get_srptheme_option('srp_gprize_every') : 16;
    $numentry = is_srptheme_option_set('srp_gprize_numentries') ? get_srptheme_option('srp_gprize_numentries') : 3;
?>
<div>Grand prizes are awarded by raffle at the end of the summer, and participants can earn entries into this raffle by accumulating reading time. You can specify maximum number of entries per participant. You can enter more than one grand prize for the program; participants will choose which grand prize they'd like to enter for when they register.</div>
<div>&nbsp;</div>
<div>If you do not check any grades for a grand prize, it will be available to all grades.</div>
<div>&nbsp;</div>
<!-- Set up the form variable for nondecreasing grand prize IDs -->
<input type="hidden" id="nextgprizeid" name="nextgprizeid" value="<?php echo $gid; ?>" />
<div>Give users an entry for their grand prize selection every&nbsp;
<input type="text" class="text" size="5" maxlength="2" value="<?php echo $entry;?>"
       name="srp_gprize_every" onKeyPress="return onlyNumbers(event.charCode || event.keyCode);" />&nbsp;hours, up to&nbsp;
<input type="text" class="text" size="5" maxlength="2" value="<?php echo $numentry;?>"
       name="srp_gprize_numentries" onKeyPress="return onlyNumbers(event.charCode || event.keyCode);" />&nbsp;entries.</div>
<div>&nbsp;</div>
<!-- Javascript for grand prize table manipulation -->
<script language="javascript">
function addGrandPrizeRow(grandPrizeId, grandPrizeName, grandPrizeGrades)
{
    if (grandPrizeName === undefined)
    {
        grandPrizeName = "";
    }

    if (grandPrizeId === undefined)
    {
        // Get the next group index
        var nextGprizeIdField = document.getElementById('nextgprizeid');
        grandPrizeId = parseInt(nextGprizeIdField.getAttribute('value'));
        nextGprizeIdField.setAttribute('value', grandPrizeId + 1);
    }

    if (grandPrizeGrades === undefined)
    {
        grandPrizeGrades = "0000000";
    }

    var parent = document.getElementById("srp_allgprizes");

    var gprizegroup = document.createElement("div");
    gprizegroup.id = 'srp_gprizerow' + grandPrizeId;
    gprizegroup.setAttribute('class', 'srp_gprize');
    gprizegroup.setAttribute('style',
         'border-left:solid 0.2em black; padding-left:0.5em; margin-bottom:0.75em');

    var namegroup = document.createElement("div");

    var namelabel = document.createElement("span");
    namelabel.innerHTML = "Grand prize:&nbsp;";
    
    var namename = document.createElement("input");
    namename.setAttribute('class', 'text');
    namename.setAttribute('type', 'text');
    namename.setAttribute('size', 40);
    namename.setAttribute('name', 'srp_gprize' + grandPrizeId);
    namename.setAttribute('value', grandPrizeName);

    var gradegroup = document.createElement("span");
    gradegroup.innerHTML = " for these grades: ";
    for (i = 0; i < 7; i++)
    {
        var check = document.createElement("input");
        check.setAttribute('type', 'checkbox');
        check.setAttribute('name', 'srp_gprize' + grandPrizeId + 'grade[]');
        check.setAttribute('value', i);
        check.setAttribute('style',
                'padding-left:0.2em');
        check.checked = (grandPrizeGrades.charAt(i) == '1');
        gradegroup.appendChild(check);
        var checkdesc = document.createElement("span");
        checkdesc.innerHTML = '&nbsp;' + (i + 6) + '&nbsp;';
        gradegroup.appendChild(checkdesc);
    }

    var nameremove = document.createElement('span');
    nameremove.setAttribute('style', 'margin-left:2em; font-size:smaller; vertical-align:middle');
    nameremove.innerHTML = "<a href=\"javascript:deleteGrandPrizeRow(" + grandPrizeId + ");\">Remove this prize</a>";
    
    namegroup.appendChild(namelabel);
    namegroup.appendChild(namename);
    namegroup.appendChild(gradegroup);
    namegroup.appendChild(nameremove);

    gprizegroup.appendChild(namegroup);
    parent.appendChild(gprizegroup);
}

function deleteGrandPrizeRow(grandPrizeId)
{
    var parent = document.getElementById("srp_allgprizes");
    for (var i = 0; i < parent.childNodes.length; i++)
    {
        var div = parent.childNodes[i];
        if (div.id == ("srp_gprizerow" + grandPrizeId))
        {
            parent.removeChild(div);
            break;
        }
    }
}
</script>
<!-- Javascript for loading data (calls are autogenerated) -->
<script language="javascript">
function loadExistingGrandPrizes()
{
<?php
    $options = get_option('SRPTheme');
    ksort($options);

    $optionkeys = array_keys($options);
    $gprizes = array();
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $key = $optionkeys[$i];
        $pos = strpos($key, 'srp_gprize');
        if ($pos === false || $pos != 0)
        {
            continue;
        }

        // There are a few keys that start with srp_gprize that we don't care about here;
        // they have underscores in their name.  Skip past those keys.
        if (strpos($key, '_', strlen('srp_gprize')) !== false)
        {
            continue;
        }

        $grandPrizeId = substr($key, strlen('srp_gprize'));
        if (!is_numeric($grandPrizeId))
        {
            $grandPrizeId = substr($grandPrizeId, 0, strpos($grandPrizeId, 'grade'));
            $grades = '0000000';
            foreach (explode(',', $options[$key]) as $val)
            {
                $grades[$val] = '1'; // lol php
            }
            $gprizes[$grandPrizeId]['grades'] = $grades;
        }
        else
        {
            $gprizes[$grandPrizeId]['name'] = $options[$key];
        }
    }

    foreach ($gprizes as $id => $prize)
    {
        echo "addGrandPrizeRow($id, '$prize[name]', '$prize[grades]');\n";
    }
?>
}

window.addEventListener('load', loadExistingGrandPrizes, false);
</script>

<div id="srp_allgprizes"></div> <!-- container for DOM-manipulated grand prize entries -->
<div>&nbsp;</div>
<div><a href="javascript:addGrandPrizeRow();">Add grand prize</a></div>
<?php
} // end SRP_PrintGrandPrizeOptions

?>
