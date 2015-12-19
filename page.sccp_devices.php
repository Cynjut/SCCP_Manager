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

$version = sccp_get_asterisk_version();

$sccpConf = sccp_get_confData('client');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
$RestartPhone = isset($_REQUEST['RestartPhone']) ? $_REQUEST['RestartPhone'] :  '';

if (isset($_REQUEST['del'])) {
    $action = 'del';
}

$devdisplay = isset($_REQUEST['devdisplay']) ? $_REQUEST['devdisplay'] :  '';
$mac = isset($_REQUEST['mac']) ? $_REQUEST['mac'] :  '';

$devData = isset($_REQUEST['devData']) ? $_REQUEST['devData'] :  '';
$buttonData = isset($_REQUEST['buttonData']) ? $_REQUEST['buttonData'] :  '';

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
    $dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}


if ($RestartPhone){
    sccp_reset_phone($devdisplay);	
}

global $astman; 

switch ($action) {
    case 'add':
	if ( $_REQUEST['Submit'] ) {
	    sccp_add_device($devData, $buttonData);
	    //needreload();
	    redirect_standard();
	}
    break;
    case 'edit':
	    sccp_edit_device($devData, $buttonData);
	    $astman->send_request("Command", array("Command" => "sccp restart ".$devdisplay));
	    //needreload();
	    redirect_standard('devdisplay');
    break;
    case 'del':
	    sccp_delete_device($devdisplay);
	    //needreload();
	    redirect_standard();
    break;
}

?>

<div class="rnav"><ul>
<?php

    echo '<li><a href="config.php?display=sccp_devices">'._('Add Phone').'</a></li>';

    foreach (sccp_list_devices() as $row) {
	$l_device = $row['device'];
	$l_type = $row['type'];
	$l_ext = $row['ext'];
        echo "<li><a href='config.php?display=sccp_devices&amp;devdisplay=$l_device&amp;type=$l_type'> $l_device ($l_type) - $l_ext</a></li>";
    }
    echo '<li>&nbsp;</li>';
    $row_temp = sccp_list_devices_wo_extension();
    if ( count($row_temp) > 0 ) {
        echo '<li>'._('Devices without extension associated').'</li>';
        foreach (sccp_list_devices_wo_extension() as $row) {
	    $l_name = $row['name'];
	    $l_type = $row['type'];
	    $l_ext = $row['ext'];
            echo "<li><a href='config.php?display=sccp_devices&amp;devdisplay=$l_name&amp;type=$l_type'> $l_name ($l_type)</a></li>";
        }
    }

?>
</ul></div>

<div class="content">

<?php

if ($devdisplay) {
    $row = sccp_get_device($devdisplay);
    $devData = sccp_get_device_full($devdisplay);
    $type = isset($devData['type']) ? $devData['type'] : $type;
    $rowButtons = get_buttons_devtype($type);
    $Lines = $rowButtons['buttons'];
    $noSidecar = $rowButtons['dns'];
    if (strlen($devData['addon']) > 0) {
        $rowButtons = get_buttons_devtype($devData['addon']);
        $Lines += $rowButtons['buttons'];
    }
    $mac = $row['mac'];

    echo "<h2>&nbsp;&nbsp;"._("Edit: ")."SEP$mac (".$devData['type'].")"."</h2>";
    $delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=del';
    $tlabel_del = sprintf(_("Delete Phone %s"),$devdisplay);
    $label_del = '<span>&nbsp;&nbsp;<img width="16" height="16" border="0" title="'.$tlabel_del.'" alt="" src="images/user_delete.png"/>&nbsp;<a href="'.$delURL.'">'.$tlabel_del.'</a></span>';
    echo $label_del;

} else {
    echo "<h2>&nbsp;&nbsp;"._("Add Phone")."</h2>";
    $Lines = 1;
}

?>

<form name="edit_sccp_devices" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return check_sccp_device(edit_sccp_devices);">
    <input type="hidden" name="devdisplay" value="<?php echo $devdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($devdisplay ? 'edit' : 'add'); ?>" >
<table>
    <tr><td colspan="5"><h5><?php  echo ($devdisplay ? _("Edit Phone") : _("Add Phone")) ?><hr></h5></td></tr>

    <tr>
    <td>&nbsp;&nbsp;&nbsp;</td>
    <td><a href="#" class="info"><?php echo _("MAC")?>:<span><?php echo _("The MAC address of the phone")?></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.</td>
    <td><input size="12" type="text" <?php if ($devData['name']) echo 'readonly="readonly"'?> name="devData[name]" id='mac_address' value="<?php  echo substr($devData['name'], -12); ?>" maxlength="12" onchange="mayusculas()" ></td>
    </tr>

