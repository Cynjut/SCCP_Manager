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

function check_website($check_url) {
    $headers = @get_headers($check_url);
    if(strpos($headers[0],'200') > 0) {
        $retval = "<td><A HREF='$check_url'><img src='/admin/modules/sccp_manager/check.png'></img></A></td>";
    } else {
        $retval = "<td><A HREF='$check_url'><img src='/admin/modules/sccp_manager/delete-icon.png'></img></A></td>";
    }
    return $retval;
}

global $astman; 

$version = sccp_get_asterisk_version();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';

$sccpConf = sccp_get_confData('client');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$RestartPhone = isset($_REQUEST['RestartPhone']) ? $_REQUEST['RestartPhone'] :  '';

if (isset($_REQUEST['del'])) {
    $action = 'del';
}

$tftpdisplay = isset($_REQUEST['tftpdisplay']) ? $_REQUEST['tftpdisplay'] :  'XMLDefault';

$tftpData = isset($_REQUEST['tftpData']) ? $_REQUEST['tftpData'] :  '';

$devData = sccp_get_device_full($tftpdisplay);

if ($RestartPhone){
    sccp_reset_phone($tftpdisplay);	
} 

switch ($action) {
    case 'edit':
	    sccp_edit_tftp($tftpdisplay,$tftpData);
//	    $astman->send_request("Command", array("Command" => "sccp restart ".$tftpdisplay));
	    //needreload();
	    redirect_standard();
    break;
    case 'del':
	    sccp_delete_tftp($tftpdisplay);
	    //needreload();
	    redirect_standard();
    break;
}

?>

<div class="rnav"><ul>
<?php
    echo '<li><a href="config.php?display=sccp_tftp&amp;tftpdisplay=XMLDefault" class="">XMLDefault</a></li>';
    foreach (sccp_list_tftp_devices() as $row) {
        echo '<li><a href="config.php?display=sccp_tftp&amp;type='.$row['type'].'&amp;tftpdisplay='.$row['name'].'" class="">'.$row['name'] . ' (' .$row['type'] . ')</a></li>';
    }

?>
</ul></div>

<div class="content">

<?php

    // load
    $tftpData = sccp_get_tftp($tftpdisplay);

    if ($tftpdisplay == 'XMLDefault') {
        $loadlist = sccp_get_tftp_loadlist('Default');
        $start_comment =  "<!-- No default loadinfo defined for XMLDefault\n";
        $end_comment = "-->\n";
        $tftpData['addonidx'] = '';
    } else {
        $loadlist = sccp_get_tftp_loadlist($type);
        $tftpData['loadInformation'] = $loadlist['loadimage'];
        $addlist = sccp_get_tftp_loadlist($devData['addon']);
        $tftpData['module_loadinfo'] = $addlist['loadimage'];
    }

    echo "<h2>&nbsp;&nbsp;Edit: $tftpdisplay ($type)";
    if ($type == '') {
        echo " type";
    }
    echo "</h2>";
    if ($tftpdisplay != 'XMLDefault') {
	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=del';
    	$tlabel_del = sprintf(_("Delete XML Config File %s"),$tftpdisplay);
    	$label_del = '<span>&nbsp;&nbsp;<img width="16" height="16" border="0" title="'.$tlabel_del.'" alt="" src="images/user_delete.png"/>&nbsp;<a href="'.$delURL.'">'.$tlabel_del.'.cnf.xml</a></span>';
    	echo $label_del;
    }
    $tftpData['bindaddr'] = $sccpConf['bindaddr'];
    $tftpData['port'] = $sccpConf['port'];
    $tftpData['deviceProtocol'] = 'SCCP';
?>

<form name="edit_sccp_tftp" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post">
    <input type="hidden" name="tftpdisplay" value="<?php echo $tftpdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($tftpdisplay ? 'edit' : 'add'); ?>" >

<table>
    <tr><td colspan="3"><h5>Edit Config<hr></h5></td></tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Device Protocol:<span>.</span></a></td>
        <td><input size="12" type="text" name="tftpData[deviceProtocol]" id='deviceProtocol' value="<?php echo $tftpData['deviceProtocol'] ?>" maxlength="12" readonly="true"></tdtr>
    <tr>
        <td>&nbsp;&nbsp;</td>
        <td><a href="#" class="info">Bind Address:<span>The address to bind to for SCCP. In general, it should be set to '0.0.0.0'. If you find problems with one-way audio, you can set this to a specific address in the server. Note that '127.0.0.1' is always wrong here.</span></a></td>
        <td><input size="12" type="text" name="tftpData[bindaddr]" id='bindaddr' value="<?php echo $tftpData['bindaddr'] ?>" maxlength="12" ></td>
    </tr>
