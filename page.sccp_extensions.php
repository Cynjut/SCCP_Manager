<?php
//if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/** SCCP MANAGER Module for FreePBX 2.5
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

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';

if (isset($_REQUEST['del'])) $action = 'del';

$extData = isset($_REQUEST['extData']) ? $_REQUEST['extData'] :  '';


$label = isset($_REQUEST['label']) ? $_REQUEST['label'] :  '';
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$extdisplay = isset($_REQUEST['extdisplay']) ? $_REQUEST['extdisplay'] :  '';

$phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] :  '';

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
	$dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}


switch ($action) {
	case 'add':
		sccp_add_extension($extData);
//		needreload();
		redirect_standard();
	break;
	case 'edit':
		sccp_edit_extension($extData);
//		needreload();
		redirect_standard('extdisplay');
	break;
	case 'del':
		sccp_delete_extension($extdisplay);
//		needreload();
		redirect_standard();
	break;
}

?>

<div class="rnav"><ul>

<?php

    echo '<li><a href="config.php?display=sccp_extensions&amp;type='.$type.'">'._('Add Extension').'</a></li>';

    foreach (sccp_list_extensions() as $row) {
	echo '<li><a href="config.php?display=sccp_extensions&amp;type='.$type.'&amp;extdisplay='.$row['name'].'" class="">' .$row['name'].' - '.$row['label'] . ' (' .$row['device'] . ') </a></li>';
    }
    echo '<li>&nbsp;</li>';

    $row_temp = sccp_list_extensions_wo_device();
    if ( count($row_temp) > 0 ) {
	echo '<li>'._('Extensions without device associated').'</li>';
	foreach (sccp_list_extensions_wo_device() as $row) {
		echo '<li><a href="config.php?display=sccp_extensions&amp;type='.$type.'&amp;extdisplay='.$row['name'].'" class="">' .$row['name'].' - '.$row['label'] .  ' ( ) </a></li>';
        }
    }

?>
</ul></div>

<div class="content">

<?php
//var_dump( $_REQUEST );

if ($extdisplay) {
    // load
    $row = sccp_get_extension($extdisplay);
    $extData = sccp_get_extension_full($extdisplay);
    
    $label = $row['label'];
    $description = $row['description'];
    
    $row_dev = sccp_get_dev_assoc($extdisplay);
    
    $phone = $row_dev['device'];
    
    echo "<h2>SCCP Extension: $extdisplay</h2>";

    echo "<span><img width='16' height='16' border='0' title='Delete Extension $extdisplay' alt='' src='images/user_delete.png'/>&nbsp;<a href='";
    echo $_SERVER['PHP_SELF'];
    echo "?";
    echo $_SERVER['QUERY_STRING'];
    echo "&action=del'>Delete Extension $extdisplay</a></span><BR>"; 
    echo "<span><img width='16' height='16' border='0' title='Asterisk Extension $extdisplay' alt='' src='images/edit.png'/>&nbsp;<a href='";
    echo $_SERVER['PHP_SELF'];
    echo "?type=setup&display=extensions&extdisplay=$extdisplay'>Asterisk Extension $extdisplay</a></span><BR>"; 
} else {
    echo "<h2>Add SCCP Extension</h2>";
}
?>

<form name="edit_sccp_extensions" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return check_sccp_extension(edit_sccp_extensions);">

	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
<table>
<tr><td colspan="3"><h5><?php  echo ($extdisplay ? "Edit Extension" : "Add Extension") ?><hr></h5></td></tr>

<tr>
	<td><a href="#" class="info">SCCP Extension:<span>"Must match a FreePBX Extension.</span></a></td>
	<td><input size="6" type="text" maxlength="10" name="extData[name]" id="extension" value="<?php  echo $extData['name'] ?>"></td>
</tr>

<tr> 
	<td><a href="#" class="info">Label:<span>Extension Label</span></a></td>
	    <td><input type='text' size='20' maxlength='20' name='extData[label]' value="<?php  echo $extData['label']?>" ></td> 
        </tr>

	<tr> 
   	    <td><a href="#" class="info">Description:<span>Extension description.</span></a></td>
 	    <td>
            <input type='text' size='20' maxlength='20' name='extData[description]' value="<?php  echo $extData['description']?>" ></td> 
        </tr>
    
