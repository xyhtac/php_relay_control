
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Remote Device Control</title>
<meta name="description" content="Remote control panel" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- stylesheets -->	
<link href="css/jquery-nicelabel.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="css/font-awesome.min.css">
<style>
@font-face {
    font-family: 'MagicMedium';
    src:url('fonts/PFDinDisplayPro-Med.eot?') format('eot'),
        url('fonts/PFDinDisplayPro-Med.ttf') format('truetype'),
        url('fonts/PFDinDisplayPro-Med.woff') format('woff'),
        url('fonts/PFDinDisplayPro-Med.svg#PFDinDisplayProMedium') format('svg');
    font-weight: normal;
    font-style: normal;
}

@font-face {
    font-family: 'MagicBold';
    src: url('fonts/PFDinDisplayPro-Bold.eot?') format('eot'),
        url('fonts/PFDinDisplayPro-Bold.ttf') format('truetype'),
        url('fonts/PFDinDisplayPro-Bold.woff') format('woff'),
        url('fonts/PFDinDisplayPro-Bold.svg#PFDinDisplayProBold') format('svg');
    font-weight: normal;
    font-style: normal;
}

.io-control-label {
	color: #FFFFFF;
	font-size: 18px;
	float: left;
	padding: 5px;
	padding-top:12px;
}
.io-control-icon {
	color: #FFFFFF;
	font-size: 25px;
	float: left;
	padding: 8px;
	padding-left: 15px;
}
.io-switch {
  padding-right: 10px; 
  width: 80px;
}
.io-control-container {
	text-align: left;
	vertical-align: middle;
    position: relative;
	background-color: #303030;
	border-radius: 30px;
	padding: 3px;
	height: 50px;
	margin: 10px;
	width: 95%;
}
.io-custom-action {
	width: 120px;
	height: 44px;
	background-color: #0e8cbf;
	border-radius: 30px;
	cursor: pointer;
}
.io-custom-action:hover {
	background-color: #28b2ea;
}

.custom-action-label {
	color: #FFFFFF;
	font-weight: normal;
	font-size: 22px;
	padding-top: 8px;
	text-align: center;
}
.separator {
	height: 10px;
}

#canvas {
	text-align: center;
}
 
