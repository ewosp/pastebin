<?php

//Generates two numbers 
$n1 = rand(1, 10);
$n2 = rand(1, 10);

//Picks an operator + - * / (but in the best conditions)
$ops[] = '+';
if ($n1 + $n2 < 12) $ops[] = '*';
/*
if ($n1 > $n2) {
	$ops[] = '-';
	if ($n1 % $n2 == 0) $ops[] = '/';
}
*/
$op = $ops[mt_rand() % sizeof($ops)];

//Gets our problem string and the solution
$problem = "$n1 $op $n2";
session_start();
eval("\$_SESSION['solution'] = $problem;");

//Creates our picture with $problem
$im = imagecreatetruecolor(80, 24);
$textcolor = imagecolorallocate($im, 0, 0, 0); 
$bgcolor = imagecolorallocate($im, 255, 255, 0);
imagecolortransparent($im, $bgcolor);
imagefill($im, 0, 0, $bgcolor);
imagestring($im, 4, 0, 2, $problem . " = ", $textcolor);

//Prints it
header("Content-type: image/png");
imagepng($im); 
ImageDestroy($im);

?>
