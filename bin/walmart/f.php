
<?php
# Might consider using this instead:
# https://serpapi.com/walmart-search-api


function dn()
	{
	$as = func_get_args();
	print_r($as);
	die(1);
	}

class ScrapeWalmart
{

function error($msg)
	{
	echo $msg;
	return 1;
	}

function getCookieCrumb($url)
	{
	// let's get a cookie first!
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$x = curl_exec($ch);
	$match="/^set-cookie:\s*(crumb=\S+)/m";
	if (!preg_match($match, $x, $r))
		die("Unable to get cookie crumb");
	$crum = $r[1];
	return $crum;
	}


function getWalmartResults($in, $ignored)
	{
	$url = "https://www.walmart.com/store/electrode/api/search";
	if (!$crumb = $this->getCookieCrumb($url))
		return false;

	$full = "$url?".http_build_query($in);
	$ch = curl_init($full);
# This was a full cookie from my browser that made good stuff come out.
#'cookie: adblocked=false; AID=wmlspartner%3D0%3Areflectorid%3D0000000000000000000000%3Alastupd%3D1646520581561; ACID=5f41665e-e36e-41ae-8bc1-a71094ae078d; hasACID=true; hasLocData=1; vtc=REEYtazIt8GbAW5rTNETpI; _pxvid=8c398b7e-9cd6-11ec-87d5-4c4d51554d7a; TBV=7; adblocked=false; dimensionData=740; locGuestData=eyJpbnRlbnQiOiJTSElQUElORyIsInN0b3JlSW50ZW50IjoiUElDS1VQIiwibWVyZ2VGbGFnIjpmYWxzZSwiaXNEZWZhdWx0ZWQiOmZhbHNlLCJwaWNrdXAiOnsibm9kZUlkIjoiMzY1MiIsInRpbWVzdGFtcCI6MTY0NjUyMDU4MzkxMX0sInBvc3RhbENvZGUiOnsidGltZXN0YW1wIjoxNjQ2NTIwNTgzOTExLCJiYXNlIjoiOTU2MTgifSwidmFsaWRhdGVLZXkiOiJwcm9kOnYyOjVmNDE2NjVlLWUzNmUtNDFhZS04YmMxLWE3MTA5NGFlMDc4ZCJ9; tb_sw_supported=true; location-data=95618:Davis:CA::1:1|2tg;;7.22,1wg;;9.6,3yr;;9.71,206;;11.24,4bq;;11.57,23z;;11.84,1ou;;11.85,36n;;13.41,4m6;;15.21,2dl;;15.46||7|1|1yjc;16;0;5.17,1yff;16;1;6.47,1xkz;16;3;8.68,1xj6;16;5;9.63,1xlk;16;7;10.56; DL=95618,,,ip,95618,,; TB_Latency_Tracker_100=1; TB_Navigation_Preload_01=1; TB_SFOU-100=1; bstc=R4iRTEg5F4OFDo94pkC_mo; mobileweb=0; xpa=-3Cq6|DAwQd|G-v-K|U4yzG|YgI0R|bWRKW|j2MFd|lw0Ow|t_0yR|vseyf|wOKU_; exp-ck=-3Cq61DAwQd2G-v-K1U4yzG1YgI0R1j2MFd1lw0Ow1t_0yR1vseyf1wOKU_1; TS013ed49a=01538efd7c03de3d2b4c178e0ad870e54cd168efcd689ab39c512bf67776bcd08049508765f1e3242f24695fbc35a61002eb6a1274; pxcts=4bd0cda0-a0a9-11ec-8981-756659567153; xpm=1+1646940950+REEYtazIt8GbAW5rTNETpI~+0; bm_mi=FC691127C7A2FF568DF8E3DF34AE4845~u4di1neMkG6l0Xc6m4R8jT2EteFakR6VbYiLapPJqCJLSLowBHoUNfHZG2cuehcH5ooUAwtVo46Fi6A30b1xGE91CVO6xxzMMfx3vezVP8hsdmSc3OAKMCb0KQjqtVI3w1KiB1d685d3gdw8JuLakcH4uTNQ40m1TSWhXYLDkOKW87ijm8jd+eNfLenxupckxRNk4IO95FJaPPURZxhMls/sKcCn6XzSTTG6ZUdGpxjqfV/MQxDn1ZMIQSjOWKTh9W0NrXyuwjBSBa4AKPyjkw==; __gads=ID=5f6c76d6b1d01b04:T=1646940952:S=ALNI_MbKVlKCUhQXua3eXrBfXoTsG2x5Uw; _gcl_au=1.1.74710379.1646940953; ak_bmsc=530F9EAC20CE76AE698E210E22CFBCC9~000000000000000000000000000000~YAAQjMcbuI1nHlJ/AQAAf6RUdQ8STX7M7FjVhQK/zzCH0ck24sMrsGfIt3th6cp+Yi/+M4l8bL9Nh1/DTGkKT1yLFdi7CY5D9fg4WknBKmkZewHDnoQijekI9H+htYT5ZqZ3ogp6XG5FMiKhZM6+DsRcIblM0CC8Zu5uWIkMUAx/VQfWdgeBvHrCc529djp7BnV7jD2RbVFvLMKDvSUroFk00PRa6ip5f5jkFPhUuFNvkCt7uvXICY5zp8w+HyXM4TOuHOzuUMGHLTnu9oJinDB71aruerMkptZuu4Wxmy8uTvDHX61J9oUDrOAM9p9ea8BjaIRyVrXFgIE+HtdnTIkAtIYBlRsZpLtJlRKjJUl1CImGc/LSalvDziQwpEZ2pU6QDjpWyYJ9okyZVqwut2RXXLVGtaa1INsIQBWUaysRn5suWv1pH7oJFwcxw12tL0IXCf2v8MhcB1Uz+t8jZ5bMLr+XgefsszNELCnEhpQOIAVXoHpIN2hAD1lH8pV1olVh99Ot6gLEOLQd7uiYhEaURTrR/HIiPOw8sI4jdOLX6RiDlia70hQ=; crumb=8dgO3cklffE6C3s4f8ZC5ZyWAg9-bwO0XG2MFHrh4cC; assortmentStoreId=3652; akavpau_p2=1646941582~id=88f3a219285ac95404a8f9a6ec2a2581; com.wm.reflector="reflectorid:0000000000000000000000@lastupd:1646940984000@firstcreate:1646520581561"; locDataV3=eyJpc0RlZmF1bHRlZCI6ZmFsc2UsImludGVudCI6IlNISVBQSU5HIiwicGlja3VwIjpbeyJidUlkIjoiMCIsIm5vZGVJZCI6IjM2NTIiLCJkaXNwbGF5TmFtZSI6Ildlc3QgU2FjcmFtZW50byBTdXBlcmNlbnRlciIsIm5vZGVUeXBlIjoiU1RPUkUiLCJhZGRyZXNzIjp7InBvc3RhbENvZGUiOiI5NTYwNSIsImFkZHJlc3NMaW5lMSI6Ijc1NSBSaXZlcnBvaW50IEN0IiwiY2l0eSI6Ildlc3QgU2FjcmFtZW50byIsInN0YXRlIjoiQ0EiLCJjb3VudHJ5IjoiVVMiLCJwb3N0YWxDb2RlOSI6Ijk1NjA1LTE2NTQifSwiZ2VvUG9pbnQiOnsibGF0aXR1ZGUiOjM4LjU4NzM2MiwibG9uZ2l0dWRlIjotMTIxLjU1MDA0OX0sImlzR2xhc3NFbmFibGVkIjp0cnVlLCJzY2hlZHVsZWRFbmFibGVkIjp0cnVlLCJ1blNjaGVkdWxlZEVuYWJsZWQiOnRydWUsImh1Yk5vZGVJZCI6IjM2NTIifV0sInNoaXBwaW5nQWRkcmVzcyI6eyJsYXRpdHVkZSI6MzguNTUwOCwibG9uZ2l0dWRlIjotMTIxLjcwOTIsInBvc3RhbENvZGUiOiI5NTYxOCIsImNpdHkiOiJEYXZpcyIsInN0YXRlIjoiQ0EiLCJjb3VudHJ5Q29kZSI6IlVTQSIsImdpZnRBZGRyZXNzIjpmYWxzZX0sImFzc29ydG1lbnQiOnsibm9kZUlkIjoiMzY1MiIsImRpc3BsYXlOYW1lIjoiV2VzdCBTYWNyYW1lbnRvIFN1cGVyY2VudGVyIiwiYWNjZXNzUG9pbnRzIjpudWxsLCJzdXBwb3J0ZWRBY2Nlc3NUeXBlcyI6W10sImludGVudCI6IlBJQ0tVUCIsInNjaGVkdWxlRW5hYmxlZCI6ZmFsc2V9LCJpbnN0b3JlIjpmYWxzZSwicmVmcmVzaEF0IjoxNjQ2OTYyNTgzOTA3LCJ2YWxpZGF0ZUtleSI6InByb2Q6djI6NWY0MTY2NWUtZTM2ZS00MWFlLThiYzEtYTcxMDk0YWUwNzhkIn0=; wm_client_ip=67.182.21.146; wmlh=; QuantumMetricUserID=194e63eab12bebff59c5a5b64c84f64c; xptwg=2094898232:5DDDD35F4730B0:F4CA08:83B40F43:C1011D9D:168497F:; _uetsid=4c828510a0a911eca6c16776ddb5ce6f; _uetvid=4c82d3c0a0a911ec838ef56ee071b170; TS01b0be75=01538efd7cc3d72de64ea5876f8cad2f79e88938030e04adbe6b799397419ead44afba269b656ee06738ab733acf198202b601939d; bm_sv=E5FC47613770DAEAF835AA7AA242631D~S7EdhxdVIa5oYSlJgDg1wTThyKPLMBNojQuVjpxp6MCM12X73Wkto1dO/EUASGy7dwb8GL3ryyrs4b+V5SgMxOov9sT211LC8/4nkRZOoAGrAgYqJqNg/CaU7z+2KTrN+F5cM4aMSeKyZKYc+tSmq0SHc9NABoBNH/JjDhmNMRE=',

# This is the cookie I need to get data back from the query.
	$headers = [
#		"cookie: $crumb assortmentStoreId=$in[stores];",
		"cookie: $crumb ",
		];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$x = curl_exec($ch);
	if (false)
		{
		$fp = fopen("product.cache", 'w');
		fwrite($fp, $x);
		fclose($fp);
		}
	return $x;
	}

function decode($in)
	{
	$j = json_decode($in, 1);
	return $j;
	print_r($j);
	}


function combineArr($orig, $repl)
	{
	foreach($repl AS $k => $v)
		$orig[$k]=$v;
	return $orig;
	}

}

