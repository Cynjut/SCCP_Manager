<?php
//if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/** SCCP MANAGER Module for FreePBX 2.5 or later.
 * Copyright 2015 David Burgess, Cynjut Consulting Services, LLC
 * Copyright 2012 Javier de la Fuente, GT-TOIP CSIC
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

//global $astman; 

$version = sccp_get_asterisk_version();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] :  'softkeyset';
$keysetData = isset($_REQUEST['keysetData']) ? $_REQUEST['keysetData'] :  '';
$keysetData['file_context'] = $name;

?>

<div class="rnav">

<ul>
    <li><a href="config.php?display=sccp_keyset&amp;name=setup">Add New Keyset</a></li>
    <li><a href="config.php?display=sccp_keyset&amp;name=default">default keyset</a></li>

<?php
    $keysets = sccp_list_keysets();
    foreach ($keysets as $row => $value) {
        echo "<li><a href=config.php?display=sccp_keyset&amp;name=" . "$row" . ">" . "$row" . "</a></li>";
    }
?>
</ul>
</div>

<div class="content">
<?php
if ($action == 'edit') {
    echo "<h2>&nbsp;&nbsp;Edit SCCP Soft Keyset</h2>";
    $keysetData = sccp_edit_keyset($keysetData);

//    needreload();
//    redirect_standard();
} else {
    echo "<h2>&nbsp;&nbsp;View SCCP Soft Keyset</h2>";
}

$confData = sccp_get_confData('server');
$keysetData = sccp_get_keysetdata($name);
$name = strtolower($name);
$keysetData['name'] = strtolower($keysetData['name']);
if ($keysetData['name'] == 'default') {
    $keysetData['name'] = $name;
}

?>

<form name="edit_sccp_keyset" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post">
    <input type="hidden" name="action" value="edit" >
    <input type="hidden" name="type" value="softkeyset" >