<?php
    $tftpData['bindaddr'] = $sccpConf['bindaddr'];
?>
    <tr>
        <td></td>
        <td><a href="#" class="info">Bind Port:<span>The port on the server to bind to for SCCP. The default is 2000.</span></a></td>
        <td><input size="12" type="text" name="tftpData[port]" id='port' value="<?php echo $tftpData['port'] ?>" maxlength="12" ></td>
    </tr>
<?php
    $versionStamp = "(" . date("M d Y H:m:s").")";
    $debug = "Yes";

?>
    <tr>
        <td></td>
        <td><a href="#" class="info">Version Stamp:<span>This is the modified date for the config file. Supposedly, if you make a change to the file and the version stamp has changed, the phone will make the updates automatically. We never know, since we reset the phone automatically at the end of an edit.</span></a></td>
        <td><input size="20" type="text" name="tftpData[versionStamp]" id='versionStamp' value="<?php echo $versionStamp ?>" maxlength="20" readonly="true"></td>
    </tr>

<?php

//
// Theory of Module Info
//
// If we are looking at the XMLDefault file, then the load information 
// should not be presented.
// 
// Rather than mess around with a bunch of conditional PHP code, I'm just
// going to comment out the next block.
//


echo "$start_comment";
?>
    <tr>
        <td></td>
        <td><a href="#" class="info">Load Information:<span>The firmware version for the software on the phone. This should vary from device type to device type. If the load information for this device is incorrect, update the XMLDefault entry, which is where the defaults are stored.</span></a></td>
        <td><input size="32" type="text" name="tftpData[loadInformation]" id='loadInformation' value="<?php echo $tftpData['loadInformation'] ?>" maxlength="12" ></td>
    </tr>
<?php

//
// Theory of Addon Info
//
// If the device from the devInfo check returns "NULL" or "" for the Addon
// Model, then the Addon Load Information should not be presented.
// 
// Rather than mess around with a bunch of conditional PHP code, I'm just
// going to comment out the next block.
//


if ($devData['addon'] == "") {
    echo "<!-- No sidecar defined for this device \n";
    $end_comment = "-->";
}
?>

    <tr>
        <td></td>
        <td><a href="#" class="info">Addon Load Information:<span>The firmware version for the software on the phone sidecar. This should vary from device to device. Note that the XMLDefault.cnf.xml has a list of valid loads for most sidecars already, so the default is "blank"</span></a></td>
        <td><input size="12" type="text" name="tftpData[module_loadinfo]" id='module_loadinfo' value="<?php echo $tftpData['module_loadinfo'] ?>" maxlength="12" ></td>
    </tr>