<?php
    $modelData = sccp_get_model_data();
    $numModels = count($modelData['model']);
    $addonData = sccp_get_addon_data();
    $numAddons = count($addonData['model']);

?>

    <tr>
	<td></td>
    	<td><a href="#" class="info">Type:&nbsp;<span><?php echo _("The type of phone: 7911, 7940, 7960, etc. Important note: the 'G' models are handled as the base model (e.g., 7962G is handled as 7962). In the Display mode, this field is read-only because the MAC address and the model number are a pair.")?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></a></td>
	<td>
	<select name='devData[type]' id='type'  onchange="this.form.submit()" >
       	<?php
	    echo "<option value=''></option>";
	    foreach ($modelData['model'] AS $model) {
	    	if ($devData['type'] == $model) {
		    echo "<option value='$model' selected='selected'>$model </option>";
		    $valToHidden = $model;
	  	} else { 
		    echo "<option value='$model'>$model</option>";
	    	}
	    }
	?>  
	 </select>
        </td>
    </tr>

<?php 
    if ($noSidecar < 2) {
	echo "<tr><td></td>";
	echo "<td><a href='#' class='info'>Addon:<span>Addons are model specific and only work with certain base phones. This phone model is identified as being a phone that does not accept sidecars. Update devmodel if this is not correct.</span></a></td>";
	echo "<td><input type=text name=ignore value='N/A' readonly='readonly'></td></tr>";
	echo "<!--";
    }
?>
    <tr>
	<td></td>
	<td><a href="#" class="info"><?php echo _("Addon")?>:<span><?php echo _("Addons are model specific and only work with certain base phones. There are no checks for this here. Use the '79xx,79xx' syntax if you have two sidecars.")?></span></a></td>
        <td>
	    <select name='devData[addon]' id='phone_addon' onchange="this.form.submit()" >
	    <?php
		echo "<option value=''></option>";
		for ($i=0; $i < $numAddons; $i++){
	    	    if ($devData['addon'] == $addonData['model'][$i]) {
			echo "<option value='{$addonData['model'][$i]}' selected='selected'>{$addonData['model'][$i]} </option>";
		    } else {
			echo "<option value='{$addonData['model'][$i]}'>{$addonData['model'][$i]} </option>";
	    	    }
		}
	    ?>  
	    </select>
	</td>
    </tr>

<?php 
    if ($noSidecar < 2) {
	echo "-->";
    }
?>

    <tr> 
	<td></td>
	<td><a href="#" class="info">Description:<span><?php echo _("Phone description. This text is shown in the upper right corner, close to date")?></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	 <td>
	<input type='text' size='20' maxlength='20' name='devData[description]' value="<?php  echo $devData['description']?>" ></td> 
    </tr>


    <tr><td colspan="5"><h5><?php  echo "<br>"; echo (_("Associated Extension & Speeddials")); ?><hr></h5></td></tr>
    <tr>
	<td></td>
	<td></td>
	<td></td>
	<td><font color='grey' size=-1><a href="#" class="info"><?php echo _("Prompt")?>:<span><?php echo _("This is the prompt your phone button will show. For 'empty', it should be blank. For 'line', it will be the line number. For everything else, it is a description of the button. Some phones have limited space for prompts, so be brief. <bR>A special note about shared lines - you can use the same line number on as many phones as you'd like without any special syntax since version 3.0 of the SCCP-Chan-B driver. If you are using a shared line, you can specify 'silent' in the options field and that phone will not ring on that line.")?></span></a></font></td>
	<td><font color='grey' size=-1><a href="#" class="info"><?php echo _("Options")?>:<span><?php echo _("Options for your line type. For 'empty', it will be blank. For 'line', it will usually be blank or 'silent'. For 'speeddial', it will be the string to dial and an optional comma separated 'hint' for monitoring that number. For 'service', it will be a Cisco XML compatible service URL. Note that not all services make sense on your phone.  For 'feature', it will be one of the feature sets implemented. Note that you can specify one of the 'not implemented yet' features and the phone will simply not use that feature, since it isn't implemented yet.")?></span></a></font></td>
    </tr>
