<?php
/*
 * functions-school.php
 * This file should be included from functions.php
 * 
 */

function SRP_PrintSchoolOptions()
{
    //csnote DO NOT START ANY ID AT ZERO.  PHP HAS MAJOR ISSUES WITH ZERO.  SERIOUSLY.
    $gid = is_srptheme_option_set('nextgroupid') ? get_srptheme_option('nextgroupid') : 1;
    $sid = is_srptheme_option_set('nextschoolid') ? get_srptheme_option('nextschoolid') : 1;
?>
<div>Configure the list of schools for Summer Reading Program users to choose from.  These settings allow the administrators to group together school names - by district, grade level, etc - so that the users will be able to easily select their school when an account is registered.</div>
<div>&nbsp;</div>
<div><em>Note:</em> Schools will be displayed in the order they are entered on this page.</div>
<div>&nbsp;</div>
<!-- Set up the form variables for nondecreasing group and school IDs -->
<input type="hidden" id="nextgroupid" name="nextgroupid" value="<?php echo $gid; ?>" />
<input type="hidden" id="nextschoolid" name="nextschoolid" value="<?php echo $sid; ?>" />
<!-- Javascript for school table manipulation -->
<script language="javascript">
function setSchoolRowShow(groupId, schoolId, showValue)
{
    var selector = document.getElementById("srp_school" + schoolId + "group" + groupId + "show");
    if (showValue < 0 || showValue > 2)
    {
        showValue = 2; // both
    }
    selector.value = showValue;
}

function addSchoolRow(groupId, schoolId, schoolName)
{
    if (schoolName === undefined)
    {
        schoolName = "";
    }
    
    if (schoolId === undefined)
    {
        // Get the next school index
        var nextSchoolIdField = document.getElementById('nextschoolid');
        var schoolId = parseInt(nextSchoolIdField.getAttribute('value'));
        nextSchoolIdField.setAttribute('value', schoolId + 1);
    }
    
    var elementName = "srp_school" + schoolId + "group" + groupId;
    var elementShowName = elementName + "show";
    
    var tableID = "srp_schooltable" + groupId;
    var table = document.getElementById(tableID);
    var insertAtIndex = table.rows.length - 1;
    var row = table.insertRow(insertAtIndex); // Last row is the "add new" link
    row.id = "srp_schoolrow" + schoolId;
    
    var nameCell = row.insertCell(0);
    var nameLabel = document.createElement("span");
    nameLabel.innerHTML = "School name:&nbsp;";
    var nameInput = document.createElement("input");
    nameInput.setAttribute('type', "text");
    nameInput.setAttribute('class', "text");
    nameInput.setAttribute('size', 40);
    nameInput.setAttribute('name', elementName);
    nameInput.setAttribute('value', schoolName);
    nameCell.appendChild(nameLabel);
    nameCell.appendChild(nameInput);
    
    var showCell = row.insertCell(1);
    var showLabel = document.createElement("span");
    showLabel.innerHTML = "Show in list:&nbsp;";
    var showInput = document.createElement("select");
    showInput.id = elementShowName;
    showInput.setAttribute('name', elementShowName);
    var showOption0 = document.createElement("option");
    showOption0.setAttribute('value', 0);
    showOption0.innerHTML = "Spring";
    var showOption1 = document.createElement("option");
    showOption1.setAttribute('value', 1);
    showOption1.innerHTML = "Fall";
    var showOption2 = document.createElement("option");
    showOption2.setAttribute('value', 2);
    showOption2.innerHTML = "Both";
    showOption2.setAttribute('selected', true);
    showInput.appendChild(showOption0);
    showInput.appendChild(showOption1);
    showInput.appendChild(showOption2);
    showCell.appendChild(showLabel);
    showCell.appendChild(showInput);
    
    var deleteCell = row.insertCell(2);
    deleteCell.innerHTML = "<a href=\"javascript:deleteSchoolRow(" + groupId + "," + schoolId + ");\">Remove from group</a>";
}

function deleteSchoolRow(groupId, schoolNumber)
{
    var tableID = "srp_schooltable" + groupId;
    var table = document.getElementById(tableID);
    for (var i = 0; i < table.rows.length; i++)
    {
        var row = table.rows[i];
        if (row.id == ("srp_schoolrow" + schoolNumber))
        {
            table.deleteRow(i);
            break;
        }
    }
}

function addGroupRow(groupId, groupName)
{
    if (groupName === undefined)
    {
        groupName = "";
    }
    
    if (groupId === undefined)
    {
        // Get the next group index
        var nextGroupIdField = document.getElementById('nextgroupid');
        groupId = parseInt(nextGroupIdField.getAttribute('value'));
        nextGroupIdField.setAttribute('value', groupId + 1);
    }
    
    var parent = document.getElementById("srp_allgroups");
    
    var genregroup = document.createElement("div");
    genregroup.id = 'srp_grouprow' + groupId;
    genregroup.setAttribute('class', 'srp_genregroup');
    genregroup.setAttribute('style',
                             'border-left:solid 0.2em black; border-bottom:solid 0.1em black; padding-left:0.5em; margin-bottom:0.75em');

    var namegroup = document.createElement("div");
    namegroup.setAttribute('style',
                           'border-bottom:solid 0.1em gray; padding-bottom:0.2em; margin-left:0.1em; margin-right:0.1em;');
    var namelabel = document.createElement("span");
    namelabel.innerHTML = "Group name:&nbsp;";
    var namename = document.createElement("input");
    namename.setAttribute('class', 'text');
    namename.setAttribute('type', 'text');
    namename.setAttribute('size', 40);
    namename.setAttribute('name', 'srp_group' + groupId);
    namename.setAttribute('value', groupName);
    var nameremove = document.createElement('span');
    nameremove.setAttribute('style', 'margin-left:2em; font-size:smaller; vertical-align:middle');
    nameremove.innerHTML = "<a href=\"javascript:deleteGroupRow(" + groupId + ");\">Remove this group</a>";
    namegroup.appendChild(namelabel);
    namegroup.appendChild(namename);
    namegroup.appendChild(nameremove);

    var table = document.createElement("table");
    table.id = "srp_schooltable" + groupId;
    table.setAttribute('class', 'form-table');
    var row = table.insertRow(0);
    var cell = row.insertCell(0);
    cell.setAttribute('colspan', 3);
    cell.innerHTML = "<a href=\"javascript:addSchoolRow(" + groupId + ");\">Add school</a>";
    
    genregroup.appendChild(namegroup);
    genregroup.appendChild(table);
    
    parent.appendChild(genregroup);
}

function deleteGroupRow(groupNumber)
{
    var parent = document.getElementById("srp_allgroups");
    for (var i = 0; i < parent.childNodes.length; i++)
    {
        var div = parent.childNodes[i];
        if (div.id == ("srp_grouprow" + groupNumber))
        {
            parent.removeChild(div);
            break;
        }
    }
}
</script>
<!-- Javascript for loading data (calls are autogenerated) -->
<script language="javascript">
window.onload = function loadExistingGroupsAndSchools()
{
<?php
    $options = get_option('SRPTheme');
    ksort($options);

    // group ID -> group name
    $groups = array();
    // group ID -> school ID -> school name
    $schools = array();
    // group ID -> school ID -> show value
    $schoolshow = array();
    
    $optionkeys = array_keys($options);
    for ($i = 0; $i < count($optionkeys); $i++)
    {
        $key = $optionkeys[$i];
        $pos = strpos($key, 'srp_school');
        if ($pos === false)
        {
            $pos = strpos($key, 'srp_group');
            if ($pos === false)
            {
                continue;
            }
        }
        
        if (strpos($key, 'school') === false)
        {
            // This is a group name
            $groupid = substr($key, strlen('stp_group'));
            $groupname = $options[$key];
            $gid = $groupid + 0;
            $groups[$gid] = $groupname;
        }
        else
        {
            // This is school data
            $matches = array();
            preg_match('/srp_school([0-9]+)group([0-9]+)/', $key, $matches);
            $gid = $matches[2] + 0;
            $sid = $matches[1] + 0;
            if (strpos($key, 'show') === false)
            {
                $schoolname = $options[$key];
                $schools[$gid][$sid] = $schoolname;
            }
            else
            {
                $show_value = $options[$key];
                $schoolshow[$gid][$sid] = $show_value;
            }
        }
    }

    ksort($groups);
    foreach ($groups as $gid => $groupname)
      {
        echo "addGroupRow($gid, '$groupname');\n";

        $gschools = $schools[$gid];
        ksort($gschools);
        foreach ($gschools as $sid => $schoolname)
          {
            echo "addSchoolRow($gid, $sid, '$schoolname');\n";
          }
            
        $gschoolshow = $schoolshow[$gid];
        ksort($gschoolshow);
        foreach ($gschoolshow as $sid => $show_value)
          {
            echo "setSchoolRowShow($gid, $sid, $show_value);\n";
          }
      }
      
?>
}
</script>

<div id="srp_allgroups"></div> <!-- container for DOM-manipulated school entries -->
<div>&nbsp;</div>
<div><a href="javascript:addGroupRow();">Add group</a></div>


<script language="javascript">
    function confirmSubmission()
    {
        return true;
    }
</script>
<?php
}
?>