$organicSpokane = [
	'query' => 'organic',
	'stores' => '2865',
	'cat_id' => 0,
	'ps' => 24,
	'offset' => 0,
	'prg' => 'desktop',
	'zipcode' => 99205,
	'stateOrProvinceCode' => 'WA',
	'facet' => 'fulfillment_method:In-store',
	];

$stores = [
 	'https://www.walmart.com/store/2865-spokane-wa' =>
		[
		'stores' => '2865',
		'zipcode' => 99205,
		'stateOrProvinceCode' => 'WA',
		'offset' => 0,
		'cat_id' => 976759,
		],

	];

$WM = new ScrapeWalmart();
foreach($stores AS $store => $sdata)
	{
	$arr = $WM->combineArr($organicSpokane, $sdata);
	$done = false;
	$all = [];
	while (!$done)
		{
		$in = $WM->getWalmartResults($organicSpokane, $store);
	//	$in = file_get_contents("product.cache");
		$j = $WM->Decode($in);
		$items = $j['itemStacks'][0]['items'];
		foreach($items AS $item)
			{
			$organicSpokane['offset']+=1;
			if (isset($all[$item['productId']]))
				echo "DUPLICATE FOUND!\n";
			else
				{
				echo	$item['title']."\n";
				$all[$item['productId']]=$item;
				}
			}
		sleep(5);
		}
	}
