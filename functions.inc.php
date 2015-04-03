<?php
/**
 * @copyright Dave Burgess, 2015 based on original work by
 * @copyright Javier de la Fuente, EEZ CSIC
 * @license GPL2
 */


function sccp_reset_phone($name) {
    global $astman; 

    $astman->Command("sccp reset ".$name);
	
}

function sccp_get_asterisk_version() {
    global $astman; 

    $ast_sig = $astman->Command("core show version");
    list ($software, $version) = explode(' ',$ast_sig['data']);
    return $version;
}
 
function sccp_get_moh_classes() {
    global $astman; 

    $ast_out = $astman->Command("moh show classes");
    $ast_out = preg_split("/[\n]/",$ast_out['data']);

    foreach ($ast_out as $text) {
	if (substr($text,0,5) == 'Class') {
	    $text = str_replace('Class: ','',$text);
	    $ast_moh[] = $text;
	}
    }
    return $ast_moh;
}
 
function sccp_list_devices() {
    global $db;
    
    $sql = "SELECT b.device, RIGHT(b.device,12) AS mac, b.name AS ext, d.type  
		FROM buttonconfig b LEFT JOIN sccpdevice d ON b.device = d.name
		WHERE b.type='line' ORDER BY b.name";    
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
	die_freepbx($results->getMessage()."<br><br>Error selecting from buttonconfig, sccpdevice");
    }
    return $results;
}

function sccp_list_tftp_devices() {
    global $db;
    
    $sql = "SELECT DISTINCT name, type  
		FROM sccpdevice ORDER BY name";
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
	die_freepbx($results->getMessage()."<br><br>Error selecting from sccpdevice");
    }
    return $results;
}

function sccp_list_extensions() {
    global $db;

    $sql = "SELECT sccpline.name, sccpline.label, buttonconfig.device 
		FROM sccpline, buttonconfig 
		WHERE sccpline.name=buttonconfig.name 
		ORDER BY sccpline.name";
		    
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
	die_freepbx($results->getMessage()."<br><br>Error selecting from sccpline");
    }
    return $results;
}

function sccp_list_devices_wo_extension() {
    global $db;

    $sql = "SELECT name, type 
		FROM sccpdevice 
		WHERE name NOT IN (SELECT device FROM buttonconfig WHERE type='line')
		ORDER BY name";
	    
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
	die_freepbx($results->getMessage()."<br><br>Error selecting from sccpdevice");
    }
    return $results;

}

function sccp_list_extensions_wo_device() {
    global $db;
    

    $sql = "SELECT name, label 
		FROM sccpline 
		WHERE name NOT IN (select name from buttonconfig where type='line')
		ORDER BY name";
	    
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
	die_freepbx($results->getMessage()."<br><br>Error selecting from sccpline");
    }
    return $results;
}

function sccp_list_keysets() {
    global $astman; 

    $ast_out = $astman->Command("sccp show softkeyssets");
    
    $ast_out = preg_split("/[\n]/",$ast_out['data']);
    for ($i = 0; $i < 5; $i++) {
	$ast_out[$i] = "";
    }
    $i = count($ast_out) - 1;
    $ast_out[--$i] = "";
    foreach ($ast_out as $line) {
	if (strlen($line) > 3) {
	    $line = substr($line,2);
	    list ($line,$junk) = split(' ',$line);
	    if (strlen($ast_key[$line]) < 1) {
		$ast_key[$line] = $line;;
	    }
	}
    }
    return $ast_key;
}
 
function sccp_get_device($device) {
    global $db;
    
    $sql = "SELECT RIGHT(name,12) AS mac, type, description 
	    FROM sccpdevice 
	    WHERE name='$device'";

    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($row)) {
	die_freepbx($row->getMessage()."<br><br>Error selecting row from buttonconfig, sccpdevice for device $device");
    }

    return($row);
}

function sccp_get_device_full($device) {
    global $db;
    
    $sql = "SELECT * 
	    FROM sccpdevice 
	    WHERE name='$device'";

    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($row)) {
	die_freepbx($row->getMessage()."<br><br>Error selecting row from sccpdevice");
    }

    return $row;
}


function sccp_get_extension($extension) {
    global $db;
    
    $extension = (int) $extension;

    $sql = "SELECT label, description  
		FROM sccpline
		WHERE name=$extension ";    

		
    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($row)) {
	die_freepbx($row->getMessage()."<br><br>Error selecting row from sccpline");
    }

    return $row;
}

function sccp_get_extension_full($extension) {
    global $db;
    
    $sql = "SELECT * 
	    FROM sccpline 
	    WHERE name='$extension'";

    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($row)) {
	die_freepbx($row->getMessage()."<br><br>Error selecting row from sccpline");
    }

    return $row;
}




