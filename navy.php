<?php
// @TODO - move to a database so this is secure... ;-)
$user = [
	'guid'      => 1,
	'name'      => 'Bobby',
	'sname'     => 'DeVeaux',
	'height'    => 180,
	'lifestyle' => 'lchf',
	'activity'  => 'low',
	'deficit'   => 'difficult',
	'mobile'    => '+447584900848',
	'height'    => 180*0.393701,
	'naval'     => 32.5,
	'neck'      => 15.5
];

$fat = 86.010 * log10($user['naval']-$user['neck']) - 70.041*log10($user['height'])+36.76;

echo $fat;