<?php
    for ($Instance = 0; $Instance < $Lines; $Instance++){   
	$tybuData = get_properties_in_button($devdisplay,($Instance+1));
?>

    <tr>
	<td></td>
	<td>
	<?php 
	    if ($Instance == 0) {
		echo "<a href='#' class='info'>";
	    }
	    echo "Button ". ($Instance+1) . ":";
	    if ($Instance == 0) {
		echo "<span>\"Assigned Values to the Button\"</span></a>";
	    }
	?>
	</td>
	<td>
 	  <select name="<?php print "buttonData[type{$Instance}]" ?>">
	    <option value="empty" <?php if ($tybuData['type']=="empty") echo "selected='selected'" ?> >Empty</option>
	    <option value="line" <?php if ($tybuData['type']=="line") echo "selected='selected'" ?> >Line</option>
	    <option value="service" <?php if ( ($tybuData['type']=="service")   ) echo "selected='selected'" ?> >Service</option>
	    <option value="feature" <?php if ( ($tybuData['type']=="feature")   ) echo "selected='selected'" ?> >Feature</option>
	    <option value="speeddial" <?php if ($tybuData['type']=="speeddial") echo "selected='selected'" ?> >SpeedDial</option>
	  </select>
	</td> 
	<td><input size="20" type="text" name="<?php print "buttonData[name{$Instance}]" ?>" value="<?php print $tybuData['name']; ?>" /></td> 
	<td>
	<?php
	if ($tybuData['type']=="feature") {
	?>
 	    <select name="<?php print "buttonData[options{$Instance}]" ?>">
	        <option value="privacy,callpresent" <?php if ($tybuData['options']=="privacy,callpresent") echo "selected='selected'" ?> >Call - Number Supporessed</option>
	        <option value="privacy,hint" <?php if ($tybuData['options']=="privacy,hint") echo "selected='selected'" ?> >Private Call - Hint Suppressed</option>
	        <option value="cfwdall,$number" <?php if ($tybuData['options']=="cfwdall,number") echo "selected='selected'" ?> disabled>Call Forward All</option>
	        <option value="cfwbusy,$number" <?php if ($tybuData['options']=="cfwbusy,number") echo "selected='selected'" ?> disabled>Call Forward On Busy</option>
	        <option value="cfwnoaswer,$number" <?php if ($tybuData['options']=="cfwnoaswer,number") echo "selected='selected'" ?> disabled>Call Forward On No Answer</option>
	        <option value="DND,busy" <?php if ($tybuData['options']=="DND,busy") echo "selected='selected'" ?> >No Not Disturb - Busy Status</option>
	        <option value="DND,silent" <?php if ($tybuData['options']=="DND,silent") echo "selected='selected'" ?> >No Not Disturb - No Status</option>
		<?php
		    if (substr($version,1,3) == '1.6') {
	        	echo "<option value='monitor' ";
			if ($tybuData['options']=="monitor") {
			    echo "selected='selected'";
			} 
			echo "Record Calls using AutoMon (Ast 1.6 only)</option>";
		    } 
		?>
	        <option value="devstate,custom_devstate" <?php if ($tybuData['options']=="devstate,custom_devstate") echo "selected='selected'" ?> >Device State Feature</option>
	        <option value="hold" <?php if ($tybuData['options']=="hold") echo "selected='selected'" ?> disabled >hold</option>
	        <option value="transfer" <?php if ($tybuData['options']=="transfer") echo "selected='selected'" ?>  disabled>transfer</option>
	        <option value="multiblink" <?php if ($tybuData['options']=="multiblink") echo "selected='selected'" ?>  disabled>multiblink</option>
	        <option value="mobility" <?php if ($tybuData['options']=="mobility") echo "selected='selected'" ?>  disabled>mobility</option>
	        <option value="parkedcalls" <?php if ($tybuData['options']=="parkedcalls") echo "selected='selected'" ?>  disabled>parkedcalls</option>
	        <option value="conference" <?php if ($tybuData['options']=="conference") echo "selected='selected'" ?>  disabled>conference</option>
	  </select>
	<?php
	} else if ($tybuData['type'] == "line") {
	    if ($tybuData['options'] == 'Default') {
		$checked = 'checked';
	    } else {
		$checked = '';
	    }
	    echo "<input type='radio' name='buttonData[default_line]' value='{$tybuData['name']}' $checked> Default";
	} else {
	    echo "<input size='60' type='text' name='buttonData[options". $Instance . "]' value='" . $tybuData['options']. "'/> ";
	}
?>
	</td>
     </tr>
<?php
}
?>
    <tr> 
	<td></td>
	<td><a href="#" class="info">Softkeyset:<span>Select the softkeyset from the list. You can manage your keysets from the SCCP keysets option. The default is 'softkeyset'.</span></a></td>
	<td>
	<select name='devData[softkeyset]' id='softkeyset' >
       	<?php
    	    $keyset = sccp_list_keysets();
	    echo "<option value='default'> default</option>";
    	    foreach ($keyset AS $check) {
	    	if ($devData['softkeyset'] == $check) {
		    echo "<option value='$check' selected='selected'> $check</option>";
	  	} else { 
		    echo "<option value='$check'> $check</option>";
	    	}
	    }
	?>  
	</td>
    </tr>
    <tr><td colspan="5"><h5><br>Device Properties<hr></h5></td></tr>

    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("Codec Disallow")?>:<span><?php echo _("Certain codecs are not allowed on these phones. The list of allowed ones is so short, 'all' makes sense.")?></span></a></td>
	<td><input size="6" type="text" name="devData[disallow]" id="devData[disallow]" value="<?php  echo $devData['disallow']?>" /></td> 
    </tr>

    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("Codec Allow")?>:<span><?php echo _("Certain codecs are allowed on these phones. The list of allowed ones is so short, 'ulaw' and 'alaw' are about it.")?></span></a></td>
	<td><input size="6" type="text" name="devData[allow]" id="devData[allow]" value="<?php  echo $devData['allow']?>" /></td> 
    </tr>

    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("Transfer")?>:<span><?php echo _("Transfer allowed")?></span></a></td>
	 <td>
	<select name="devData[transfer]" id="devData[transfer]">
	    <option value="on" <?php if ($devData['transfer']=="on") echo "selected='selected'" ?> >On</option>
	    <option value="off" <?php if ($devData['transfer']=="off") echo "selected='selected'" ?> >Off</option>
	  </select>
    </tr>

    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("cfwdall")?>:<span><?php echo _("Activate the callforward softkeys. Default is On")?></span></a></td>
	 <td>
	<select name="devData[cfwdall]" id="devData[cfwdall]">
	    <option value="on" <?php if ($devData['cfwdall']=="on") echo "selected='selected'" ?> >On</option>
	    <option value="off" <?php if ($devData['cfwdall']=="off") echo "selected='selected'" ?> >Off</option>
	  </select>
    </tr>

	
    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("cfwdbusy")?>:<span><?php echo _("Activate the callforward busy softkeys. Default is On")?></span></a></td>
	 <td>
	<select name="devData[cfwdbusy]" id="devData[cfwdbusy]">
	    <option value="on" <?php if ($devData['cfwdbusy']=="on") echo "selected='selected'" ?> >On</option>
	    <option value="off" <?php if ($devData['cfwdbusy']=="off") echo "selected='selected'" ?> >Off</option>
	  </select>
    </tr>

    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("DTMFmode")?>:<span><?php echo _("Dual-Tone Multi-Frequency: outofband is the native cisco dtmf tone play")?></span></a></td>
	 <td>
	<select name="devData[dtmfmode]" id="devData[dtmfmode]">
	    <option value="outofband" <?php if ($devData['dtmfmode']=="outofband") echo "selected='selected'" ?> >outofband</option>
	    <option value="inband" <?php if ($devData['dtmfmode']=="inband") echo "selected='selected'" ?> >inband</option>
	  </select>
    </tr>


    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("DND")?>:<span><?php echo _("Do Not Disturb. Default is Off")?></span></a></td>
	 <td>
	<select name="devData[dndFeature]" id="devData[dndFeature]">
	    <option value="on" <?php if ($devData['dndFeature']=="user") echo "selected='selected'" ?> >User</option>
	    <option value="silent" <?php if ($devData['dndFeature']=="ilent") echo "selected='selected'" ?> >Silent</option>
	    <option value="reject" <?php if ($devData['dndFeature']=="reject") echo "selected='selected'" ?> >Reject</option>
	    <option value="off" <?php if ($devData['dndFeature']=="off") echo "selected='selected'" ?> >Off</option>
	  </select>
    </tr>

    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("mwilamp")?>:<span><?php echo _("Define the MWI Lamp stype - on. off. flash. blink, or wink. Default is On")?></span></a></td>
	 <td>
	<select name="devData[mwilamp]" id="devData[mwilamp]">
	    <option value="on" <?php if ($devData['mwilamp']=="on") echo "selected='selected'" ?> >On</option>
	    <option value="flash" <?php if ($devData['mwilamp']=="flash") echo "selected='selected'" ?> >Flash</option>
	    <option value="wink" <?php if ($devData['mwilamp']=="wink") echo "selected='selected'" ?> >Wink</option>
	    <option value="blink" <?php if ($devData['mwilamp']=="blink") echo "selected='selected'" ?> >Blink</option>
	    <option value="off" <?php if ($devData['mwilamp']=="off") echo "selected='selected'" ?> >Off</option>
	  </select>
    </tr>

    <tr> 
	<td></td>
	<td><a href="#" class="info"><?php echo _("tzoffset")?>:<span><?php echo _("Set the TimeZone offset for your phone. Not usually required, but may be needed to keep some phones screens from turning off during the day.")?></span></a></td>
	<td><input size="6" type="text" name="devData[tzoffset]" id="devData[tzoffset]" value="<?php  echo $devData['tzoffset']?>" /></td> 
    </tr>

    <tr>
	<td>&nbsp;</td>
    </tr>
	
