<?php
// Get the name of the addon
$addon = $_GET["addon"];

// Some curl stuff
$url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI'])."/curse_api.php?addon=".$addon;
$req = curl_init();
curl_setopt($req, CURLOPT_URL, $url);
curl_setopt($req, CURLOPT_RETURNTRANSFER, 1); // get the content as string
$reply = curl_exec($req);
curl_close($req);

// Read the monthly downloads from JSON
$monthly = json_decode($reply, true)["monthly"];

// Return the image from shields.io as SVG
header("Content-Type: image/svg+xml;charset=utf-8");
readfile('https://img.shields.io/badge/monthly-'.$monthly.'-yellow.svg');
?>
