<?php
// Perco Door Unlock Automator v.1.0
// Max.Fischer dev@monologic.ru

$logfile = "./log/actions.log";
$client = $_SERVER['REMOTE_ADDR'];

require_once __DIR__ . "/config.php";
$cred = load_credentials("door-controller");

$host = $cred["hostname"];
$username = $cred["username"];
$password = $cred["password"];


# get data from QUERY
parse_str($_SERVER['QUERY_STRING'], $params);
$door_id = $params['door_id'];

statuslog ("Door #".$door_id." unlocked by '$client'");

$status_file = "";

$status_file .= sendCommand ("setreadnum.cgi?file=managing-a-reader.html&num=".$door_id);
sleep (1);
$status_file .= sendCommand ("managing_a_reader.cgi?mode=0");
sleep (2);
$status_file .= sendCommand ("setreadnum.cgi?file=managing-a-reader.html&num=".$door_id);
sleep (1);
$status_file .= sendCommand ("managing_a_reader.cgi?mode=1");
sleep (1);
echo $status_file;



function sendCommand ($cmd) {
	global $host;
	global $username;
	global $password;
	$readstatus =  "";
        ini_set('default_socket_timeout', 4);
        $params = array(
                'http' => array(
                        'method' => "GET",
                        'header' => "Authorization: Basic " . base64_encode("$username:$password")
              )
        );
        $ctx = stream_context_create($params);
        $url = "http://$host/$cmd";
        $data = file_get_contents($url, false, $ctx);
}

function statuslog ($message) {
	global $logfile;
	$datestamp = date("m.d.Y H:i:s");
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

exit();
?>
