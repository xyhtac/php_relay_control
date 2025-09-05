<?php
# Web interface for USR-IOT-R16 16-channel electric relay board
# Max.Fischer dev@monologic.ru

$logfile = "actions.log";
$controller_ip = '__BmsControllerIP__';
$controller_pass = '__BmsControllerPassword__';
$controller_port = '8899';
$log_depth = 17000; 

# Get params from query string
parse_str($_SERVER['QUERY_STRING'], $params);

# Create TCP socket
if (!($sock = socket_create(AF_INET, SOCK_STREAM, 0))) {
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

# Connect socket to I/O controller
if(!socket_connect($sock , $controller_ip , $controller_port)) {
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    die("Could not connect: [$errorcode] $errormsg \n");
} 


# Step 1. Toggle channel state if toggle_channel param specified
if ( $params['toggle_channel']  ) {
	
	# make sure $input_channel is a string.
	$input_channel = strval($params['toggle_channel']);
	
	# make sure channel number is within 1-16 range
	if ( $input_channel < 1 ) {  
		$input_channel = 1;
	}
	if ( $input_channel > 16 ) {
		$input_channel = 16;
	}
	
	# get array of channel states from controller
	$channel_state = get_channel_state($controller_pass, $sock);
	
	# get state of requested channel
	$source_state = $channel_state[ $input_channel - 1];
	
	# select the appropriate command byte to flip channel state
	if ($source_state == 0)  {
		$target_state = "\x02";
		$new_state = 1;
	} elseif ($source_state == 1) {
		$target_state = "\x01";
		$new_state = 0;
	}
	$hex_chan = chr($input_channel);
	
	# compose the control datagram to set new channel state
	$control_str = "\x55\xAA\x00\x03\x00".$target_state.$hex_chan."\x06";
	
	# send control datagram to controller using comm function
	$status = inject ($control_str, 8,  $sock);
	
	# write performed action to the log file
	statuslog ($input_channel, $source_state, $new_state);
};

# Step 2. List all current channel state to JS array relay_channel

# Get array of channel states from controller. Overwrite $channel_state array.
$channel_state = get_channel_state($controller_pass, $sock);

echo "var relay_channel = new Array(); \n";
for ($channel=0; $channel < count($channel_state); $channel++) {
	$state = $channel_state[$channel];
	echo "relay_channel[$channel] = $state;\n";
}

socket_close ($sock);
exit();


function get_channel_state ($controller_pass, $sock) {
	
	# send auth packet to the controller with comm function
	inject ($controller_pass."\x0D\x0A", 2, $sock);
	
	# request controller status information with comm function
	$status = inject ("\x55\xAA\x00\x03\x00\x0A\x00\x06", 8,  $sock);
	
	# get 4 last bytes of the second line of the controller report
	$hex_status = substr($status[1], -4, 4);
	$bin_status = "";  

	# split channel status string (hex) in two blocks, 2 bytes each
	$block_one = substr($hex_status, 0, 2);
	$block_two = substr($hex_status,2, 2);
	
	# convert blocks to binary
	$block_one = base_convert($block_one, 16,2);
	$block_two = base_convert($block_two, 16,2);
	
	# add leading zeroes to blocks to fill up 8 bits
	while (strlen($block_one)<8) {
		$block_one = "0".$block_one;
	}
	while (strlen($block_two)<8) {
		$block_two = "0".$block_two;
	}
	
	# reverse chars in blocks
	$block_one = strrev( $block_one );
	$block_two = strrev( $block_two );
	
	# compose result binary status 
	$bin_status = $block_one.$block_two;
	
	# generate $channel_state array using binary string, 1 bit per channel.
	for ($b=0; $b < strlen($bin_status); $b++) {
		$channel_state[$b] = substr( $bin_status, $b, 1);
	}
	
	return $channel_state;
}

# I/O Controller TCP comm function.
function inject ($message, $anslen, $sock) {
  # Send data ($message)
  if( ! socket_send ( $sock , $message , strlen($message), 0)) {
     $errorcode = socket_last_error();
     $errormsg = socket_strerror($errorcode);
     die("Could not send data: [$errorcode] $errormsg \n");
  } 
  
  # Receive data (to $buf)
  if(socket_recv ( $sock , $buf , $anslen , MSG_WAITALL ) === FALSE) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);
      die("Could not receive data: [$errorcode] $errormsg \n");
  }
  return unpack("H*", $buf);
};

# string to Hexadecimal convert function
function strToHex ($string) {
    $hex='';
    for ($i=0; $i < strlen($string); $i++) {
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
};


# Action logging function. 
# Save old status, new status, source IP and timestamp.
function statuslog ($chanid, $oldstatus, $newstatus) {
	global $logfile;
	global $log_depth;
	$datestamp = date("m.d.Y H:i:s");
	$client = $_SERVER['REMOTE_ADDR'];
	$statusline = "<span class='datestamp'>$datestamp:</span> Relay '$chanid' toggled from '$oldstatus' to '$newstatus' by '$client'";
	
	if (file_exists ( $logfile ) ) {
		$lines = explode("\n", file_get_contents($logfile) );
		if ( count($lines) >= $log_depth ) {
			$drop = array_shift($lines);
		}
		array_push($lines, $statusline);
		$data = implode("\n", $lines);
		file_put_contents($logfile, $data, LOCK_EX);	
	} else {
		file_put_contents($logfile, $statusline, LOCK_EX);
	}
} 

?>
