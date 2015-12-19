<?php
//if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//

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

global $astman; 

$version = sccp_get_asterisk_version();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';

?>

<div class="content">
    <h2>&nbsp;&nbsp;Edit SCCP Configuration</h2>

<?php
if ($action == 'edit') {
    $confData = isset($_REQUEST['confData']) ? $_REQUEST['confData'] :  '';
    sccp_edit_config($confData);
//    needreload();
    redirect_standard('confdisplay');
}
$confData = sccp_get_confData('server');

$mohclasses = sccp_get_moh_classes();

//echo "<pre>";
//var_dump($mohclasses);
//echo "</pre>";

?>

<form name="edit_sccp_config" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post">
    <input type="hidden" name="action" value="edit" >
<table>
    <tr><td colspan="5"><h5><br><br><br>Server Configuration<hr></h5></td></tr>
    <tr>
        <td>&nbsp;&nbsp;&nbsp;</td>
        <td><a href="#" class="info">Servername:<span>This is the type of server - usually, it will be Asterisk.</span></a></td>
        <td><input size="12" type="text" name="confData[servername]" id='servername' value="<?php echo $confData['servername'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Keepalive:<span>Time between Keep Alive checks. Valid range is 60-300 seconds. After much trial-and-error, the minimum (60) seems to work just fine.</span></a></td>
    	<td><input size="12" type="text" name="confData[keepalive]" id='keepalive' value="<?php echo $confData['keepalive'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Debug:<span>Enable debugging in SCCP module. Set to '0' for 'No' and '1' for 'Yes'.</span></a></td>
    	<td>
            <select name="confData[debug]">
                <option value="0" <?php if ($confData['debug']=="0") echo "selected='selected'" ?> >0</option>
                <option value="1" <?php if ($confData['debug']=="1") echo "selected='selected'" ?> >1</option>
	    </select>
	</td>
    </tr>
    <tr>
    	<td>&nbsp;&nbsp;&nbsp;</td>
    	<td><a href="#" class="info">Context:<span>This is the context in which your phones will operate. It should match the context you are using for the rest of your phones (if you have any). The FreePBX default is 'from-internal'.</span></a></td>
    	<td><input size="12" type="text" name="confData[context]" id='context' value="<?php echo $confData['context'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td>&nbsp;&nbsp;&nbsp;</td>
    	<td><a href="#" class="info">Bind Address:<span>The address to bind to for SCCP. In general, it should be set to '0.0.0.0'. If you find problems with one-way audio, you can set this to a specific address in the server. Note that '127.0.0.1' is always wrong here.</span></a></td>
    	<td><input size="12" type="text" name="confData[bindaddr]" id='bindaddr' value="<?php echo $confData['bindaddr'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td>&nbsp;&nbsp;&nbsp;</td>
    	<td><a href="#" class="info">Bind Port:<span>The port number on the server that SCCP will be listening for your phones on. This must match the port in your SEPDefault.cnf.xml or SEPxxxxxxxxxxxx.cnf.xml file.</span></a></td>
    	<td><input size="12" type="text" name="confData[port]" id='port' value="<?php echo $confData['port'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">IP deny:<span>IP Address deny netmask. Should always be at least '0.0.0.0/0.0.0.0'.</span></a></td>
    	<td><input size="12" type="text" name="confData[deny]" id='deny' value="<?php echo $confData['deny'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
   	<td></td>
    	<td><a href="#" class="info">IP permit:<span>This is the netmask for allowed connections. A special netmask called 'internal' is available (and is the default). If you do not specify a netmask, 'internal' will be used.</span></a></td>
    	<td><input size="12" type="text" name="confData[permit]" id='permit' value="<?php echo $confData['permit'] ?>" maxlength="12" ></td>
    </tr>
    <tr><td colspan="5"><h5><br>Device Properties<hr></h5></td></tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Date Format:<span>The date format for the on-screen display. Can be one of the following: (D-M-YA, M.D.Y, Y/M/D) where 'D' is Day, 'M' is Month, 'Y' is Year, 'A' is 24-hour, 'a' is 12-hour, and the separators can be '.','-','/'"</span></a></td>
	<td>
            <select name="confData[dateformat]">
                <option value="D.M.Y" <?php if ($confData['dateformat']=="D.M.Y") echo "selected='selected'" ?> >D.M.Y</option>
                <option value="D.M.YA" <?php if ($confData['dateformat']=="D.M.YA") echo "selected='selected'" ?> >D.M.YA</option>
                <option value="Y.M.D" <?php if ($confData['dateformat']=="Y.M.D") echo "selected='selected'" ?> >Y.M.D</option>
                <option value="YA.M.D" <?php if ($confData['dateformat']=="YA.M.D") echo "selected='selected'" ?> >YA.M.D</option>
                <option value="M-D-Y" <?php if ($confData['dateformat']=="M-D-Y") echo "selected='selected'" ?> >M-D-Y</option>
                <option value="M-D-YA" <?php if ($confData['dateformat']=="M-D-YA") echo "selected='selected'" ?> >M-D-YA</option>
                <option value="D-M-Y" <?php if ($confData['dateformat']=="D-M-Y") echo "selected='selected'" ?> >D-M-Y</option>
                <option value="D-M-YA" <?php if ($confData['dateformat']=="D-M-YA") echo "selected='selected'" ?> >D-M-YA</option>
                <option value="Y-M-D" <?php if ($confData['dateformat']=="Y-M-D") echo "selected='selected'" ?> >Y-M-D</option>
                <option value="YA-M-D" <?php if ($confData['dateformat']=="YA-M-D") echo "selected='selected'" ?> >YA-M-D</option>
                <option value="M/D/Y" <?php if ($confData['dateformat']=="M/D/Y") echo "selected='selected'" ?> >M/D/Y</option>
                <option value="M/D/YA" <?php if ($confData['dateformat']=="M/D/YA") echo "selected='selected'" ?> >M/D/YA</option>
                <option value="D/M/Y" <?php if ($confData['dateformat']=="D/M/Y") echo "selected='selected'" ?> >D/M/Y</option>
                <option value="D/M/YA" <?php if ($confData['dateformat']=="D/M/YA") echo "selected='selected'" ?> >D/M/YA</option>
                <option value="Y/M/D" <?php if ($confData['dateformat']=="Y/M/D") echo "selected='selected'" ?> >Y/M/D</option>
                <option value="YA/M/D" <?php if ($confData['dateformat']=="YA/M/D") echo "selected='selected'" ?> >YA/M/D</option>
                <option value="M/D/Y" <?php if ($confData['dateformat']=="M/D/Y") echo "selected='selected'" ?> >M/D/Y</option>
                <option value="M/D/YA" <?php if ($confData['dateformat']=="M/D/YA") echo "selected='selected'" ?> >M/D/YA</option>
	    </select>
	</td>
    </tr>
    <tr>
    	<td>&nbsp;&nbsp;&nbsp;</td>
    	<td><a href="#" class="info">Disallowed Codecs:<span>Codecs not allowed. Typically, this is set to 'all' and the allowed codecs are specified below.</span></a></td>
    	<td><input size="12" type="text" name="confData[disallow]" id='disallow' value="<?php echo $confData['disallow'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td>&nbsp;&nbsp;&nbsp;</td>
    	<td><a href="#" class="info">Allowed Codecs:<span>Codecs allowed. The list if allowed codecs for Cisco phones is really short. They are 'alaw', 'ulaw', and 'g729'. Use a comma separated list if you want to allow more than one codec - the syntax will be corrected in the server.</span></a></td>
    	<td><input size="12" type="text" name="confData[allow]" id='allow' value="<?php echo $confData['allow'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td>&nbsp;&nbsp;&nbsp;</td>
    	<td><a href="#" class="info">First Digit Timeout:<span>The amount of time after your first digit to start dialing automatically. This can be over-ridden with settings in your dialplan.xml or by using the 'immediate dial' button.</span></a></td>
    	<td><input size="12" type="text" name="confData[firstdigittimeout]" id='firstdigittimeout' value="<?php echo $confData['firstdigittimeout'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Digit Timeout:<span>The amount of time to wait after the second (or subsequent) dialed digit. Override rules are the same as for firstdigittimeout.</span></a></td>
    	<td><input size="12" type="text" name="confData[digittimeout]" id='digittimeout' value="<?php echo $confData['digittimeout'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Autoanswer Ring Time:<span>The amount of time the phones will ring when being called as Intercom or Paging mode.</span></a></td>
    	<td><input size="12" type="text" name="confData[autoanswer_ring_time]" id='autoanswer_ring_time' value="<?php echo $confData['autoanswer_ring_time'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Autoanswer Tone:<span>The tone the phone plays back when it picks up the phone in autoanswer mode. Default is '0x32'. Silence is '0x00'. There are lots of tones, all expressed as '0XNN' where 'NN' is a hexadecimal number.</span></a></td>
    	<td><input size="12" type="text" name="confData[autoanswer_tone]" id='autoanswer_tone' value="<?php echo $confData['autoanswer_tone'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Remote Hangup Tone:<span>The tone played by the phone when it received a remote hang-up signal. Use '0' to disable the tone.</span></a></td>
    	<td><input size="12" type="text" name="confData[remotehangup_tone]" id='remotehangup_tone' value="<?php echo $confData['remotehangup_tone'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Transfer Tone:<span>The tone played when a call is transferred. Use '0' to disable the tone.</span></a></td>
    	<td><input size="12" type="text" name="confData[transfer_tone]" id='transfer_tone' value="<?php echo $confData['transfer_tone'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Call Waiting Tone:<span>The tone played when a call is waiting. If you set this one to '0', you will not get a tone in your current call if a new call comes in, so you might want to disable call waiting for this line instead.</span></a></td>
    	<td><input size="12" type="text" name="confData[callwaiting_tone]" id='callwaiting_tone' value="<?php echo $confData['callwaiting_tone'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Music Class:<span>Available MOH Classes. These are the MOH classes listed in your current server.</span></a></td>
	<td>
            <select name="confData[musicclass]">
		<?php
		$i = 0;
		while ($i < count($mohclasses)) {
		    $moh = $mohclasses[$i++];
                    echo "<option value='$moh' ";
		    if ($moh == $confData['musicclass']) {
			echo "selected='selected'";
		    }
		    echo ">$moh</option>";
		}
		?>
	    </select>
	</td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Language:<span>This is the language for your hints and other features of the phone. If you don't have any languages installed or are using a single language, you can leave this blank.</span></a></td>
	<td>
            <select name="confData[language]">
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
    	<td></td>
    	<td><a href="#" class="info">Echo Cancel:<span>Echo Cancellation (On or Off).</span></a></td>
	<td>
            <select name="confData[echocancel]">
                <option value="on" <?php if ($confData['echocancel']=="on") echo "selected='selected'" ?> >On</option>
                <option value="off" <?php if ($confData['echocancel']=="off") echo "selected='selected'" ?> >Off</option>
	    </select>
	</td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Silence Suppression:<span>Slience Suppression on the phone.</span></a></td>
	<td>
            <select name="confData[silencesuppression]">
                <option value="on" <?php if ($confData['silencesuppression']=="on") echo "selected='selected'" ?> >On</option>
                <option value="off" <?php if ($confData['silencesuppression']=="off") echo "selected='selected'" ?> >Off</option>
	    </select>
	</td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Private Calling Enabled:<span>Place a call with privacy Options (no Caller ID) turned on. Needs to be supported in Asterisk to work through SIP and DAHDI trunks.</span></a></td>
	<td>
            <select name="confData[private]">
                <option value="on" <?php if ($confData['private']=="on") echo "selected='selected'" ?> >On</option>
                <option value="off" <?php if ($confData['private']=="off") echo "selected='selected'" ?> >Off</option>
	    </select>
	</td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Directed Pickup Mode (Answer):<span>If a call is sent with the "directed pickup" flag, the phone will answer when set to "Yes".</span></a></td>
	<td>
            <select name="confData[directed_pickup_modeanswer]">
                <option value="on" <?php if ($confData['directed_pickup_modeanswer']=="on") echo "selected='selected'" ?> >On</option>
                <option value="off" <?php if ($confData['directed_pickup_modeanswer']=="off") echo "selected='selected'" ?> >Off</option>
	    </select>
	</td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Call Answer Order:<span>Which call should be answered first? The most common choice is "oldestfirst", but other orders are supported.</span></a></td>
	<td>
            <select name="confData[callanswerorder]">
                <option value="oldestfirst" <?php if ($confData['callanswerorder']=="oldestfirst") echo "selected='selected'" ?> >Oldestfirst</option>
                <option value="latestfirst" <?php if ($confData['callanswerorder']=="latestfirst") echo "selected='selected'" ?> >Latestfirst</option>
	    </select>
	</td>
    </tr>
    <tr><td colspan="5"><h5><br><br><br>Class or Type of Service Configuration<hr></h5></td></tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">SCCP Type Of Service:<span>SCCP Type of Service - this is modifiable, but don't.</span></a></td>
    	<td><input size="12" type="text" name="confData[sccp_tos]" id='sccp_tos' value="0x68" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">SCCP Class Of Service:<span>SCCP Class of Service. This is modifiable, but don't.</span></a></td>
    	<td><input size="12" type="text" name="confData[sccp_cos]" id='sccp_cos' value="4" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Audio Type Of Service:<span>Audio Class of Service. This is modifiable, but don't.</span></a></td>
    	<td><input size="12" type="text" name="confData[audio_tos]" id='audio_tos' value="0xB8" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Audio Class Of Service:<span>Audio Class of Service. This is modifiable, but don't.</span></a></td>
    	<td><input size="12" type="text" name="confData[audio_cos]" id='audio_cos' value="6" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Video Type Of Service:<span>Video Class of Service. This is modifiable, but don't.</span></a></td>
    	<td><input size="12" type="text" name="confData[video_tos]" id='video_tos' value="0x88" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Video Class Of Service:<span>Video Class of Service. This is modifiable, but don't.</span></a></td>
    	<td><input size="12" type="text" name="confData[video_cos]" id='video_cos' value="5" maxlength="12" ></td>
    </tr>
    <tr><td colspan="5"><h5><br><br><br>"Hotline" (Unregistered Device) Configuration<hr></h5></td></tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Hotline Enabled:<span>This allows unregistered extensions to connect to the system and dial the number listed below.</span></a></td>
    	<td><input size="12" type="text" name="confData[hotline_enabled]" id='hotline_enabled' value="<?php echo $confData['hotline_enabled'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Hotline Context:<span>This is the context through which the phone will connect. It should probably match your other contest. The default is "from-internal" but "from-internal-xfer" would also make sense by limiting the options for the person using the phone.</span></a></td>
    	<td><input size="12" type="text" name="confData[hotline_context]" id='hotline_context' value="<?php echo $confData['hotline_context'] ?>" maxlength="12" ></td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Hotline Extension:<span>The number that gets called when a hotline is picked up. hint</span></a></td>
    	<td><input size="12" type="text" name="confData[hotline_extension]" id='hotline_extension' value="<?php echo $confData['hotline_extension'] ?>" maxlength="12" ></td>
    </tr>
    <tr><td colspan="5"><h5><br><br><br>Realtime Database Configuration<hr></h5></td></tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Device Table:<span>THis is the devicetable for your realtime configuration. Don't change this unless you know what you are doing and have made all the appropriate changes in the rest of your Asterisk config files. There are two reasonable settings for this - the sccpdevice table or the sccpdeviceconfig view. If you do not want to use the realtime database anymore, you can set this to blank. NOT RECOMMENDED.</span></a></td>
	<td>
            <select name="confData[devicetable]">
                <option value="sccpdevice" <?php if ($confData['devicetable']=="sccpdevice") echo "selected='selected'" ?> >sccpdevice</option>
                <option value="sccpdeviceconfig" <?php if ($confData['devicetable']=="sccpdeviceconfig") echo "selected='selected'" ?> >sccpdeviceconfig</option>
	    </select>
	</td>
    </tr>
    <tr>
    	<td></td>
    	<td><a href="#" class="info">Line Table:<span>THis is the linetable for your realtime configuration. Don't change this unless you know what you are doing and have made all the appropriate changes in the rest of your Asterisk config files. If you do not want to use the realtime database anymore, you can set this to blank. NOT RECOMMENDED.</span></a></td>
    	<td><input size="12" type="text" name="confData[linetable]" id='linetable' value="<?php echo $confData['linetable'] ?>" maxlength="12" readonly='true'></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="4"><br /><input name="Submit" type="submit" value="Submit Changes"> 
        </td>
</table>
</form>


<?php echo add_free_space(7); ?>
</div>
