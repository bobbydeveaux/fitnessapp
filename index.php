<?php

$user = [
	'guid' => '1s1d1d1fwf2ff3g4v4g4',
	'name' => 'Bobby',
	'sname' => 'DeVeaux',
	'age'   => 32,
	'height' => 180,
	'lifestyle' => 'lchf',
	'activity'  => 'low',
	'deficit'   => 'moderate',
	'mobile'    => '+447584900848',
];

$twilio['account'] = getenv('TWILIO_ACCOUNT');
$twilio['user'] = getenv('TWILIO_USER');
$twilio['auth'] = getenv('TWILIO_AUTH');

$mass     = $_GET['mass'] ? $_GET['mass'] : 0;
$bodyfat  = $_GET['bf'] ? $_GET['bf'] : 0;
$user['lifestyle'] = $_GET['ls'] ? $_GET['ls'] : $user['lifestyle'];
$height   = $user['height'];
$lifestyle = $user['lifestyle'];
$activity = $user['activity'];
$deficit  = $user['deficit'];

if ($mass == 0 || $bodyfat == 0 || $height == 0) {
	print "Mass / bodyfat / height must be > 0";
	exit;
}

putenv("DEBUG=true");


$calc['fatmass']  = round($mass*($bodyfat/100),2);
$calc['leanmass'] = round($mass-$calc['fatmass'],2);
$calc['bmr']      = round(370 + (21.6*$calc['leanmass']),2);

switch ($activity) {
	default;
	case "low":
		$activityFactor = 1.2;
}

switch ($deficit) {
	default;
	case "moderate":
		$deficitGoal = 0.75;
}

$calc['tdee']         = round($calc['bmr']*$activityFactor,2);
$calc['calorie_goal'] = round($calc['tdee']*$deficitGoal,0);
$calc['protein_goal'] = round($mass*2, 2);
$calc['protein_cals'] = round($calc['protein_goal']*4, 2);

switch ($lifestyle) {
	default;
	case "lchf":
		$calc['carb_goal'] = 30;
		break;
	case "lfhc":
		// @TODO needs to be a percentage or minus fat cals
		// this may cause higher calorie than permitted, or create negative fat.
		$calc['carb_goal'] = 180;
		break;
}

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
	$message .= "Carb goal: " . $calc['carb_goal'] . "g\n";
	$message .= "Fat goal: " . $calc['fat_goal'] . "g\n";
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



