<?php

$twilio['account'] = getenv('TWILIO_ACCOUNT');
$twilio['user'] = getenv('TWILIO_USER');
$twilio['auth'] = getenv('TWILIO_AUTH');

$mass     = $_GET['mass'] ? $_GET['mass'] : 0;
$bodyfat  = $_GET['bf'] ? $_GET['bf'] : 0;
$height   = $_GET['height'] ? $_GET['height'] : 0; 
$lifesyle = "lchf";
$activity = "low";
$deficit  = "moderate";

if ($mass == 0 || $bodyfat == 0 || $height == 0) {
	print "Mass / bodyfat / height must be > 0";
	exit;
}

putenv("SEND_MESSAGE=true");
putenv("DEBUG=true");



$calc['fatmass']  = round($mass*($bodyfat/100),2);
$calc['leanmass'] = round($mass-$calc['fatmass'],2);
$calc['bmr']      = round(370 + (21.6*$calc['leanmass']),2);

switch ($activity) {
	default;
	case "low":
		$activityFactor = 1.2;
}

$calc['tdee']         = round($calc['bmr']*$activityFactor,2);
$calc['calorie_goal'] = round($calc['tdee']*0.75,0);
$calc['protein_goal'] = round($mass*2, 2);
$calc['carb_goal']    = 60;
$calc['protein_cals'] = round($calc['protein_goal']*4, 2);
$calc['carb_cals']    = round($calc['carb_goal']*4, 2);
$calc['fat_cals']     = round($calc['calorie_goal'] - $calc['protein_cals'] - $calc['carb_cals'], 2);
$calc['fat_goal']     = round($calc['fat_cals'] / 9, 2);

$calc['bmi'] = round($mass/($height/100*$height/100), 2);

$calc['mass']    = $mass;
$calc['bodyfat'] = $bodyfat;
$calc['height']  = $height;

//print_r($calc);

$output = json_encode($calc);
//print $output;

$user['name'] = 'Bobby';
$user['mobile'] = '+447584900848';

$message = formatMessage($user, $calc);

echo $message;

$send = getenv("SEND_MESSAGE");
if (getenv('SEND_MESSAGE') == 'true') {
	$response = sendMessage($user, $twilio, $message);
}

function formatMessage($user, $calc) {
	$message = "\n";
	$message .= $user['name'] . "!\n";
	$message .= "Weight: " . $calc['mass'] . "kg\n";
	$message .= "Fat: " . $calc['bodyfat'] . "%\n";
	$message .= "Calorie goal: " . $calc['calorie_goal'] . "kcal!\n";
	$message .= "Protein goal: " . $calc['protein_goal'] . "g\n";
	$message .= "BMI: " . $calc['bmi'] . "\n";
	$message .= "Good luck fatty!";

	return $message;
}


function sendMessage($user, $twilio, $message) {

	if (getenv("DEBUG") == "true") {
		print "Sending..";
	}

	$data['To']   = $user['mobile'];
	$data['From'] = '+441683292010';
	$data['Body'] = $message;
	$ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/". 
		$twilio['account'] . "/Messages.json");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERPWD, $twilio['user'].":" . $twilio['auth']);
	$output = curl_exec($ch);       
	curl_close($ch);

	if (getenv("DEBUG") == "true") {
		print $output;	
	}

	return $output;
}