<table>
    <tr><td colspan="5"><h5><br><br><br>Keyset Configuration<hr></h5></td></tr>
    <tr>
        <td>&nbsp;&nbsp;</td>
        <td><a href="#" class="info">Keyset Name:<span>This is the name of the keyset. The 'default' keyset is read-only, so you will not be able to update the default keyset.</span></a></td>
        <td><input size="12" type="text" name="keysetData[name]" id='name' value="<?php echo $keysetData['name'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
	<td></td>
	<td><h5><a href="#" class="info">Call Phase<span>This is the name of the phase of the call. Different phases have different soft button configurations.</span></a><hr></h5></td>
	<td colspan=3><h5><a href="#" class="info">Options<span>These are the options for each phase. Select as many or as few as you want. For example, if you've globally disabled Call Forward, setting soft buttons that interact with that could be confusing.</span></a><hr></h5></td>
    <tr>
	<td></td>
        <td><a href="#" class="info">Onhook:<span>Displayed when we are on hook the name of the keyset in question.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'onhook','redial'); 
	    echo sccp_display_keyset($keysetData,'onhook','newcall'); 
	    echo sccp_display_keyset($keysetData,'onhook','cfwdall'); 
	    echo sccp_display_keyset($keysetData,'onhook','dnd'); 
	    echo sccp_display_keyset($keysetData,'onhook','pickup'); 
	    echo sccp_display_keyset($keysetData,'onhook','gpickup'); 
	    echo sccp_display_keyset($keysetData,'onhook','private'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Connected:<span>Displayed when we have a connected call.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'connected','hold'); 
	    echo sccp_display_keyset($keysetData,'connected','endcall'); 
	    echo sccp_display_keyset($keysetData,'connected','park'); 
	    echo sccp_display_keyset($keysetData,'connected','select'); 
	    echo sccp_display_keyset($keysetData,'connected','cfwdall'); 
	    echo sccp_display_keyset($keysetData,'connected','cfwdbusy'); 
	    echo sccp_display_keyset($keysetData,'connected','idivert'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">On Hold:<span>Displayed when we have a  call on hold.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'onhold','resume'); 
	    echo sccp_display_keyset($keysetData,'onhold','newcall'); 
	    echo sccp_display_keyset($keysetData,'onhold','endcall'); 
	    echo sccp_display_keyset($keysetData,'onhold','transfer'); 
	    echo sccp_display_keyset($keysetData,'onhold','confrn'); 
	    echo sccp_display_keyset($keysetData,'onhold','select'); 
	    echo sccp_display_keyset($keysetData,'onhold','dirtrfr'); 
	    echo sccp_display_keyset($keysetData,'onhold','idivert'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Ringin:<span>Displayed when we have an incoming call.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'ringin','answer'); 
	    echo sccp_display_keyset($keysetData,'ringin','endcall'); 
	    echo sccp_display_keyset($keysetData,'ringin','transvm'); 
	    echo sccp_display_keyset($keysetData,'ringin','idivert'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Offhook:<span>Displayed when the phone is taken off hook.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'offhook','redial'); 
	    echo sccp_display_keyset($keysetData,'offhook','endcall'); 
	    echo sccp_display_keyset($keysetData,'offhook','private'); 
	    echo sccp_display_keyset($keysetData,'offhook','cfwdall'); 
	    echo sccp_display_keyset($keysetData,'offhook','cfwdbusy'); 
	    echo sccp_display_keyset($keysetData,'offhook','pickup'); 
	    echo sccp_display_keyset($keysetData,'offhook','gpickup'); 
	    echo sccp_display_keyset($keysetData,'offhook','meetme'); 
	    echo sccp_display_keyset($keysetData,'offhook','barge'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Conntrans:<span>Displayed when we are connected and could transfer a call.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'conntrans','hold'); 
	    echo sccp_display_keyset($keysetData,'conntrans','endcall'); 
	    echo sccp_display_keyset($keysetData,'conntrans','transfer'); 
	    echo sccp_display_keyset($keysetData,'conntrans','conf'); 
	    echo sccp_display_keyset($keysetData,'conntrans','park'); 
	    echo sccp_display_keyset($keysetData,'conntrans','select'); 
	    echo sccp_display_keyset($keysetData,'conntrans','dirtrfr'); 
	    echo sccp_display_keyset($keysetData,'conntrans','meetme'); 
	    echo sccp_display_keyset($keysetData,'conntrans','cfwdall'); 
	    echo sccp_display_keyset($keysetData,'conntrans','cfwdbusy'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Digitsfoll:<span>Displayed when one or more digits have been entered, more are expected.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'digitsfoll','back'); 
	    echo sccp_display_keyset($keysetData,'digitsfoll','endcall'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info"> Connconf:<span>Displayed when we are in a conference.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'connconf','conflist'); 
	    echo sccp_display_keyset($keysetData,'connconf','endcall'); 
	    echo sccp_display_keyset($keysetData,'connconf','join'); 
	    echo sccp_display_keyset($keysetData,'connconf','hold'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Ringout:<span>Displayed when we are calling someone.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'ringout','endcall'); 
	    echo sccp_display_keyset($keysetData,'ringout','transfer'); 
	    echo sccp_display_keyset($keysetData,'ringout','cfwdall'); 
	    echo sccp_display_keyset($keysetData,'ringout','idivert'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Offhookfeat:<span>Displayed wenn we went offhook using a feature.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'offhookfeat','redial'); 
	    echo sccp_display_keyset($keysetData,'offhookfeat','endcall'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Onhint:<span>Displayed when a hint is activated.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'onhint','redial'); 
	    echo sccp_display_keyset($keysetData,'onhint','newcall'); 
	    echo sccp_display_keyset($keysetData,'onhint','pickup'); 
	    echo sccp_display_keyset($keysetData,'onhint','gpickup'); 
	    echo sccp_display_keyset($keysetData,'onhint','barge'); 
	?>
        </td>
    </tr>
    <tr>
	<td></td>
        <td><a href="#" class="info">Onstealable:<span>Displayed when there is a call we could steal on one of the neighboring phones.</span></a></td>
	<td>
	<?php 
	    echo sccp_display_keyset($keysetData,'onstealable','redial'); 
	    echo sccp_display_keyset($keysetData,'onstealable','newcall'); 
	    echo sccp_display_keyset($keysetData,'onstealable','cfwdall'); 
	    echo sccp_display_keyset($keysetData,'onstealable','pickup'); 
	    echo sccp_display_keyset($keysetData,'onstealable','gpickup'); 
	    echo sccp_display_keyset($keysetData,'onstealable','dnd'); 
	    echo sccp_display_keyset($keysetData,'onstealable','intrcpt'); 
	?>
        </td>
    </tr>
<?php
    if ($keysetData['name'] == 'default') {
	echo "<!--";
    }
?>
    <tr>
	<td></td>
        <td>&nbsp;</td>
        <td colspan="4"><br /><input name="Submit" type="submit" value="Submit Changes"> 
        </td>
    </tr>
<?php
    if ($keysetData['name'] == 'default') {
	echo "-->";
    }
?>


</table>
</form>

<?php echo add_free_space(7); ?>
</div>