function sccp_get_buttons_assoc($device) {
    global $db;
    
    $sql = "SELECT instance, type, name as extension
	FROM buttonconfig 
	WHERE device='$device' 
	ORDER BY instance";
	
    $res = mysql_query($sql);	
    
    $num_row = 0;
    
    while ($row[$num_row] = mysql_fetch_assoc($res)) {
	$num_row++;
    }

    return $row;
}


function sccp_get_ext_assoc($device) {
    global $db;
    
    $sql = "SELECT name as extension
	FROM buttonconfig 
	WHERE device='$device' ";

    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($row)) {
	die_freepbx($row->getMessage()."<br><br>Error selecting row from buttonconfig, sccpdevice");
    }

    return $row;
    
}


function sccp_get_dev_assoc($extension) {
    global $db;
    
    $extension = (int) $extension;

    $sql = "SELECT device 
	FROM buttonconfig 
	WHERE name='$extension' ";

    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($row)) {
	die_freepbx($row->getMessage()."<br><br>Error selecting row from buttonconfig, sccpdevice");
    }

    return $row;
    
}

function get_buttons_devtype($type) {

    $res = mysql_query("SELECT dns, buttons
			FROM sccpdevmodel
			WHERE model='$type' ");
			
    while ($row = mysql_fetch_row($res)) {
	$modelData['dns'] = $row[0];
	$modelData['buttons'] = $row[1];
    }
    return $modelData;

}

function sccp_add_device($devData, $buttonData) {
    global $db;

    if ( strpos($devData['name'],"SEP") === false )
	$devData['name'] = "SEP".$devData['name'];

    foreach ($devData as $Field => $Value) {
	$Query .= ($Query ? "," : "")."$Field=".(trim($Value)!="" ? "\"$Value\"" : "null");
    }
    $sql = "INSERT INTO sccpdevice set $Query";

    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().$sql);
    }
    
    $numButton = 0;
    while ( strlen($buttonData['type'.$numButton]) > 0  ) { 
	if ($buttonData['type'.$numButton] == 'line') {
	    $sql = "INSERT IGNORE INTO sccpline
		(id, name, label, description, mailbox, cid_num, cid_name)
		VALUES ('{$buttonData['name'.$numButton]}', '{$buttonData['name'.$numButton]}', 'Extension {$buttonData['name'.$numButton]}', 'Line {$buttonData['name'.$numButton]}', '{$buttonData['name'.$numButton]}', '{$buttonData['name'.$numButton]}', '{$buttonData['name'.$numButton]}' )";
	    
	    $result = $db->query($sql);
	    if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	    }
	    if ($buttonData['default_line'] == $buttonData['name'.$numButton]) {
		$buttonData['options'.$numButton] = "Default";
	    } else {
		$buttonData['options'.$numButton] = "";
	    }
	}
	$sql = "INSERT INTO buttonconfig
	    (device, instance, type, name, options)
	    VALUES ('{$devData['name']}', $numButton+1, '{$buttonData['type'.$numButton]}', '{$buttonData['name'.$numButton]}', '{$buttonData['options'.$numButton]}')";

	$result = $db->query($sql);
	if(DB::IsError($result)) {
	    die_freepbx($result->getMessage().$sql);
	}
	$numButton++;	
    }  
    sccp_edit_tftp($devData['name']);	    
}


function sccp_add_extension($extData) {
    global $db;
    
    $extData['id'] = $extData['name'];
    $extData['cid_num'] = $extData['name'];
    $extData['mailbox'] = ( !$extData['mailbox'] ? $extData['name'] : $extData['mailbox']);

    foreach ($extData as $Field => $Value) {
	if (!empty($Value)) {
	     $Query .= ($Query ? "," : "")."$Field=".(trim($Value)!="" ? "\"$Value\"" : "null");
	}
    }
    $sql = "REPLACE INTO sccpline set $Query";
    
    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().' '.$sql);
    }

    $Query = '';
    $astData['id'] = $extData['name'];
    $astData['tech'] = 'custom';
    $astData['dial'] = 'SCCP/'.$extData['name'];
    $astData['devicetype'] = 'fixed';
    $astData['user'] = $extData['name'];
    $astData['description'] = $extData['description'];
    $astData['emergency_cid'] = '';

    foreach ($astData as $Field => $Value) {
	if (!empty($Value))
	$Query .= ($Query ? "," : "")."$Field=".(trim($Value)!="" ? "\"$Value\"" : "null");
    }
    
    $Query = "REPLACE INTO devices SET $Query";
    $result = $db->query($Query);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().' '.$Query);
    }

}

/*
 * We don't delete the sccpline entries since they could be
 * shared. They are actually managed in page.sccp_lines.php, so
 * there's no reason to delete them unless we mean it.
 */