body { background-color:#000000; font-family:'MagicMedium';}
.container { margin:150px auto 30px auto; max-width:640px;}

		.clearfix{clear:both;}
		.rect-checkbox{float:left;margin-left:20px;}
		.rect-radio{float:left;margin-left:20px;}
		.circle-checkbox{float:left;margin-left:20px;}
		.circle-radio{float:left;margin-left:20px;}
		.text_checkbox{float:left;margin-left:20px;}
		.text_radio{float:left;margin-left:20px;}
		
</style>
	
<link rel="stylesheet" href="css/jquery-confirm.min.css">
<!-- End of stylesheets -->

<!-- JS -->

<script src="js/jquery.min.js"></script>
<script src="js/jquery.nicelabel.js"></script>
<script src="js/jquery-confirm.min.js"></script>
<script language="javascript" src="io.php"></script>
<script>
$(function(){	
		$('.io-switch > input').nicelabel();
		panel_update();
		
		$( '.io-custom-action' ).click( function( index, element ){
			script_url = $( this ).attr('io-custom-script');
			item_caption = $(this).closest('table').find('.io-control-label').html();
			custom_action = $(this).closest('table').find('.custom-action-label').html();
			item_icon = $(this).closest('table').find('.io-control-icon i').attr("class");
			msg_head = custom_action + " " + item_caption + "?";
			msg_text = "Do you want to " + custom_action + " " + item_caption + " now? This action cannot be undone.";
			msg_color = "red";
			btn_class = "btn-red";
			msg_status = custom_action;
			
			$.confirm({
				title: msg_head,
				content: msg_text,
				icon: item_icon,
				type: msg_color,
                animation: 'zoom',
                closeAnimation: 'zoom',
				buttons: {
					confirm: {
						text: msg_status,
						btnClass: btn_class,
						action: function () {
							url = script_url;
							top.frames["cmd"].location.href  = url;
						}
					},
					cancel: {
						text: 'Cancel',
						action: function () {
							panel_update();
						}
					}
				}
			});
		});
		
		$( '.io-switch > input' ).change( function( index, element ){
			chan_id = $( this ).attr('io-relay-channel');
			chan_status = relay_channel[ chan_id - 1 ];
			
			toggle_invert = $( this ).attr('io-relay-invert');
			if (toggle_invert == "true") { 
				logic_base = 1;
			} else {
				logic_base = 0;
			}
			
			if (chan_status == logic_base) {
				msg_status = "Disable";
				msg_color = "red";
				btn_class = "btn-red";
				new_status = 1;
			} else {
				msg_status = "Enable";
				msg_color = "green";
				btn_class = "btn-green";
				new_status = 0;
			}
			item_caption = $(this).closest('table').find('.io-control-label').html();
			item_icon = $(this).closest('table').find('.io-control-icon i').attr("class");
			msg_head = msg_status + item_caption + "?";
			msg_text = item_caption + " will be " + msg_status + "d now. Are you sure?";
			
			$.confirm({
				title: msg_head,
				content: msg_text,
				icon: item_icon,
				type: msg_color,
                animation: 'zoom',
                closeAnimation: 'zoom',
				buttons: {
					confirm: {
						text: msg_status,
						btnClass: btn_class,
						action: function () {
							url = "io.php?toggle_channel=" + chan_id;
							top.frames["cmd"].location.href  = url;
							relay_channel[ chan_id - 1 ] = new_status;
						}
					},
					cancel: {
						text: 'Cancel',
						action: function () {
							panel_update();
						}
					}
				}
			});
		});
});

function panel_update ( ) {
		$( '.io-switch > input' ).each( function( index, element ){
			chan_id = $( this ).attr('io-relay-channel') - 1;
			toggle_invert = $( this ).attr('io-relay-invert');
			
			if (toggle_invert == "true") {
				if (relay_channel[ chan_id ] == 1 ) {
					$( this ).attr('checked',true);
				} else {
					$( this ).attr('checked',false);
				}
			} else {
				if (relay_channel[ chan_id ] == 1 ) {
					$( this ).attr('checked',false);
				} else {
					$( this ).attr('checked',true);
				}
			}
		});	
}
</script>
<!-- End of JS  -->


</head>

<body>
<div id="canvas">

    <table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-refresh" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Recuperation pump</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='16' />
		</div>
	</tr></td>
	</table>

    <table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-envira" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Irrigation</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='15' io-relay-invert='true' />
		</div>
	</tr></td>
	</table>
	
    <table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-tint" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Well Pump</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='14'/>
		</div>
	</tr></td>
	</table>
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-bitbucket" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Drain Pump</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='13'/>
		</div>
	</tr></td>
	</table>
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-thermometer-half" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Heating 2nd Floor</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='12'/>
		</div>
	</tr></td>
	</table>
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-thermometer-half" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Heating 1st Floor</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='9'/>
		</div>
	</tr></td>
	</table>
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-shower" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Hot water</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='11'/>
		</div>
	</tr></td>
	</table>
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-spinner" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Main Ventilation</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='1' io-relay-invert='true' />
		</div>
	</tr></td>
	</table>
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-spinner" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Garage Ventilation</div>
    </td><td align="right">
		<div class="io-switch">
			<input class="circle-nicelabel" data-nicelabel='{"position_class": "circle-checkbox"}'  type="checkbox" io-relay-channel='8' io-relay-invert='true' />
		</div>
	</tr></td>
	</table>
	
	<div class="separator"></div>
	
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-lock" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Main Gate</div>
    </td><td align="right">
		<div class="io-custom-action" io-custom-script='http://10.0.10.29/scripts/door-unlock.php?door_id=6'>
			<div class="custom-action-label">activate</div>
		</div>
	</tr></td>
	</table>
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-lock" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Garage gate</div>
    </td><td align="right">
		<div class="io-custom-action" io-custom-script='http://10.0.10.29/scripts/door-unlock.php?door_id=4'>
			<div class="custom-action-label">activate</div>
		</div>
	</tr></td>
	</table>
	
	<table class="io-control-container">
    <tr><td>
    	<div class="io-control-icon"><i class="fa fa-lock" aria-hidden="true"></i></div>
    	<div class="io-control-label"> Facade gate</div>
    </td><td align="right">
		<div class="io-custom-action" io-custom-script='http://10.0.10.29/scripts/door-unlock.php?door_id=2'>
			<div class="custom-action-label">unlock</div>
		</div>
	</tr></td>
	</table>
	
</div>


<iframe src="io.php" name="cmd" width="0px" height="0px" align="center" border="0">frames disabled</iframe>

</body>
</html>
