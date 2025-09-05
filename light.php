<?php
# Dlink PTZ Cam control script v.1.2
# Max.Fischer dev@monologic.ru

require_once __DIR__ . "/config.php";
$cam = load_credentials("ptz-cam");
$sensor = load_credentials("bms-sensor");

$host = $cam["hostname"];
$username = $cam["username"];
$password = $cam["password"];

$sensorHost = $sensor["hostname"];
$community = $sensor["community"];

$logfile = "./log/actions.log";
$sunsets = "./config/sunsets.csv";

# Write welcome log record
statuslog ("Outdoor light status update initiated");

# Read current light status
$sourceLightStatus = getLightStatus();

# Check if it's dark outside 
$isDark = isDark();

# Check current Security Guard status
$vigilance = getSNMP ("vigilance_mode", $sensor, "1.3.6.1.4.1.25728.8900.1.1.2.1", $community);

# if it's dark, light is off and security disarmed - enable lights.
if ($sourceLightStatus == 0 && $isDark == 1 && $vigilance == 0) { 
	statuslog ("It's dark now, security disarmed and the lights were switched off, so i decided to switch them on.");
	setLightStatus(1); 
	$currentLightStatus = 1;
}
if ($sourceLightStatus == 1 && $isDark == 1 && $vigilance == 0) {
	statuslog ("It's dark now, security disarmed and the lights were on. Nothing to do.");
}

# if it's dark, light is on and security armed - disable lights.
if ($sourceLightStatus == 1 && $isDark == 1 && $vigilance == 1) { 
	statuslog ("It's dark now, the lights were switched on, but security is armed, so i decided to switch them off.");
	setLightStatus(0); 
	$currentLightStatus = 0;
}
if ($sourceLightStatus == 0 && $isDark == 1 && $vigilance == 1) { 
	statuslog ("It's dark now, security is armed and the lights were switched off. Nothing to do.");
}

# If lights were initially on and it's light outside, disable lights regardless of security state.
if ($isDark == 0 && $sourceLightStatus == 1 ) {
	statuslog ("It's light outside, so i decided to switch the lights off.");
	setLightStatus(0); 
	$currentLightStatus = 0;
}
if ($isDark == 0 && $sourceLightStatus == 0 ) {
	statuslog ("It's light outside, and the lights were off. Nothing to do.");
}


# End log message
statuslog ("End of light status update procedure");


function setLightStatus ($newstatus) {
	global $host;
	global $username;
	global $password;
    ini_set('default_socket_timeout', 3);
        $params = array(
                'http' => array(
                        'method' => "GET",
                        'header' => "Authorization: Basic " . base64_encode("$username:$password")
              )
        );
        $ctx = stream_context_create($params);
        $url = "http://$host/dev/gpioCtrl.cgi?out1=$newstatus";
        $data = file_get_contents($url, false, $ctx);
	statuslog ("Setting new light status '$newstatus'");
    return "";
}

function getLightStatus () {
	global $host;
	global $username;
	global $password;
    ini_set('default_socket_timeout', 3);
        $params = array(
                'http' => array(
                        'method' => "GET",
                        'header' => "Authorization: Basic " . base64_encode("$username:$password")
              )
        );
        $ctx = stream_context_create($params);
        $url = "http://$host/dev/gpioCtrl.cgi";
        $data = file_get_contents($url, false, $ctx);
	
	preg_match("@(<out1>)(.+?)(<\/out1>)@i", $data, $matches);
	if ( $matches[2] == "off" ) {
		$status = 0;
	} else if ( $matches[2] == "on" ) {
		$status = 1;
	};
	statuslog ("Reading outside lights status '$status'");

    return $status;
}

function statuslog ($message) {
	global $logfile;
	$datestamp = date("m.d.Y H:i:s");
	$client = $_SERVER['REMOTE_ADDR'];
	$statusline = "<span class='datestamp'>$datestamp:</span> $message";
	
	if (file_exists ( $logfile ) ) {
		$lines = explode("\n", file_get_contents($logfile) );
		if ( count($lines) >= 17000 ) {
			$drop = array_shift($lines);
		}
		array_push($lines, $statusline);
		$data = implode("\n", $lines);
		file_put_contents($logfile, $data, LOCK_EX);	
	} else {
		file_put_contents($logfile, $statusline, LOCK_EX);
	}
}

function isDark () {
	global $sunsets;
	$month = date("n");
	$day = date("j");
	$year = date("Y");
	$sunsetdata = explode("\n", file_get_contents($sunsets) );
	foreach ($sunsetdata as &$daydata) {
		list($linedate, $sunrise, $zenith, $sunset, $daylength) = explode(";", $daydata);
		if ( "$day.$month" === $linedate ){
			break;
		}
	}
	$timestamp_current = time();
	$timestamp_sunrise = strtotime("$year-$month-$day $sunrise");
	$timestamp_sunset = strtotime("$year-$month-$day $sunset");
	if ($timestamp_current > $timestamp_sunrise && $timestamp_current < $timestamp_sunset ) {
		$darktime = 0;
	} else {
		$darktime = 1;
	}
	statuslog ("Sunrise at $timestamp_sunrise, sunset at $timestamp_sunset. Now $timestamp_current. Darktime is '$darktime'");
	return $darktime;
}

function getSNMP ($sensorid,$host,$oid,$community) {
	$session = new SNMP(SNMP::VERSION_1, $host, $community);
	$data = $session->get($oid);
	preg_match("@:\s(\d+)@i", $data, $matches);
	$readstatus = $matches[1];
	$sens = statuslog($sensorid." is currently ".$readstatus);
	return "$readstatus";
}


?>


