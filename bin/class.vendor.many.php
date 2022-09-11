<?php

# https://thefarmersmarketplace.localfoodmarketplace.com/Products
class many
{

/**
 * How long to sleep after making a call to the website.
 * @var type
 */
const sleep = 5;

public static function vendor()
	{
	$filter = [
		"subPeriodId" => '',	# $("#curSubPeriodId").val();
    "listType" => '',			# $("#listType").val();
		"catId" => '',				# $("#catId").val();
		"searchString" => '',	# $("#searchString").val();
		"sortBy" =>	'',				# $("#sortBy").val();
		"producerList" => '', # getProducerFilter();
		"attrList" => '',			# getAttrFilter();
		"callingPage" => "Products",
		];
	$filter_str = json_encode($filter);
	self::domain($domain, $sub);
	$url = "https://$domain/api/ProductApi/getProducts";
	$file = "cache/$sub.products.cache";
	if (!$json = getCachedContent($file))
		if (!$json = getContentsJson($url, $filter_str))
			die("Unable to get products content");
		else
			cacheContent($file, $json);
	$arr = json_decode($json, true);
	$ts = time();
	$pcount = 0;
	foreach($arr AS $p)
		{
		$pcount++;
		// get the linkCart
		$x = str_get_html($p['productLink']);
		foreach($x->find('a') AS $a)
			$linkcart = $a->href;
		unset($x);
		if (substr($linkcart, 0, 2) == '//')
			$linkcart = "https:$linkcart";

		$price = $p['units'][0];

		$prod = [
			'name' => $p['prName'],
			'price' => self::guessPrice($price),
			'address' => '',
			'description' => $p['prTagline'],
			'stock' => $price['remaining'],
			'update_ts' => time(),
			'image' => '',
			'pricetxt' => $price['unitDesc'] ? $price['unitDesc'] : $price['unitFullName'],
			'price_unit' => $price['unitPrice'] ? $price['unitPrice'] : $price['unitName'],
			'linkcart' => $linkcart,
			'externalid' => $p['uniqueId'],
			];
		echo "Updating $pcount $prod[name]...\n";
		if (is_null($prod['description']))
			$prod['description'] = $prod['name'];
		self::getImage($p, $prod['image']);
		if (!$farmers_id = self::resolveGrower($p, $ts))
			dn("Unable to set grower", $prod);
		if (!\setProduct($prod, $ts, $farmers_id))
			dn("Unable to set product", $prod);
		echo "Updated $pcount $prod[name]...\n";
		}
	echo "$pcount products updated\n";
	}

static function domain(&$domain, &$sub)
	{
	if (!isset($_SERVER['argv'][3]))
		die("Domain required! EG: acornwholesalenetwork.localfoodmarketplace.com");
	$domain = $_SERVER['argv'][3];
	$sub = explode(".", $domain)[0];
	return true;
	}


public static function resolveGrower($p, $ts)
	{
	self::domain($domain, $sub);
	$pUid = $p['producerGuid'];
	$url = "https://$domain/Producer/$pUid";
	$file = "cache/$sub.$pUid.cache";
	if (!$txt = \getCachedContent($file))
		{
		echo "Retrieving $url\n";
		if (!$txt = file_get_contents($url))
			die("Unable to get grower $pUid");
		else
			{
			cacheContent($file, $txt);
			sleep(self::sleep);
			}
		}
	$x = str_get_html($txt);

	$img = $x->find('#producer-page-image1 img');
	$src = $image = '';
	foreach($img AS $i)
		{
		// producer images //
		#IGNORED FOR HNOW: $i->src;
		if (!$src)
			$src = $i->src;
		}
	if ($src)
		\getphoto($sub, $src, $image, "$pUid.", self::sleep);
	unset($img);
	unset($src);
	$save = [
		'address' => self::addressSanitize(self::divInner($x, '#producer-address')),
		'name' => self::divInner($x, '#producer-name'),
		'phone' => self::divInner($x, '#phone'),
		'website' => self::websiteSanitize(self::divInner($x, '#website')),
		'general_info' => self::detag(self::divInner($x, '#producer-description')),
		'company_email' => self::emailSanitize(self::divInner($x, '#email-address')),
		'update_ts' => time(),
		'contact' => self::divInner($x, '#contact'),
		'externalid' => $pUid,
		'image' => $image,
		];
	if ($pUid == '500bfe14-0870-4d87-991e-ad2df2646c35')
		dn($save, $txt);
	return \setFarmer($sub, $save, $ts);
	}

function websiteSanitize($text)
	{
	if (!trim($text))
		return '';
	$match="/href\s*\=\s*[\"\'](\S*?)[\"\']/";
	if (preg_match($match, $text, $r))
		{
		return $r[1];
		}
	return trim($text);
	}

function addressSanitize($addr)
	{
	if (substr($addr, 0, 5) == 'City:')
		$addr = trim(substr($addr, 5));
	if (substr($addr, 0, 8) == 'Address:')
		$addr = trim(substr($addr, 8));
	$last = substr($addr, strlen($addr)-1);
	if (!preg_match("/[a-zA-Z0-9]/", $last))
		$addr = substr($addr, 0, strlen($addr)-1);
	return $addr;
	}

function emailSanitize($addr)
	{
	return trim(str_replace('Email Address:', '', $addr));
	}

function deTag($text)
	{
	$text = preg_replace("/\<[Bb][Rr][\s\/]*\>/", "\n", $text);
	return strip_tags($text, '');
	} 

function divInner($x, $divId)
	{
	$divs = $x->find($divId);
	foreach($divs AS $div)
		{
		if ($txt = trim($div->innerText()))
			return $txt;
		}
	// did we find the elements? If so, return '';
	if ($divs)
		return '';
	}

public static function getImage($p, &$save)
	{
	$src = '';
	$x = str_get_html($p['prImage']);
	foreach($x->find('img') AS $a)
		$src = $a->src;
	if (!$src)
		return print("Unable to find image...\n");
	self::domain($domain, $sub);
	return \getPhoto($sub, $src, $save, '', self::sleep);
	}

public static function guessPrice($price)
	{
	$match="/\\$([0-9\.]+)/";
	$fields = [ 'unitPrice', 'unitName', 'unitFullName' ];
	foreach($fields AS $f)
	 if (preg_match($match, $price[$f], $r))
		 return $r[1];
	return -1;
	}

}
