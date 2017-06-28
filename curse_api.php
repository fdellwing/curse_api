<?php
// Return application/json
header('Content-Type: application/json;charset=utf-8');

// Get the name of the addon
$addon  = $_GET['addon'];

// Only a-Z, 0-9 and - are allowed as name
if (!preg_match("/^[a-zA-Z0-9\-]+$/",$addon)) {
	print_r(json_encode(["monthly"=>"busted","total"=>"busted"]));
	die();
}
 // Search for a cache file for this addon
$cache = glob("./cache/".$addon.".*");

// If there is one
if (!empty($cache)) {
	// Take the time of the cache and add 30 minutes
	$time = explode(".", $cache[0])[2] + 1800;
	// If the cache is older than 30 minutes
	if ($time < time()) {
		// Delete the cache
		unlink($cache[0]);
		$cache = [];
	}
}

// If there is no cache
if (empty($cache)) {
	// Do some curl magic
	$url = "https://mods.curse.com/addons/wow/".$addon;
	$req = curl_init();
	curl_setopt($req, CURLOPT_URL, $url);
	curl_setopt($req, CURLOPT_FOLLOWLOCATION, true); // fuck you curse for needing this!
	curl_setopt($req, CURLOPT_RETURNTRANSFER, 1); // get the content as string
	$reply = curl_exec($req);
	curl_close($req);
	// Do some string manipulation to get the download counts
	$needle = '/[0-9\,]+ Monthly Downloads/';
	preg_match($needle, $reply, $matches);
	$monthly = explode(" ", $matches[0])[0];
	$needle = '/[0-9\,]+ Total Downloads/';
	preg_match($needle, $reply, $matches);
	$total = explode(" ", $matches[0])[0];
	// Write the JSON to cache
	file_put_contents("./cache/".$addon.".".time(),json_encode(["monthly"=>$monthly,"total"=>$total]),LOCK_EX);
}
else { // If there is cache
	// Get the cache as string
	$reply = file_get_contents($cache[0]);
	// JSON -> Array
	$array = json_decode($reply,true);
	// Get the download counts
	$monthly = $array["monthly"];
	$total = $array["total"];
}

// Output the JSON
print_r(json_encode(["monthly"=>$monthly,"total"=>$total]));
?>