<?php
if ($extdisplay) {
	echo "<tr><td colspan='3'><h5>Associated Phone<hr></h5></td></tr>";
	echo "<tr>";
	echo "	<td><a href='#' class='info'>SCCP Device:<span>SCCP Phone associated: SEPxxxxxxxxxxxx</span></a></td>";
	echo "	<td>";
	if ($phone == '') {
	    echo "<h5>Not assigned</h5>";
	} else {
	    echo "<a href=config.php?display=sccp_devices&type=setup&devdisplay=$phone> $phone</a>";
	}
	echo "</td></tr>";
}
?>    
    <tr>
        <td><a href="#" class="info">"Divert to" Voicemail:<span>The extension to forward a call to when "IDivert" is pressed. The default is the this extension's voicemail.</span></a></td>
	<td><input size="6" maxlength="6" type="text" name="<?php echo $extData['trnsfvm'] ?>" id="<?php echo $extData[trnsfvm] ?>" value="<?php  echo ($extData['trnsfvm'] ? $extData['trnsfvm'] : $extData['name']) ?>"></td> 
    </tr>
    <tr><td colspan="2"><h5><?php  echo "<br>Extension Properties" ?><hr></h5></td></tr>
    
<!--
	<tr>
		<td><a href="#" class="info">PIN:<span>Personal ID Number. Used to limit access to phones. Not currently implemented.</span></a></td>
		<td><input size="6" maxlength="6" type="text" name="extData[pin]" id="extData[pin]" value="<?php  echo $extData['pin']?>" /></td> 
     </tr>