function sccp_edit_device($devData, $buttonData) {
    global $db;

    if ( strpos($devData['name'],"SEP") === false )
	$devData['name'] = "SEP".$devData['name'];

    $sql = "DELETE FROM buttonconfig
	    WHERE device = '{$devData['name']}' ";

    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().$sql);
    }


    $sql = "DELETE FROM sccpdevice
	    WHERE name = '{$devData['name']}' ";

    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().$sql);
    }

    sccp_delete_tftp($devData['name']);
    
    sccp_add_device($devData, $buttonData);
	
}

function sccp_edit_extension($extData) {
    global $db;

    $extData['id'] = $extData['name'];
    $extData['mailbox'] = ( $extData['mailbox'] ? $extData['mailbox'] : $extData['name'] );
    $extData['trnsfvm'] = ( $extData['trnsfvm'] ? $extData['trnsfvm'] : '*'.$extData['name'] );

    foreach ($extData as $Field => $Value) {
	$Query .= ($Query ? "," : "")."$Field=".(trim($Value)!="" ? "\"$Value\"" : "null");
    }
    $sql = "UPDATE sccpline set $Query
	    WHERE name = '{$extData['name']}' ";
    
    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().$sql);
    }
    
}

function sccp_delete_device($device) {
    global $db;


    $sql = "DELETE FROM buttonconfig
		    WHERE device = '$device'";

    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().$sql);
    }

    $sql = "DELETE FROM sccpdevice
		    WHERE name = '$device'";
    
    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().$sql);
    }

    sccp_delete_tftp($device);

}

function sccp_delete_extension($extension) {
    global $db;

    $sql = "DELETE FROM buttonconfig
		    WHERE name = '{$extension}'";

    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().$sql);
    }

    $sql = "DELETE FROM sccpline
		    WHERE name = '{$extension}'";
    
    $result = $db->query($sql);
    if(DB::IsError($result)) {
	die_freepbx($result->getMessage().$sql);
    }
}