<?php echo $end_comment ?>

    <tr>
        <td></td>
        <td><a href="#" class="info">Locale Name:<span>The language the phone will display native prompts in.</span></a></td>
        <td>
            <select name="tftpData[locale_name]">
                <option value="English_United_States" <?php if ($tftpData['locale_name']=="English_United_States") echo "selected='selected'" ?> >English_United_States</option>
                <option value="English_United_Kingdom" <?php if ($tftpData['locale_name']=="English_United_Kingdom") echo "selected='selected'" ?> >English_United_Kingdom</option>
                <option value="Danish_Denmark" <?php if ($tftpData['locale_name']=="Danish_Denmark") echo "selected='selected'" ?> >Danish_Denmark</option>
                <option value="Dutch_Netherlands" <?php if ($tftpData['locale_name']=="Dutch_Netherlands") echo "selected='selected'" ?> >Dutch_Netherlands</option>
                <option value="French_France" <?php if ($tftpData['locale_name']=="French_France") echo "selected='selected'" ?> >French_France</option>
                <option value="German_Germany" <?php if ($tftpData['locale_name']=="German_Germany") echo "selected='selected'" ?> >German_Germany</option>
                <option value="Italian_Italy" <?php if ($tftpData['locale_name']=="Italian_Italy") echo "selected='selected'" ?> >Italian_Italy</option>
                <option value="Japanese_Japan" <?php if ($tftpData['locale_name']=="Japanese_Japan") echo "selected='selected'" ?> >Japanese_Japan</option>
                <option value="Norwegian_Norway" <?php if ($tftpData['locale_name']=="Norwegian_Norway") echo "selected='selected'" ?> >Norwegian_Norway</option>
                <option value="Portuguese_Portugal" <?php if ($tftpData['locale_name']=="Portuguese_Portugal") echo "selected='selected'" ?> >Portuguese_Portugal</option>
                <option value="Russian_Russia" <?php if ($tftpData['locale_name']=="Russian_Russia") echo "selected='selected'" ?> >Russian_Russia</option>
                <option value="Spanish_Spain" <?php if ($tftpData['locale_name']=="Spanish_Spain") echo "selected='selected'" ?> >Spanish_Spain</option>
                <option value="Swedish_Sweden" <?php if ($tftpData['locale_name']=="Swedish_Sweden") echo "selected='selected'" ?> >Swedish_Sweden</option>
                <option value="User_Defined" <?php if ($tftpData['locale_name']=="User_Defined") echo "selected='selected'" ?> >User_Defined</option>
	    </select>
	</td>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Locale Code:<span>The two letter code for your locale name (above). You can modify the locale name if you want, but it will be overwritten with the name associated with this code, of a known name is available.</span></a></td>
        <td>
            <select name="tftpData[locale_code]">
                <option value="US" <?php if ($tftpData['locale_code']=="US") echo "selected='selected'" ?> >US</option>
                <option value="UK" <?php if ($tftpData['locale_code']=="UK") echo "selected='selected'" ?> >UK</option>
                <option value="CA" <?php if ($tftpData['locale_code']=="CA") echo "selected='selected'" ?> >CA</option>
                <option value="DK" <?php if ($tftpData['locale_code']=="DK") echo "selected='selected'" ?> >DK</option>
                <option value="NL" <?php if ($tftpData['locale_code']=="NL") echo "selected='selected'" ?> >NL</option>
                <option value="FR" <?php if ($tftpData['locale_code']=="FR") echo "selected='selected'" ?> >FR</option>
                <option value="CA" <?php if ($tftpData['locale_code']=="CA") echo "selected='selected'" ?> >CA</option>
                <option value="DE" <?php if ($tftpData['locale_code']=="DE") echo "selected='selected'" ?> >DE</option>
                <option value="AT" <?php if ($tftpData['locale_code']=="AT") echo "selected='selected'" ?> >AT</option>
                <option value="CH" <?php if ($tftpData['locale_code']=="CH") echo "selected='selected'" ?> >CH</option>
                <option value="IT" <?php if ($tftpData['locale_code']=="IT") echo "selected='selected'" ?> >IT</option>
                <option value="JP" <?php if ($tftpData['locale_code']=="JP") echo "selected='selected'" ?> >JP</option>
                <option value="NO" <?php if ($tftpData['locale_code']=="NO") echo "selected='selected'" ?> >NO</option>
                <option value="PT" <?php if ($tftpData['locale_code']=="PT") echo "selected='selected'" ?> >PT</option>
                <option value="RU" <?php if ($tftpData['locale_code']=="RU") echo "selected='selected'" ?> >RU</option>
                <option value="ES" <?php if ($tftpData['locale_code']=="ES") echo "selected='selected'" ?> >ES</option>
                <option value="SE" <?php if ($tftpData['locale_code']=="SE") echo "selected='selected'" ?> >SE</option>
                <option value="UN" <?php if ($tftpData['locale_code']=="UN") echo "selected='selected'" ?> >UN</option>
            </select>
	</td>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Network Locale Code:<span>The two letter code for your network locale name.</span></a></td>
        <td>
            <select name="tftpData[networkLocale]">
                <option value="US" <?php if ($tftpData['networkLocale']=="US") echo "selected='selected'" ?> >US</option>
                <option value="UK" <?php if ($tftpData['networkLocale']=="UK") echo "selected='selected'" ?> >UK</option>
                <option value="CA" <?php if ($tftpData['networkLocale']=="CA") echo "selected='selected'" ?> >CA</option>
                <option value="DK" <?php if ($tftpData['networkLocale']=="DK") echo "selected='selected'" ?> >DK</option>
                <option value="NL" <?php if ($tftpData['networkLocale']=="NL") echo "selected='selected'" ?> >NL</option>
                <option value="FR" <?php if ($tftpData['networkLocale']=="FR") echo "selected='selected'" ?> >FR</option>
                <option value="CA" <?php if ($tftpData['networkLocale']=="CA") echo "selected='selected'" ?> >CA</option>
                <option value="DE" <?php if ($tftpData['networkLocale']=="DE") echo "selected='selected'" ?> >DE</option>
                <option value="AT" <?php if ($tftpData['networkLocale']=="AT") echo "selected='selected'" ?> >AT</option>
                <option value="CH" <?php if ($tftpData['networkLocale']=="CH") echo "selected='selected'" ?> >CH</option>
                <option value="IT" <?php if ($tftpData['networkLocale']=="IT") echo "selected='selected'" ?> >IT</option>
                <option value="JP" <?php if ($tftpData['networkLocale']=="JP") echo "selected='selected'" ?> >JP</option>
                <option value="NO" <?php if ($tftpData['networkLocale']=="NO") echo "selected='selected'" ?> >NO</option>
                <option value="PT" <?php if ($tftpData['networkLocale']=="PT") echo "selected='selected'" ?> >PT</option>
                <option value="RU" <?php if ($tftpData['networkLocale']=="RU") echo "selected='selected'" ?> >RU</option>
                <option value="ES" <?php if ($tftpData['networkLocale']=="ES") echo "selected='selected'" ?> >ES</option>
                <option value="SE" <?php if ($tftpData['networkLocale']=="SE") echo "selected='selected'" ?> >SE</option>
                <option value="UN" <?php if ($tftpData['networkLocale']=="UN") echo "selected='selected'" ?> >UN</option>
            </select>
	</td>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Directory URL:<span>The URL for the Cisco XML Directory file that is displayed when you press the "Directory" key on your phone. Should typically be on the server above, but can be anywhere.</span></a></td>
        <td><input size="48" type="text" name="tftpData[directoryURL]" id='directoryURL' value="<?php echo $tftpData['directoryURL'] ?>" maxlength="127" ></td>
	<?php echo check_website($tftpData['directoryURL']); ?>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Services URL:<span>The URL for the Cisco XML Services file. Should typically be on the server above, but can be anywhere.</span></a></td>
        <td><input size="48" type="text" name="tftpData[servicesURL]" id='servicesURL' value="<?php echo $tftpData['servicesURL'] ?>" maxlength="127" ></td>
	<?php echo check_website($tftpData['servicesURL']); ?>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Idle URL:<span>The URL for the Cisco XML file that gets displayed when the phone is Idle, but still within the time frame associated with "Active". Should typically be on the server above, but can be anywhere.</span></a></td>
        <td><input size="48" type="text" name="tftpData[idleURL]" id='idleURL' value="<?php echo $tftpData['idleURL'] ?>" maxlength="127" ></td>
	<?php echo check_website($tftpData['idleURL']); ?>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Idle Timeout:<span>The number of minutes the phone will sit idle before the Idle process for each particular device kicks in. For some phones, it's the URL below. For others, the screen just goes dark.</span></a></td>
        <td><input size="12" type="text" name="tftpData[idleTimeout]" id='idleTimeout' value="<?php echo $tftpData['idleTimeout'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Autoselect Line Enable:<span>This setting (for some devices) allows the device to select the phone line to be used. Default is '0' for the first line. ???</span></a></td>
        <td>
            <select name="tftpData[autoSelectLineEnable]">
                <option value="0" <?php if ($tftpData['autoSelectLineEnable']=="0") echo "selected='selected'" ?> >Disabled</option>
                <option value="1" <?php if ($tftpData['autoSelectLineEnable']=="1") echo "selected='selected'" ?> >Enabled</option>
	    </select>
        </td>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Auto Call Select:<span>When this is selected, the phone automatically changes the focus to the line that is ringing. If nt selected, the focus remains on the current line.</span></a></td>
        <td>
            <select name="tftpData[autoCallSelect]">
                <option value="0" <?php if ($tftpData['autoCallSelect']=="0") echo "selected='selected'" ?> >Disabled</option>
                <option value="1" <?php if ($tftpData['autoCallSelect']=="1") echo "selected='selected'" ?> >Enabled</option>
	    </select>
        </td>
    </tr>
    <tr>
        <td></td>
        <td><a href="#" class="info">Background Image Access:<span>I'm guessing on this one, but on some devices, the background image on the display can be modified at the device. I think this is the thing that allows that to take.</span></a></td>
        <td>
            <select name="tftpData[backgroundImageAccess]">
                <option value="false" <?php if ($tftpData['backgroundImageAccess']=="false") echo "selected='selected'" ?> >Disabled</option>
                <option value="true" <?php if ($tftpData['backgroundImageAccess']=="true") echo "selected='selected'" ?> >Enabled</option>
	    </select>
        </td>
    </tr>
<?php
?>

    <tr>
        <td>&nbsp;</td>
        <td colspan="3"><br/><input name="Submit" type="submit" value=Submit Changes></td>
    </tr>


	
</table>

<?php
    if ($debug == 'Yes') {
//      echo "<pre>";
//	echo "DevData\n";
//	var_dump($devData);
//	echo "TFTPData\n";
//	car_dump($tftpData);
//	echo "Load Lists\n";
//	var_dump($loadlist);
//        echo "</pre>";
    }
?>

</div>

<?php echo add_free_space(7); ?>