<?php
    $loadlist = sccp_get_tftp_loadlist($type);
    if (strlen($loadlist['loadimage']) > 0) {
        $devData['imageversion'] = $loadlist['loadimage'];
    }
?>
    <tr>
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("Phone Load Name")?>:<span><?php echo _("Firmware version for upgrade ")?></span></a></td>
	<td><input size="20" type="text" name="devData[imageversion]" id="devData[imageversion]" value="<?php  echo $devData['imageversion']?>" /></td> 
     </tr>

    <tr>
	<td>&nbsp;</td>
    </tr>
	
    <tr> 
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("NAT")?>:<span><?php echo _("Device NAT support (default Off)")?></span></a></td>
	 <td>
	<select name="devData[nat]" id="devData[nat]">
	    <option value="off" <?php if ($devData['nat']=="off") echo "selected='selected'" ?> >Off</option>
	    <option value="on" <?php if ($devData['nat']=="on") echo "selected='selected'" ?> >On</option>
	  </select>
    </tr>

	
    <tr> 
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("directrtp")?>:<span><?php echo _("This option allow devices to do direct RTP sessions (default Off)")?></span></a></td>
	 <td>
	<select name="devData[directrtp]" id="devData[directrtp]">
	    <option value="off" <?php if ($devData['directrtp']=="off") echo "selected='selected'" ?> >Off</option>
	    <option value="on" <?php if ($devData['directrtp']=="on") echo "selected='selected'" ?> >On</option>
	  </select>
    </tr>

	
    <tr> 
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("earlyrtp")?>:<span><?php echo _("The audio strem will be open in the progress and connected state.<br>Valid options: none, progress, offhook, dial, ringout. Default may be Progress.")?></span></a></td>
	 <td>
	<select name="devData[earlyrtp]" id="devData[earlyrtp]">
	    <option value="progress" <?php if ($devData['earlyrtp']=="progress") echo "selected='selected'" ?> >Progress</option>
	    <option value="offhook" <?php if ($devData['earlyrtp']=="offhook") echo "selected='selected'" ?> >Offhook</option>
	    <option value="dial" <?php if ($devData['earlyrtp']=="dial") echo "selected='selected'" ?> >Dial</option>
	    <option value="none" <?php if ($devData['earlyrtp']=="none") echo "selected='selected'" ?> >None</option>
	  </select>
    </tr>

    <tr>
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("deny")?>:<span><?php echo _("Deny IP address list. Default is 0.0.0.0")?></span></a></td>
	<?php 
	if (strlen($devData['deny']) < 7) {
	    $devData['deny'] = $sccpConf['deny'];;
	}
	?>
	<td><input size="20" type="text" name="devData[deny]" id="devData[deny]" value="<?php  echo $devData['deny']?>" /></td> 
    </tr>
	
    <tr>
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("permit")?>:<span><?php echo _("Permit IP address list. Default is the server's webserver IP address.")?></span></a></td>
	<?php
	if (strlen($devData['permit']) < 7) {
	    $devData['permit'] = $sccpConf['permit'];
	}
	?>
	<td><input size="20" type="text" name="devData[permit]" id="devData[permit]" value="<?php  echo $devData['permit']?>" /></td> 
    </tr>
	

    <tr>
	<td>&nbsp;</td>
    </tr>

	
    <tr> 
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("Pickup Exten")?>:<span><?php echo _("Enable Pickup function to direct pickup an extension. Default is On")?></span></a></td>
	 <td>
	<select name="devData[pickupexten]" id="devData[pickupexten]">
	    <option value="on" <?php if ($devData['pickupexten']=="on") echo "selected='selected'" ?> >On</option>
	    <option value="off" <?php if ($devData['pickupexten']=="off") echo "selected='selected'" ?> >Off</option>
	  </select>
    </tr>
    <tr>
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("Pickup Context")?>:<span><?php echo _("Context where direct pickup search for extensions. Default value in FreePBX is from-internal.")?></span></a></td>
	<?php
	if (strlen($devData['pickupcontext']) < 1) {
	    $devData['pickupcontext'] = $sccpConf['context'];
	}
	?>
	<td><input size="20" type="text" name="devData[pickupcontext]" id="devData[pickupcontext]" value="<?php  echo $devData['pickupcontext']?>" /></td> 
    </tr>
	
    <tr> 
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("Pickup Mode Answer")?>:<span><?php echo _("On (Default)= the call has been answered when picked up<br />Off = call manager way, the phone who picked up the call rings the call")?></span></a></td>
	<td>
	<select name="devData[pickupmodeanswer]" id="devData[pickupmodeanswer]">
	    <option value="on" <?php if ($devData['pickupmodeanswer']=="on") echo "selected='selected'" ?> >On</option>
	    <option value="off" <?php if ($devData['pickupmodeanswer']=="off") echo "selected='selected'" ?> >Off</option>
	  </select>
    </tr>

    <tr> 
	<td>&nbsp;</td>
	<td><a href="#" class="info"><?php echo _("Background Image")?>:<span><?php echo _("For phones that can display background images - display this one. Default is [empty]")?></span></a></td>
	<td><input size="20" type="text" name="devData[backgroundImage]" id="devData[backgroundImage]" value="<?php  echo $devData['backgroundImage']?>" /></td> 
    </tr>

    <tr> 
	<td>&nbsp;</td>
	<td><a href="#" class="info">Ringtone:<span><?php echo _("The ringtone that the phone will default to. Can be overridden in the phone. The files RINGLIST.XML provice the basic phone ring tones, while DISTINCTIVERINGLIST.XML defines the list of possible ring tones for your other line types. They, along with the actual 'raw' ringtones, are stored in the /tftpboot/ directory with the rest of the config files."); ?></a></td>
	<td><input size="20" type="text" name="devData[ringtone]" id="devData[ringtone]" value="<?php  echo $devData['ringtone']?>" /></td> 
    </tr>

    <tr>
	<td>&nbsp;</td>
    </tr>
	

    <tr>
	<td>&nbsp;</td>
	<td colspan="5"><br /><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>"> 
	<?php
	    if ($devdisplay) { 
		echo '<input name="ResetPhone" id="ResetPhone" type="button" value="Reset Phone" onclick="reset_phone(edit_sccp_devices)" />';
		echo "<input type='hidden' name='RestartPhone' id='RestartPhone' value=0>";

			
	    }
	?>
	</td>
    </tr>
