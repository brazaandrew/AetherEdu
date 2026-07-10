<?php
// Generate a simple default avatar
header('Content-Type: image/png');

// Create a 100x100 transparent image
$image = imagecreate(100, 100);

// Allocate colors
$background = imagecolorallocate($image, 200, 200, 200); // Light gray background
$textColor = imagecolorallocate($image, 100, 100, 100);  // Dark gray text

// Fill the background
imagefill($image, 0, 0, $background);

// Draw a simple user icon (a circle for head and rectangle for body)
$circleX = 50;
$circleY = 35;
$circleRadius = 20;

// Draw head (circle)
imageellipse($image, $circleX, $circleY, $circleRadius * 2, $circleRadius * 2, $textColor);
imagefilledellipse($image, $circleX, $circleY, $circleRadius * 2, $circleRadius * 2, $background);

// Draw body (rectangle)
imagerectangle($image, 30, 55, 70, 85, $textColor);
imagefilledrectangle($image, 30, 55, 70, 85, $background);

// Output the image
imagepng($image);
imagedestroy($image);
?>