-->    
	<tr>
		<td><a href="#" class="info">Context:<span>SCCP context this extension will send calls to. Only change this if you know what you are doing.</span></a></td>
		<td><input size="20" maxlength="20" type="text" name="extData[context]" id="extData[context]" value="<?php  echo ($extData['context'] ? $extData['context']  : "from-internal" )?>" /></td> 
    </tr>
    <tr>
	<td><a href="#" class="info">Caller ID Number:<span>This is the internal Caller ID Number, unless you allow internal CID numbers to get out onto your trunk.</span></a></td>
	<td><input size="20" maxlength="20" type="text" name="extData[cid_num]" id="extData[cid_num]" value="<?php  echo ($extData['cid_num'] ? $extData['cid_num']  : $extData['name'] )?>" /></td> 
    </tr>
    
    <tr>
	<td><a href="#" class="info">Caller ID Name:<span>This is the internal Caller ID Name, unless you allow internal CID info to get out onto your trunk.</span></a></td>
	<td><input size="20" maxlength="20" type="text" name="extData[cid_name]" id="extData[cid_name]" value="<?php  echo ($extData['cid_name'] ? $extData['cid_name']  : $extData['label'] )?>" /></td> 
    </tr>
    
    <tr>
		<td><a href="#" class="info">Incoming Limit:<span>Inbound call limit (per extension). Suggested value is 2. This allows you to process two calls at the same time or conference two numbers at once.</span></a></td>
		<td><input size="6" maxlength="6" type="text" name="extData[incominglimit]" id="extData[incominglimit]" value="<?php echo ($extData['incominglimit'] ? $extData['incominglimit']  : "2" ) ?>" /></td> 
     </tr>
    
	<tr>
		<td><a href="#" class="info">Mailbox:<span>Mailbox for this device. This should not be changed unless you know what you are doing. If left blank, the default setting is automatically configured by the voicemail module. Only change this on devices that may have special needs. Note that the PIAF 'Hotelwakeup' and FreePBX 'SayMyNumber' modules use the mailbox number as the extension.</span></a></td>
		<td><input size="6" maxlength="6" type="text" name="extData[mailbox]" id="extData[mailbox]" value="<?php echo $extData['mailbox'] ?>" /></td> 
     </tr>
    
	<tr>
		<td><a href="#" class="info">Voicemail Number:<span>Asterisk dialplan extension to reach voicemail for this device. Another common choice is "*98" to dial straight into the main Comedian Mail module. The "messages" button is programmed from the XMLDefault.cnf.xml and the device specific SEP*.cnf.xml files, so this option may not actually be useful in most settings.</span></a></td>
		<td><input size="6" maxlength="6" type="text" name="extData[vmnum]" id="extData[vmnum]" value="<?php echo ($extData['vmnum'] ? $extData['vmnum']  : "*97" ) ?>" /></td> 
     </tr>
	<tr> 
	<td><a href="#" class="info">Echo Cancel:<span><Sets the echocancellation for this line. Default is On.</span></a></td>
 		<td>
        <select name="extData[echocancel]" id="extData[echocancel]">
		    <option value="on" <?php if ($extData['echocancel']=="on") echo "selected='selected'" ?> >On</option>
		    <option value="off" <?php if ($extData['echocancel']=="off") echo "selected='selected'" ?> >Off</option>
  	    </select>
    </tr>

	<tr> 
	<td><a href="#" class="info">Silence Suppression:<span>Sets the phone silence suppression for this line. Default is Off.</span></a></td>
 		<td>
        <select name="extData[silencesuppression]" id="extData[silencesuppression]">
		    <option value="off" <?php if ($extData['silencesuppression']=="off") echo "selected='selected'" ?> >Off</option>
		    <option value="on" <?php if ($extData['silencesuppression']=="on") echo "selected='selected'" ?> >On</option>
  	    </select>
    </tr>
    <tr> 
	<td><a href="#" class="info">Call Group:<span>Callgroup that this device is part of - valid range is 1-63. Phone can only be part of one call group.</span></a></td>
 		<td>
        <input type='text' size='20' maxlength='20' name='extData[callgroup]' value="<?php  echo $extData['callgroup']?>" ></td> 
    </tr>

    <tr> 
	<td><a href="#" class="info">Pickup Group:<span>Pickup groups(s) that this device can pickup calls from using 'GPickup' button, can be one or more groups, e.g. '1,3-5' would be in groups 1,3,4,5. Does not affect direct extension pickup.</span></a></td>
 		<td>
        <input type='text' size='20' maxlength='20' name='extData[pickupgroup]' value="<?php  echo $extData['pickupgroup']?>" ></td> 
    </tr>


	<tr><td colspan="2"><h5>Language<hr></h5></td></tr>
	<tr> 
	<td><a href="#" class="info">Language Code:<span>This will cause all messages and prompts to use the selected language, if installed.</span></a></td>
        <td>
            <select name="extData[language]">
                <option value="en" <?php if ($confData['language']=="en") echo "selected='selected'" ?> >en</option>
                <option value="fr" <?php if ($confData['language']=="fr") echo "selected='selected'" ?> >fr</option>
                <option value="de" <?php if ($confData['language']=="de") echo "selected='selected'" ?> >de</option>
                <option value="it" <?php if ($confData['language']=="it") echo "selected='selected'" ?> >it</option>
                <option value="jp" <?php if ($confData['language']=="jp") echo "selected='selected'" ?> >jp</option>
                <option value="no" <?php if ($confData['language']=="no") echo "selected='selected'" ?> >no</option>
                <option value="pr" <?php if ($confData['language']=="pr") echo "selected='selected'" ?> >pr</option>
                <option value="ru" <?php if ($confData['language']=="ru") echo "selected='selected'" ?> >ru</option>
                <option value="es" <?php if ($confData['language']=="es") echo "selected='selected'" ?> >es</option>
                <option value="sc" <?php if ($confData['language']=="sc") echo "selected='selected'" ?> >sc</option>
            </select>
        </td>
	</tr>
	<tr>
	<td>&nbsp;</td>
    </tr>
    <tr>
	<td colspan="3"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>">
	</td>
	</tr>
</table>
</form>

<?php echo add_free_space(7); ?>


<script language="javascript">

function check_sccp_extension(theForm) {
	var msgInvalidExtension = "<?php echo _('Invalid SCCP Extension specified'); ?>";

	// set up the Destination stuff
	setDestinations(theForm, '_post_dest');

	// form validation
	defaultEmptyOK = false;
	if (isEmpty(theForm.extension.value))
		return warnInvalid(theForm.extension, msgInvalidExtension);

	if (!validateDestinations(theForm, 1, true))
		return false;

	return true;
}

</script>
