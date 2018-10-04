<?php

// @TODO - move to a database so this is secure... ;-)
$userList[1] = [
	'guid'      => 1,
	'name'      => 'Bobby',
	'sname'     => 'DeVeaux',
	'height'    => (71*2.54),
	'lifestyle' => 'lchf',
	'activity'  => 'low',
	'deficit'   => 'medium',
	'mobile'    => '+447584900848',
	'naval'     => 31,
	'neck'      => 15
];

$userList[2] = [
	'guid'      => 2,
	'name'      => 'Stephanie',
	'sname'     => 'DeVeaux',
	'height'    => 165.10,
	'lifestyle' => 'lchf',
	'activity'  => 'low',
	'deficit'   => 'easy',
	'mobile'    => '+447833492482',
];

$twilio['account'] = getenv('TWILIO_ACCOUNT');
$twilio['user'] = getenv('TWILIO_USER');
$twilio['auth'] = getenv('TWILIO_AUTH');

$userId = $_GET['userid'];

$user = $userList[$userId];

$mass     = $_GET['mass'] ? $_GET['mass'] : 0;
$bodyfat  = $_GET['bf'] ? $_GET['bf'] : 0;
$user['lifestyle'] = $_GET['ls'] ? $_GET['ls'] : $user['lifestyle'];
$height   = $user['height'];
$lifestyle = $user['lifestyle'];
$activity = $user['activity'];
$deficit  = $user['deficit'];

if ($mass == 0 || $bodyfat == 0 || $height == 0) {
	print "Mass / bodyfat / height must be > 0";
	echo '<a href="/?bf=14&mass=75&userid=1">Example</a>';
	exit;
}

putenv("DEBUG=true");

$calc['navy']       = 86.010 * log10($user['naval']-$user['neck']) - 70.041*log10($user['height']/2.54)+36.76;
$calc['navy']       = round($calc['navy'],2);
$calc['bodyfat']    = $bodyfat;
$calc['averagefat'] = round(($calc['navy']+$calc['bodyfat'])/2, 2);

$calc['fatmass']  = round($mass*($calc['averagefat']/100),2);
$calc['leanmass'] = round($mass-$calc['fatmass'],2);
$calc['bmr']      = round(370 + (21.6*$calc['leanmass']),2);

switch ($activity) {
	default;
	case "low":
		$activityFactor = 1.2;
		break;
	case "light":
		$activityFactor = 1.375;
		break;
	case "moderate":
		$activityFactor = 1.55;
		break;
	case "very":
		$activityFactor = 1.725;
		break;
	case "extreme":
		$activityFactor = 1.9;
		break;

}

switch ($deficit) {
	default;
	case "leangains":
		$deficitGoal = 1.2;
		break;
	case "slow":
		$deficitGoal = 0.95;
		break;
	case "easy":
		$deficitGoal = 0.90;
		break;
	case "medium":
		$deficitGoal = 0.85;
		break;
	case "hard":
		$deficitGoal = 0.80;
		break;
	case "difficult":
		$deficitGoal = 0.75;
		break;
}

$calc['tdee']         = round($calc['bmr']*$activityFactor,0);
$calc['calorie_goal'] = round($calc['tdee']*$deficitGoal,0);
$calc['protein_goal'] = round($mass*2.2, 2);
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


//print_r($calc);

$output = json_encode(['calcs' => $calc, 'user' => $user]);

print $output;

$message = formatMessage($user, $calc);

var_dump(nl2br($message));

$send = getenv("SEND_MESSAGE");
if (getenv('SEND_MESSAGE') == 'true') {
	$response = sendMessage($user, $twilio, $message);
}

function formatMessage($user, $calc) {
	$message = "\n";
	$message .= $user['name'] . "!\n";
	$message .= "Weight: " . $calc['mass'] . "kg\n";
	$message .= "BIA Fat: " . $calc['bodyfat'] . "%\n";
	$message .= "Navy Fat: " . $calc['navy'] . "%\n";
	$message .= "Avg Fat: " . $calc['averagefat'] . "%\n";
	$message .= "BMR: " . $calc['bmr'] . "kcal!\n";
	$message .= "Activity Level: " . $user['activity'] . "\n";
	$message .= "TDEE: " . $calc['tdee'] . "kcal!\n";
	$message .= "Diet Choice: " . $user['lifestyle'] . "\n";
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