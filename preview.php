<?php
require_once 'config.php';

if (isset($_GET['fn'])) preview($_GET['fn']);

// Generate thumbnail and show
// function preview($filename = '') {
//     $original = DIR . $filename;
//     list($width, $height, $mime) = getimagesize($original);
//     $tn = imagecreatetruecolor($width / $rate, $height / $rate);
//     imagealphablending($tn, true);
//     imagesavealpha($tn, true);
//     $bgcolor = imagecolorallocatealpha($tn, 0, 0, 0, 127);
//     imagefill($tn, 0, 0, $bgcolor);
//     $image = imagecreatefrompng($original);
//     imagecopyresampled($tn, $image, 0, 0, 0, 0, $width / ZOOM, $height / ZOOM, $width, $height);

//     header ('Content-Type: image/png');
//     imagepng($tn);
//     imagedestroy($tn);
// }