function sccp_get_model_data(){
    global $db;
	
    $res = mysql_query("SELECT model, dns, buttons, loadimage
			FROM sccpdevmodel
			WHERE dns > 0
			ORDER BY model ");
			
    while ($row = mysql_fetch_row($res)) {
	$modelData['model'][] = $row[0];
	$modelData['dns'][] = $row[1];
	$modelData['buttons'][] = $row[2];
	$modelData['loadimage'][] = $row[3];
    }
    return $modelData;
}

function sccp_get_addon_data(){
    global $db;
	
    $res = mysql_query("SELECT model, buttons, loadimage
			FROM sccpdevmodel
			WHERE dns = 0
			ORDER BY model ");
			
    while ($row = mysql_fetch_row($res)) {
	$addonData['model'][] = $row[0];
	$addonData['buttons'][] = $row[1];
	$addonData['loadimage'][] = $row[2];
    }
    return $addonData;
}

//
// The old way of doing this bugs me, but I can see why they did it.
// The way I'm going to do it now is to use the XMLDefault.cnf.xml file,
// but I'm going to pull some of the elements out and not include some
// pieces in the "per-device" files.
//
function sccp_create_tftp($device){

    if (! file_exists("/tftpboot/XMLDefault.cnf.xml")) {
	$template = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/XMLDefault.cnf.xml');
	file_put_contents('/tftpboot/XMLDefault.cnf.xml', $template);
    }
	
    $confData = sccp_get_confData('client');
    $asterisk_ip = $confDate['bindaddr'];

    $filename = '/tftpboot/' . $device . '.cnf.xml';
    file_put_contents($filename, $template);
    chmod ($filename,0666);

    return true;
}

function sccp_delete_tftp($device){
    
    $filename = '/tftpboot/' . $device . '.cnf.xml';
    $command = 'rm -f '.$filename;
    exec($command); 

}

function add_free_space($lines){
    $cad = "";

    $lines = (int) $lines;
    for ($i=0; $i <= $lines; $i++) {
	$cad .= "<br/>";
    }
    return $cad;
}

function tras_button_data($b_Data){
    
    $num_bt = count($b_Data);
    
    for ($i=0; $i < $num_bt; $i++) {
	$buttonData['instance'.$i] = $b_Data[$i]['instance'];
	$buttonData['type'.$i] = $b_Data[$i]['type'];
	$buttonData['button'.$i] = $b_Data[$i]['extension'];
    }

    return $buttonData;
}


function get_properties_in_button($device, $instance) {
    global $db;
    
    $sql = "SELECT type, name, options 
	FROM buttonconfig 
	WHERE device='$device' AND instance='$instance' ";

    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($row)) {
	die_freepbx($row->getMessage()."<br><br>Error selecting row from buttonconfig, sccpdevice");
    }

    switch ($row['type']) {
      case ('line'):
	break;
      case ('speeddial'):
      case ('function'):
      case ('service'):
	list ($description, $hint) = explode(",",$row['options']);
	$row['description'] = $description;
	$row['hint'] = $hint;
	break;
      case ('empty'):
	$row['name'] = "";
	$row['options'] = "";
	break;
    }
    return $row;
}

function sccp_edit_config($confData) {
    $confDir = $amp_conf["ASTETCDIR"];
    if (strlen($confDir) < 1) {
	$confDir = "/etc/asterisk";
    }
    $inputfile = "$confDir/sccp.conf";
    if (! file_exists("$confDir/sccp.conf")) {
	$sccpfile = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/sccp.conf');
	file_put_contents("$confDir/sccp.conf", $sccpfile);
    }

    $handle = fopen($inputfile, "r");
    if (strpos($confData['allow'],',')) {
	$confData['allow'] = str_replace(',',"\nallow=",$confData['allow']);
    }
    $written = 'No';
    if ($handle) {
	$file_context = '';
	while (($input = fgets($handle)) != false) {
	    if (strpos($input,"]")) {
		$file_context = $input;
	    }
	    if (trim($file_context) != '[general]') {
		$outfile .= $input;
	    } else {
		if ($written == 'No') {
		    $outfile .= "[general]\n";
		    foreach ($confData as $field => $value) {
			$outfile .= "$field=$value\n";
		    }
		    $outfile .= "\n";
		    $written = 'Yes';
		}
	    }
//
// 'allow' is a multientry field, so we add commas.
//
	    
	}
	fclose($handle);
    } else {
	die_freepbx($results->getMessage()."<br><br>Error retrieving data from $inputfile");
    }
    file_put_contents("$confDir/sccp.conf", $outfile);
}

function sccp_get_confData($type) {
    global $db;

    $confDir = $amp_conf["ASTETCDIR"];
    if (strlen($confDir) < 1) {
	$confDir = "/etc/asterisk";
    }
    $inputfile = "$confDir/sccp.conf";
    if (! file_exists("$confDir/sccp.conf")) {
	$sccpfile = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/sccp.conf');
	file_put_contents("$confDir/sccp.conf", $sccpfile);
    } else {
	$sccpfile = file_get_contents("$inputfile");
    }

    $handle = fopen($inputfile, "r");
    if ($handle) {
	while (($input = fgets($handle)) != false) {
	    if ($loc = strpos($input,';')) {
	       $input = substr($input,0,($loc-1));
	    }
	    $input = trim($input);
	    $input = preg_replace('/[\s]+=[\s]+/','=',$input);
	    if (strpos($input,"]")) {
		$file_context = $input;
		$confData['file_context'] = preg_replace('/[\[\]]/','',$input);
	    }
//
// If the $type is either 'client' or 'server', we want the information 
// from the general context. If it's anything else, we want to pull
// the context from the file and get the info from it.
//
// 'allow' is a multientry field, so we add commas.
//
	    if ($type == 'server' || $type == 'client') {
		$search = '[general]';
	    } else {
		$search = "[".$type."]";
		$confData['type'] = $type;
	    }
	    if ($file_context == "$search" && strpos($input,"=")) {
		list($field,$value) = split("=",$input);
		if ($field == 'allow') {
		    $confData['allow'] .= $value . ',';
		} else {
		    $confData[$field] = $value;
		}
	    }
	}
	fclose($handle);
    } else {
	die_freepbx($results->getMessage()."<br><br>Error retrieving data from $inputfile");
    }

//
// Special Fields - these are returned if we are searching for [general]
//
    if ($search == '[general]') {
	$bindaddr = $confData['bindaddr'];
	if ($binaddr == '') {
	    $bindaddr = '0.0.0.0';
	}
	if ($type == 'client' && $bindaddr == '0.0.0.0') {
	    $bindaddr = $_SERVER['SERVER_ADDR'];
	}
	$confData['bindaddr'] = $bindaddr;
    
	if ($confData['permit'] == '') {
	    $confData['permit'] = 'internal';
	}
	if (($lastComma = strlen($confData['allow'])) > 1) {
	    $confData['allow'] = substr($confData['allow'],0,$lastComma-1);
	}
	if ($confData['disallow'] == '') {
	    $confData['disallow'] = 'all';
	}
	if ($confData['allow'] == '') {
	    $confData['allow'] = 'ulaw';
	}
    }
    return($confData);
} 

function sccp_get_keysetdata($name) {

    if ($name == 'default') {
	$keysetData = sccp_get_confData('softkeyset');
	$keysetData['name'] = 'default';
    } else {
	$keysetData = sccp_get_confData($name);
    }
    $keysetData['name'] = ($keysetData['name'] ? $keysetData['name'] : $name);
    return $keysetData;
} 

function sccp_get_tftp($tftpdisplay) {

    $inputfile = "/tftpboot/$tftpdisplay.cnf.xml";
    $tftpfile = file_get_contents($inputfile);
    if (strlen($tftpfile) < 24) {
	$inputfile = "/tftpboot/XMLDefault.cnf.xml";
	$tftpfile = file_get_contents($inputfile);
	$tftpfile = preg_replace('/^\s\s+/imU','',$tftpfile);
	$tftpfile = preg_replace('/<default>$/imU','',$tftpfile);
	$tftpfile = preg_replace('/<[\/]+default>$/imU','',$tftpfile);
	$tftpfile = preg_replace('/.*<loadInformation[0-9].*$/imU','',$tftpfile);
    }

    $xml = simplexml_load_string($tftpfile);
    $json = json_encode($xml);
    $confData = json_decode($json,TRUE);
    if (is_array($confData['device'])) {
	$confData = $confData['device'];
    }

    $tftpData['deviceProtocol'] = $confData['deviceProtocol'];
    $tftpData['port'] = $confData['devicePool']['callManagerGroup']['members']['member']['callManager']["ports"]["ethernetPhonePort"];
    $tftpData['bindaddr'] = $confData['devicePool']['callManagerGroup']['members']['member']['callManager']["processNodeName"];

    $tftpData['versionStamp'] = $confData['versionStamp'];
    $tftpData['loadInformation'] = $confData['loadInformation'];
    $tftpData['addonidx'] = $confData["addOnModules"]["addOnModule"]['@attributes']["idx"];
    $tftpData['module_loadinfo'] = $confData["addOnModules"]["addOnModule"]["loadInformation"];
    $tftpData['locale_name'] = $confData['userLocale']['name'];
    $tftpData['locale_code'] = $confData['userLocale']['langCode'];
    $tftpData['directoryURL'] = $confData['directoryURL'];
    $tftpData['idleTimeout'] = $confData['idleTimeout'];
    if (! is_array($confData['idleURL'])) {
	$tftpData['idleURL'] = $confData['idleURL'];
    }
    if (! is_array($confData['proxyServerURL'])) {
	$tftpData['proxyServerURL'] = $confData['proxyServerURL'];
    }
    if (! is_array($confData['servicesURL'])) {
	$tftpData['servicesURL'] = $confData['servicesURL'];
    }
    $tftpData['autoSelectLineEnable'] = $confData['autoSelectLineEnable'];
    $tftpData['autoCallSelect'] = $confData['autoCallSelect'];
    return($tftpData);
}

function sccp_edit_tftp($tftpdisplay,$tftpData) {
    global $db;

    $filename = "/tftpboot/$tftpdisplay.cnf.xml";
    $tftpfile = file_get_contents($inputfile);
//
// I know this look crazy, but we pull the "<Default>" tags from the
// XMLDefault.cnf.xml file to create a template of a new file. If the 
// file exists, we don't screw with it.
//
    if (strlen($tftpfile) < 24) {
	$inputfile = "/tftpboot/SEP-Master.cnf.xml";
	$tftpfile = file_get_contents($inputfile);
    }

    $xml = simplexml_load_string($tftpfile);
    $json = json_encode($xml);
    $confData = json_decode($json,TRUE);

//
// At this point, we have all the data in the system.
//
    $confData['devicePool']['callManagerGroup']['members']['member']['callManager']["ports"]["ethernetPhonePort"] = $tftpData['port'];
    $confData['devicePool']['callManagerGroup']['members']['member']['@attributes']['priority'] = "0";
    $confData['devicePool']['callManagerGroup']['members']['member']['callManager']["processNodeName"] = $tftpData['bindaddr'];

    $confData['versionStamp'] = $tftpData['versionStamp']; 
    $confData['loadInformation'] = $tftpData['loadInformation'];
    $addonidx = $devData['addonidx'];
    if ($tftpdisplay != 'XMLDefault') {
	if (is_numeric($addonidx) && $addonidx < 1) {
	    unset($confData["addOnModules"]);
	}
	if (is_numeric($addonidx) && $addonidx == 1) {
	    $confData["addOnModules"]["addOnModule"]['@attributes']["idx"] = "1";
	    $confData["addOnModules"]["addOnModule"]["loadInformation"] = $tftpData['module_loadinfo'];
	}
	if (is_numeric($addonidx) && $addonidx == 2) {
	    $confData["addOnModules"]["addOnModule"]['@attributes']["idx"] = "2";
	    $confData["addOnModules"]["addOnModule"]["loadInformation"] = $tftpData['module_loadinfo'];
	}
    } else {
	unset($confData["addOnModules"]);
	unset($confData["loadInformation"]);
    }
    $confData['userLocale']['name'] = $tftpData['locale_name'];
    $confData['userLocale']['langCode'] = $tftpData['locale_code'];
    $confData['directoryURL'] = $tftpData['directoryURL'];
    $confData['idleTimeout'] = $tftpData['idleTimeout'];
    $confData['idleURL'] = $tftpData['idleURL'];
    $confData['proxyServerURL'] = $tftpData['proxyServerURL'];
    $confData['servicesURL'] = $tftpData['servicesURL'];
    $confData['autoSelectLineEnable'] = $tftpData['autoSelectLineEnable'];
    $confData['autoCallSelect'] = $tftpData['autoCallSelect'];

    if ($tftpdisplay == 'XMLDefault') {
	$res = mysql_query("SELECT vendor, model, loadimage, loadinformationid
			FROM sccpdevmodel
			WHERE loadinformationid IS NOT NULL 
			AND loadimage IS NOT NULL
			ORDER BY loadinformationid ");
			
	while ($row = mysql_fetch_row($res)) {
	    $vendor = $row[0];
	    $model = $row[1];
	    $model = preg_replace('/(,.*)/','',$model);
	    $loadimage = $row[2];
	    $loadinfo = $row[3];
	    $confData[$loadinfo] = array('@attributes' => array('model' => "$vendor $model"), '@value' => "$loadimage");
	}
	$xml = Array2XML::createXML('default', $confData);
    } else {
	$xml = Array2XML::createXML('device', $confData);
    }
    $outfile = $xml->saveXML();
    file_put_contents($filename, $outfile);
    chmod ($filename,0666);

    return;
}


function arrayToXML(Array $array, SimpleXMLElement $xml) {
    // Attribute string
    foreach($array as $key => $value) {
	// Nonarray
	if (!is_array($value)) {
	    (is_numeric($key)) ? $xml->addChild("item$key", $value) : $xml->addChild($key, $value);
	    continue;
	}   
	// Array
	$xmlChild = (is_numeric($key)) ? $xml->addChild("item$key") : $xml->addChild($key);
	if (is_array($key['@attributes'])) {
	    foreach ($key['@attributes'] AS $attrid => $attr) {
		$xmlChild->addAttribute($attrid, $attr);
	    }
#	    unset($key['@attributes'];
	}
	arrayToXML($value, $xmlChild);
    }
    return($xml->asXML());
}  

function sccp_get_tftp_loadlist($model) {
    global $db;

    if (strtoupper($model) == 'DEFAULT') {
	$where = "WHERE loadimage IS NOT NULL";
    } else {
	$where = "WHERE model = '$model'";
    }
	
    $res = mysql_query("SELECT loadinformationid,loadimage 
		FROM sccpdevmodel
		$where
		ORDER BY loadinformationid");    

    while ($row = mysql_fetch_row($res)) {
	if (strtoupper($model) == 'DEFAULT') {
	    $loadlist[$row[0]] = $row[1];
	} else {
	    $loadlist['loadimage'] = $row[1];
	}
    }
    return $loadlist;
}

function sccp_edit_keyset($keysetData) {
    $keysetImplode['name'] = $keysetData['name'];
    $keysetImplode['type'] = $keysetData['type'];
    $keysetImplode['file_context'] = $keysetData['file_context'];
    $keysetImplode['onhook'] = implode(',',$keysetData['onhook']);
    $keysetImplode['connected'] = implode(',',$keysetData['connected']);
    $keysetImplode['onhold'] = implode(',',$keysetData['onhold']);
    $keysetImplode['ringin'] = implode(',',$keysetData['ringin']);
    $keysetImplode['offhook'] = implode(',',$keysetData['offhook']);
    $keysetImplode['conntrans'] = implode(',',$keysetData['conntrans']);
    $keysetImplode['digitsfoll'] = implode(',',$keysetData['digitsfoll']);
    $keysetImplode['connconf'] = implode(',',$keysetData['connconf']);
    $keysetImplode['ringout'] = implode(',',$keysetData['ringout']);
    $keysetImplode['offhookfeat'] = implode(',',$keysetData['offhookfeat']);
    $keysetImplode['onhint'] = implode(',',$keysetData['onhint']);
    $keysetImplode['onstealable'] = implode(',',$keysetData['onstealable']);

//
// Write config file context section.
//
    $file_context = $keysetData['name'];
    if ($file_context != 'default') {
        $confDir = $amp_conf["ASTETCDIR"];
        if (strlen($confDir) < 1) {
	    $confDir = "/etc/asterisk";
        }
        $inputfile = "$confDir/sccp.conf";
        if (! file_exists("$confDir/sccp.conf")) {
	    $sccpfile = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/sccp.conf');
	    file_put_contents("$confDir/sccp.conf", $sccpfile);
        }
    
        $handle = fopen($inputfile, "r");
        $sccpfile = '';
        $file_context = "[$file_context]";
        if ($handle) {
	    while (($input = fgets($handle)) != false) {
	        if (trim($input) != $file_context) {
		    $sccpfile .= $input;
	        } else {
		    $sccpfile .= "$file_context	; Managed by sccp_manager\n";
		    $sccpfile .= "type=softkeyset\n";
//
//	We don't include the 'name=' directive in sccp.conf contexts.
//		    $sccpfile .= "name=".$keysetImplode['name']."\n";
//
		    foreach ($keysetImplode as $field => $value) {
		        if ($field != 'type' && $field != 'name' && $field != 'file_context') { 
			    if (strlen($value) > 1) {
			        $sccpfile .= $field . "=" . $value . "\n";
			    }
		        }
		    }
		    $sccpfile .= "\n";
		    $trimmer = true;
		    while ($trimmer) {
		        $trimmer = ($input = fgets($handle));
		        if (substr($input,0,1) == '[') {
			    $trimmer = false;
			    $sccpfile .= $input;
		        }
		    }
	        }
	    }
	}
    }	
    file_put_contents("$confDir/sccp.conf", $sccpfile);
    return $sccpfile;
}

function sccp_display_keyset($keysetData,$softkey,$option) {
    if ($keysetData['name'] == 'default') {
	$output = "<font size='+1'>";
	if (strpos(' '.$keysetData[$softkey],$option)) {
	    $output .= '&#x2611;';
	} else {
	    $output .= '&#x2610;';
	}
	$output .= "</font>&nbsp;$option<br>";
    } else {
	$output =  "<input type='checkbox' name='keysetData[$softkey][]' value='$option'";
	if (strpos(' '.$keysetData[$softkey],$option)) {
	    $output .= ' checked';
	}
	$output .= "> $option<br>"; 
    }
    return $output;
}

function sccp_list_devmodel() {
    global $db;

    $sql = "SELECT * FROM sccpdevmodel ORDER BY model;"; 

    $devmodel = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($devmodel)) {
	die_freepbx($devmodel->getMessage()."<br><br>Error selecting from sccpdevmodel");
    }
    return $devmodel;
}

function sccp_edit_devmodel($devmodel) {
    global $db;

    foreach ($devmodel as $field => $value) {
	list ($field, $model) = split('_',$field);
	if ($field == "del" && $value == 'on') {
	    $dquery = "DELETE FROM sccpdevmodel WHERE model = '$model';";
            $result = $db->query($dquery);
            if(DB::IsError($result)) {
                die_freepbx($result->getMessage()."Failed devmodel delete $dquery");
            }
	    $deleted[$model] = 'on';
	} else {
	    $modellist[$model] = $model;
	    $fieldlist[$model][$field] = $value;
	}
    }
    $query = '';
    foreach ($modellist AS $model) {
	if ((isset($deleted[$model]) && $deleted[$model] == 'on') || $fieldlist[$model]['model'] == "NEW" || $fieldlist[$model]['model'] == "") {
	    unset($fieldlist[$model]);
	} else {
	    $query = "REPLACE sccpdevmodel SET ";
	    $sql = '';
	    foreach ($fieldlist[$model] AS $field => $value) {
	        $sql .= ($sql ? "," : "")."$field=".(trim($value)!="" ? "\"$value\"" : "''");
	    }
	    $query .= $sql . ";\n";
	    $result = $db->query($query);
            if(DB::IsError($result)) {
    		die_freepbx($result->getMessage().' '.$query);
	    }
	}
    }
    return;
}

//
// This function creates new OS79xx.txt files in the /tftpboot
// directory. There's only a few that we actually need to do, 
// and the access to the database is the slow part, so we
// whack the DB once and search through for the files we know
// we're going build.
//
function sccp_create_osf() {
    global $db;

    $query = "SELECT * FROM sccpdevmodel WHERE model LIKE '79%' AND loadimage != '' AND dns > 0";
    $res = mysql_query($query);	
    
    if(DB::IsError($res)) {
        die_freepbx($res->getMessage()."Failed OS File create $query");
    }
    $name = 'not written';
    while ($row = mysql_fetch_assoc($res)) {
	$model = $row['model'];
	$loadimage = $row['loadimage'];
	if ($model == '7940' && $name != '79XX') {
	    $name = '79XX';
	    $filename = "/tftpboot/OS79XX.TXT";
	}
	if (strcmp($model,"7940") < 0) {
	    $filename = "/tftpboot/OS$model.TXT";
	}
	file_put_contents($filename, $loadimage);
    	chmod ($filename,0666);
    }
    return;
}

/**
 * Array2XML: A class to convert array in PHP to XML
 * It also takes into account attributes names unlike SimpleXML in PHP
 * It returns the XML in form of DOMDocument class for further manipulation.
 * It throws exception if the tag name or attribute name has illegal chars.
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * License: Apache License 2.0
 *	  http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (10 July 2011)
 * Version: 0.2 (16 August 2011)
 *	  - replaced htmlentities() with htmlspecialchars() (Thanks to Liel Dulev)
 *	  - fixed a edge case where root node has a false/null/0 value. (Thanks to Liel Dulev)
 * Version: 0.3 (22 August 2011)
 *	  - fixed tag sanitize regex which didn't allow tagnames with single character.
 * Version: 0.4 (18 September 2011)
 *	  - Added support for CDATA section using @cdata instead of @value.
 * Version: 0.5 (07 December 2011)
 *	  - Changed logic to check numeric array indices not starting from 0.
 * Version: 0.6 (04 March 2012)
 *	  - Code now doesn't @cdata to be placed in an empty array
 * Version: 0.7 (24 March 2012)
 *	  - Reverted to version 0.5
 * Version: 0.8 (02 May 2012)
 *	  - Removed htmlspecialchars() before adding to text node or attributes.
 *
 * Usage:
 *       $xml = Array2XML::createXML('root_node_name', $php_array);
 *       echo $xml->saveXML();
 */
 
class Array2XML {
 
    private static $xml = null;
	private static $encoding = 'UTF-8';
 
    /**
     * Initialize the root XML node [optional]
     * @param $version
     * @param $encoding
     * @param $format_output
     */
    public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
	self::$xml = new DomDocument($version, $encoding);
	self::$xml->formatOutput = $format_output;
		self::$encoding = $encoding;
    }
 
    /**
     * Convert an Array to XML
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return DomDocument
     */
    public static function &createXML($node_name, $arr=array()) {
	$xml = self::getXMLRoot();
	$xml->appendChild(self::convert($node_name, $arr));
 
	self::$xml = null;    // clear the xml node in the class for 2nd time use.
	return $xml;
    }
 
    /**
     * Convert an Array to XML
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return DOMNode
     */
    private static function &convert($node_name, $arr=array()) {
 
	//print_arr($node_name);
	$xml = self::getXMLRoot();
	$node = $xml->createElement($node_name);
 
	if(is_array($arr)){
	    // get the attributes first.;
	    if(isset($arr['@attributes'])) {
		foreach($arr['@attributes'] as $key => $value) {
		    if(!self::isValidTagName($key)) {
			throw new Exception('[Array2XML] Illegal character in attribute name. attribute: '.$key.' in node: '.$node_name);
		    }
		    $node->setAttribute($key, self::bool2str($value));
		}
		unset($arr['@attributes']); //remove the key from the array once done.
	    }
 
	    // check if it has a value stored in @value, if yes store the value and return
	    // else check if its directly stored as string
	    if(isset($arr['@value'])) {
		$node->appendChild($xml->createTextNode(self::bool2str($arr['@value'])));
		unset($arr['@value']);    //remove the key from the array once done.
		//return from recursion, as a note with value cannot have child nodes.
		return $node;
	    } else if(isset($arr['@cdata'])) {
		$node->appendChild($xml->createCDATASection(self::bool2str($arr['@cdata'])));
		unset($arr['@cdata']);    //remove the key from the array once done.
		//return from recursion, as a note with cdata cannot have child nodes.
		return $node;
	    }
	}
 
	//create subnodes using recursion
	if(is_array($arr)){
	    // recurse to get the node for that key
	    foreach($arr as $key=>$value){
		if(!self::isValidTagName($key)) {
		    throw new Exception('[Array2XML] Illegal character in tag name. tag: '.$key.' in node: '.$node_name);
		}
		if(is_array($value) && is_numeric(key($value))) {
		    // MORE THAN ONE NODE OF ITS KIND;
		    // if the new array is numeric index, means it is array of nodes of the same kind
		    // it should follow the parent key name
		    foreach($value as $k=>$v){
			$node->appendChild(self::convert($key, $v));
		    }
		} else {
		    // ONLY ONE NODE OF ITS KIND
		    $node->appendChild(self::convert($key, $value));
		}
		unset($arr[$key]); //remove the key from the array once done.
	    }
	}
 
	// after we are done with all the keys in the array (if it is one)
	// we check if it has any text value, if yes, append it.
	if(!is_array($arr)) {
	    $node->appendChild($xml->createTextNode(self::bool2str($arr)));
	}
 
	return $node;
    }
 
    /*
     * Get the root XML node, if there isn't one, create it.
     */
    private static function getXMLRoot(){
	if(empty(self::$xml)) {
	    self::init();
	}
	return self::$xml;
    }
 
    /*
     * Get string representation of boolean value
     */
    private static function bool2str($v){
	//convert boolean to text value.
	$v = $v === true ? 'true' : $v;
	$v = $v === false ? 'false' : $v;
	return $v;
    }
 
    /*
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     */
    private static function isValidTagName($tag){
	$pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
	return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }
}
?>

