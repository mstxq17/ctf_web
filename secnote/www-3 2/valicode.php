<?php
session_start();
error_reporting(0);

$oplist = Array('+', '-', '*');
srand();
$a = rand(1, 20);
$b = rand(1, 20);
$op = $oplist[array_rand($oplist)];
$text = $a . $op . $b;
eval('$result = ' . $text . ';');
$_SESSION['answer'] = $result;

header("Content-type: image/png");
$font = 5;
$width1 = imagefontwidth($font)*strlen($text);
$height1 = imagefontheight($font);
$img1 = imagecreate($width1, $height1);
$width = 10*5;
$height = 20;
$img = imagecreate($width, $height);
$bkcolor = imagecolorallocate($img1, 255, 255, 255);
$color = imagecolorallocate($img1, 0, 0, 0);
imagestring($img1, $font, 0, 0, $text, $color);
for ($i = 0; $i < rand(3, 5); $i++) {
	imageline($img1, rand(0, $width1 - 1), rand(0, $height1 - 1), rand(0, $width1 - 1), rand(0, $height1 - 1), $color);
}
imagecopyresized($img, $img1, 5 * (5 - strlen($text)), 0, 0, 0, 10 * strlen($text), $height, $width1, $height1);
imagedestroy($img1);
imagepng($img);
imagedestroy($img);
?>