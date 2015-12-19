<?php
//if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/** SCCP MANAGER Module for FreePBX 2.5 or later.
 * Copyright 2015 David Burgess, Cynjut Consulting Servmode, LLC
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

global $astman; 

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
$modData = isset($_REQUEST['modData']) ? $_REQUEST['modData'] :  '';

if ($action == "edit") {
    sccp_edit_devmodel($modData);
    sccp_create_osf();
//    needreload();
//    redirect_standard();
}

?>

<div class="content">
<h2>&nbsp;&nbsp;Edit SCCP Device Model Table</h2>

<form name=edit_sccp_devmodel action="<?php  $_SERVER['PHP_SELF'] ?>" METHOD='POST'>
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="modData[model]" value="NEW">

<table>
<tr>
    <th>Add<BR>Del</th>
    <th><a href="#" class="info">Model:&nbsp;<span>The type of phone: 7911, 7940, 7960, etc. Important note: the 'G' models are usually handled the same as the base model (e.g., 7962G is handled as 7962). You can define the 'G' models separate from the base model. Do this if the loadimage for your regular phone is different than your 'G' model.</span></a></th>
    <th><a href="#" class="info">Vendor:&nbsp;<span>The manufacturer of the phone. Typical values are "CISCO", "MOTOROLA", etc.</span></a></th>
    <th><a href="#" class="info">Sidecar configuration:&nbsp;<span>The number of devices that can make up the phone. Sidecars (like the 7914) count as '0' because they need a phone to make the work. If the phone doesn't accept sidecar, the value is '1'. If the device can accept one or two sidecars, the value is '2' or '3'. Just so we're clear, the phone is one, the first sidecar is two, the second sidecar is three.</span></a></th>
    <th><a href="#" class="info">Buttons:&nbsp;<span>The number of line buttons on the device. For a pair of 7914s, the number is 28 (2 * 14 buttons per sidecar).</span></a></th>
    <th><a href="#" class="info">Loadimage:&nbsp;<span>The load image you are using for this model of phone. The defaults are some that I know work and that I use. These should match the Load Image files you have in your TFTP directory.</span></a></th>
    <th><a href="#" class="info">LoadinformationID:&nbsp;<span>OK, this one is wierd. This is the optional model number information value for the "default" entries in the XMLDefault.cnf.xml file in the /tftpboot directory. If you don't know the actual value for the phone you are adding (like a Motorola phone), just fill in a unique value. As far as I know, the system doesn't actually use these for anything, but I included them in case.</span></a></th>
</tr>
<tr>
    <td>NEW</td>
    <td><input type='text' size='14' maxlength='12' name='modData[model_NEW]' value='' ></td>
    <td><input type='text' size='8' maxlength='12' name='modData[vendor_NEW' value='' ></td>
    <td>
	<select name='modData[dns_NEW]'>
        <option value="1" <?php if ($modData['dns_NEW']=="1") echo "selected='selected'" ?> >Phone - no sidecars.</option>
        <option value="2" <?php if ($modData['dns_NEW']=="2") echo "selected='selected'" ?> >Phone - one sidecar.</option>
        <option value="3" <?php if ($modData['dns_NEW']=="3") echo "selected='selected'" ?> >Phone - two sidecars.</option>
        <option value="0" <?php if ($modData['dns_NEW']=="0") echo "selected='selected'" ?> >Sidecar</option>
	</select>
    </td>
    <td><input type='text' size='6' maxlength='2' name='modData[buttons_NEW]' value='1' ></td>
    <td><input type='text' size='20' maxlength='40' name='modData[loadimage_NEW]' value='' ></td>
    <td><input type='text' size='20' maxlength='40' name='modData[loadinformationid_NEW]' value='' ></td>
</tr>

<?php
foreach (sccp_list_devmodel() as $row) {
    $model = $row['model'];
    $del_id = 'del_'.$model;
    $model_id = 'model_'.$model;
    $vendor_id = 'vendor_'.$model;
    $dns_id = 'dns_'.$model;
    $buttons_id = 'buttons_'.$model;
    $loadimage_id = 'loadimage_'.$model;
    $loadinfo_id = 'loadinformationid_'.$model;

    echo "<tr>\n";
    echo "<td><input type='checkbox' name='modData[$del_id]'></td>\n ";
    echo "<td><input type='text' size='14' maxlength='20' name='modData[$model_id]' value='" . $row['model'] . "' ></td>\n";
    echo "<td><input type='text' size='8' maxlength='12' name='modData[$vendor_id]' value='" . $row['vendor'] . "' ></td>\n";
?>

    <td>
	<select name='modData[<?php echo $dns_id ?>]'>
        <option value="1" <?php if ($row['dns']=="1") echo "selected='selected'" ?> >Phone - no sidecars.</option>
        <option value="2" <?php if ($row['dns']=="2") echo "selected='selected'" ?> >Phone - one sidecar.</option>
        <option value="3" <?php if ($row['dns']=="3") echo "selected='selected'" ?> >Phone - two sidecars.</option>
        <option value="0" <?php if ($row['dns']=="0") echo "selected='selected'" ?> >Sidecar</option>
	</select>
    </td>

<?php
    echo "<td><input type='text' size='6' maxlength='2' name='modData[$buttons_id]' value='" . $row['buttons'] . "' ></td>\n";
    echo "<td><input type='text' size='20' maxlength='40' name='modData[$loadimage_id]' value='" . $row['loadimage'] . "' ></td>\n";
    echo "<td><input type='text' size='20' maxlength='40' name='modData[$loadinfo_id]' value='" . $row['loadinformationid'] . "' ></td>\n";
    echo "</tr>\n";

}
?>
    <tr>
	<td>&nbsp;</td>
	<td colspan="6">
 	    <br/><input name="Submit" type="submit" value="Submit Changes"> 
	</td>
    </tr>
</table>
</form>


<?php echo add_free_space(7); ?>
