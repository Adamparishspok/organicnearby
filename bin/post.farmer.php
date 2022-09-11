<?php

function sendRecord($sub='version-test')
{
//$endpoint = "https://myorganicsnearby.bubbleapps.io/$sub/api/1.1/wf/createfarm/initialize";
$endpoint = "https://myorganicsnearby.bubbleapps.io/$sub/api/1.1/wf/createfarm";

/** 

*/
// got the "farm" from farm's "_id" field
$body = '{
"address" : "4005 Cowell Blvd, Davis, CA 95618",
"company email" : "ima@bogusfarms.com",
"company name" : "We Farm and stuff",
"general info" : "General info 3",
"phone" : "1.234.234.5678",
"website" : "https://bogusfarms.com",
"externalId" : "bogusfarmsid.123abc",
"scraped" : "true",
"trashed" : "false"
}';


#"address" : "38.573234065882495, -121.64579000965489",
// myProducts
// "logo" : "",
// trashed
// "verified" : 

echo "Endpoint\n$endpoint\n";
echo "Sending\n$body\n";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
	'Content-type: application/json',
	]);
$return = curl_exec($ch);
echo "Bubble platform response:\n";
print_r($return);
}


function getFarms()
	{
	$endpoint = "https://organicnearby.com/version-test/api/1.1/obj/farm";
	$body = '';
	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_HTTPGET, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-type: application/json',
		]);
	$return = curl_exec($ch);
	print_r($return);
	}

//die(getFarms());
sendRecord();