</table>
</form>


<?php echo add_free_space(7); ?>


<script language="javascript">

function check_sccp_device(theForm) {
    var msgInvalidMAC = "<?php echo _('Invalid MAC address specified'); ?>";
    var msgInvalidPhoneType = "<?php echo _("Must select phone type - currently $phone_type or $type"); ?> or theForm.type.value";

    // set up the Destination stuff
    setDestinations(theForm, '_post_dest');
    
    defaultEmptyOK = false;
    
    if (theForm.mac_address.value.length != 12) 
	    return warnInvalid(theForm.mac_address, msgInvalidMAC);

     
    if ("<!php echo $devData['type'] ?>" ==""){
	alert (msgInvalidPhoneType);
	return false;
    }
     
    if (!validateDestinations(theForm, 1, true))
	    return false;
    
    return true;
}

function reset_phone(theForm) {
    var msgResetPhone = "<?php echo _('Reset phone: '); ?>";
    var Phone = "SEP"+theForm.mac_address.value;

    if (confirm(msgResetPhone+Phone+'.  OK ?' )) {
	theForm.RestartPhone.value = 1;
	document.edit_sccp_devices.submit();
    } else {
	return false;
    }

}

function mayusculas(){

    texto = document.getElementById("mac_address").value;
    document.getElementById("mac_address").value = texto.toUpperCase();
}
 
</script>
