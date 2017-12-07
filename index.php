<?php

$twilio['account'] = getenv('TWILIO_ACCOUNT');
$twilio['user'] = getenv('TWILIO_USER');
$twilio['auth'] = getenv('TWILIO_AUTH');

$mass     = $_GET['mass'];
$bodyfat  = $_GET['bf'];
$kg       = $_GET['kg'];
$lifesyle = "lchf";
$activity = "low";
$deficit  = "moderate";

$calc['fatmass']  = round($mass*($bodyfat/100),2);
$calc['leanmass'] = round($mass-$calc['fatmass'],2);
$calc['bmr']      = round(370 + (21.6*$calc['leanmass']),2);

switch ($activity) {
	default;
	case "low":
		$activityFactor = 1.2;
}

$calc['tdee'] = round($calc['bmr']*$activityFactor,2);

$calc['calorie_goal'] = round($calc['tdee']*0.75,0);

$calc['protein_goal'] = round($mass*2, 2);
$calc['carb_goal']    = 60;
$calc['protein_cals'] = round($calc['protein_goal']*4, 2);
$calc['carb_cals']    = round($calc['carb_goal']*4, 2);
$calc['fat_cals']     = round($calc['calorie_goal'] - $calc['protein_cals'] - $calc['carb_cals'], 2);
$calc['fat_goal']     = round($calc['fat_cals'] / 9, 2);

//print_r($calc);

$output = json_encode($calc);

print $output;

$data['To']   = '+447584900848';
$data['From'] = '+441683292010';
$data['Body'] = "Bobby!\nCalorie goal today is " . $calc['calorie_goal'] . "kcal!\nProtein Goal: " . $calc['protein_goal'] . "\nBody Fat: " . $bodyfat . "%\nWeight: " . $mass . "kg\nGood luck fatty!";
$ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/". $twilio['account'] . "/Messages.json?Body=$body&To=$to&From=$from");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, $twilio['user'].":" . $twilio['auth']);
$output = curl_exec($ch);       
curl_close($ch);

if (getenv("DEBUG") == "true") {
	echo $output;	
}


