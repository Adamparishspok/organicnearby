#! /usr/bin/php
<?php

function getContents($url)
	{
	echo "getContents $url\n";
	$fp = fopen($url, 'r');
	$content = '';
	while ($line = fgets($fp, 65535))
		$content.=$line;
	return $content;
	}


/**
 * Retrieves content from a json endpoint. 
 * @param string $url EG https://example.site.com/json/endpoint
 * @param string $data_string EG: json_encoded array
 * @return string
 */
function getContentsJson($url, $data_string)
	{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER,
	    array(
	        'Content-Type: application/json',
	        'Content-Length: ' . strlen($data_string)
	    )
	);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_string);
	$x = curl_exec($ch);
	return $x;
	}


function cacheGoodEnough($file, $maxSeconds=3600)
	{
	if (!file_exists($file))
		return false;
	if (filemtime($file) < (time() - $maxSeconds) )
		return false;
	return true;
	}

/**
 *
 * @param type $file
 * @param int $maxSeconds (if unspecified, defaults to 3600)
 * @return type
 */
function getCachedContent($file, $maxSeconds = -1)
	{
	if ($maxSeconds == -1)
		{
		$o = getOptions();
		if (isset($o['cache']))
			$maxSeconds = $o['cache'];
		}
	if (cacheGoodEnough($file, $maxSeconds))
		return file_get_contents($file);
	}

function getOptions()
	{
	$ret = [];
	$match="/^(\S+)\=(\S+)$/";
	foreach($_SERVER['argv'] as $arg)
	 if (preg_match($match, $arg, $r))
		{
		$ret[$r[1]]=$r[2];
		}
	return $ret;
	}

function cacheContent($file, $content)
	{
	$fp = fopen($file,'w');
	$return = fwrite($fp, $content);
	fclose($fp);
	return $return;
	}

function extractLink($text)
	{
	$match="/href\=\"(.+?)\"/";
	if (!preg_match($match, $text, $r))
		return false;
	$return = $r[1];
	if (substr($return, 0, 2) == '//')
		$return = 'https:' . $return;
	return $return;
	}

/**
 *
 * @param string $vendor EG "linc"
 * @param string $url EG "https://site.com/images/blah.jpg"
 * @param ref $save memory address where local relative path will be saved. EG:
 *	"images/linc/blah.jpg"
 * @return string local page, same as $save
 */
function getPhoto($vendor, $url, &$save, $ofilePrepend='', $sleep=0)
	{
	if (!$vendor)
		dn("Vendor string is required");
	$parts = parse_url( $url );
	$ofile = $ofilePrepend.basename($parts['path']);
	$opath = "images/$vendor/$ofile";
	if (!is_dir("images/$vendor/"))
		if (!mkdir("images/$vendor/"))
			die("Unable to make dir images/$vendor/");
	if (file_exists($opath))
		{
		echo "Picture already found\n\t$opath\n";
		$save = $opath;
		return $opath;
		}
	elseif (!$ifp = fopen($url, 'r'))
		{
		//die("unable to open $r[1] for read");
		}
	elseif ($ofp = fopen($opath, 'w'))
		{
		echo "Retrieving\n\t$opath\n";
		while ($line = fgets($ifp, 65535))
			fwrite($ofp, $line);
		fclose($ifp);
		fclose($ofp);
		$save = $opath;
		if ($sleep)
			sleep($sleep);
		return $opath;
		}
	else
		die("Unable to open $opath for write");
	return false;
	}

function dn()
	{
	$e = func_get_args();
	print_r($e);
	die(1);
	}


function getConn()
	{
	static $conn; 
	if ($conn)
		return $conn;

	$rpath = realpath(dirname(__FILE__)."/../../");
	$site = $_SERVER['argv'][1];
	$sfiles = [
		"/etc/sb4/$site/include/settings.inc",
		"$rpath/$site/include/settings.inc"
		];
	foreach($sfiles AS $sfile)
		{
		if ( (!isset($settings)) || (!file_exists($settings)) )
			$settings = $sfile;
		}
	if (!file_exists($settings))
		die("Unable to find settings file");
	include $settings;
	if (!$settings['connstr'])
		die("No DB connstr found!");
	$conn = pg_connect($settings['connstr']);
	return $conn;
	}

/**
 *
 * @param string $src
 * @param array  $farmer
 * @param int $ts
 * @return int sc_farmers.id record updated.
 */
function setFarmer(string $src, array $farmer, int $ts)
	{
	$sc_sources_id = getSourcesId($src);
	$required = [
		'address',
		'name',
		'phone',
		'website',
		'general_info',
		'company_email',
		'update_ts',
		'externalid',
		];
	foreach($required AS $r)
		if (!isset($farmer[$r]))
			dn("Farmer requires field $r");
	
	$conn = getConn();
	foreach($farmer AS $field => $fval)
		${$field} = pg_escape_string($conn, $fval);
	echo "Update farmer record: $name\n";
	$sql="UPDATE sc_farmers SET
		sc_sources_id=					'$sc_sources_id',
		address=				'$address',
		name=						'$name',
		phone=					'$phone',
		website=				'$website',
		general_info=		'$general_info',
		company_email=	'$company_email',
		update_ts=			$update_ts,
		image=					'$image'
	WHERE
			externalid=		'$externalid'
		returning sc_farmers.id
		";
	echo $sql;
	if (!$res = pg_query($conn, $sql))
		die("Error executing $sql");
	if (pg_affected_rows($res))
		{
		$row = pg_fetch_array($res, 0, PGSQL_ASSOC);
		return $row['id'];
		}
		{
		$sql = "INSERT INTO sc_farmers
			(
			name, phone, website,
			general_info,company_email, update_ts,
			insert_ts, externalid, sc_sources_id,
			address, image
			)
		VALUES
			(
			'$name', '$phone', '$website',
			'$general_info', '$company_email', $update_ts,
			$update_ts, '$externalid', '$sc_sources_id',
			'$address', '$image'
			)
			returning sc_farmers.id";
		echo $sql;
		if (!$res = pg_query($conn, $sql))
			die("Error executing $sql");
		$row = pg_fetch_array($res, 0, PGSQL_ASSOC);
		return $row['id'];
		}
	return true;
	}

function getSourcesId($source)
	{
	static $ids = [];
	if (isset($ids[$source]))
		return $ids[$source];
	$conn = getConn();
	$source = pg_escape_string($conn, $source);
	$sql="SELECT id FROM sc_sources WHERE source = '$source'";
	echo $sql;
	if (!$res = pg_query($conn, $sql))
		die("Unable to query $sql");
	if (pg_num_rows($res))
		{
		$row = pg_fetch_array($res, 0, PGSQL_ASSOC);
		return $ids[$source] = $row['id'];
		}
	$sql="INSERT INTO sc_sources (source, script)
		VALUES
		('$source', 'scrape.php')
		RETURNING *
		";
	echo $sql;
	if (!$res = pg_query($conn, $sql))
		die("Error executing $sql");
	if (pg_num_rows($res))
		{
		$row = pg_fetch_array($res, 0, PGSQL_ASSOC);
		return $ids[$source] = $row['id'];
		}
	}

/**
 *
 * @param array $fields <ul>
 *	<li><b>name</b> String EG "Mushrooms, Oyster"
 *	<li><b>price</b> Numeric 10.49
 *	<li><b>address</b>
 *	<li><b>stock</b> Int Number of items in stock
 *	<li><b>update_ts</b> Int epoch
 *	<li><b>image</b> Name of image file. EG: images/linc/123-123-123.jpeg
 *	<li><b>pricetxt</b> string. EG: "2 lb bag $10.49"
 *	<li><b>price_unit</b> string EG "2 lb bag"
 *	<li><b>linkcart</b> string EG "https://farmerjoe.com/buyme/12345
 *	<li><b>externalid</b> string EG 12345
 *	<li><b></b>
 * @return boolean
 */
function setProduct(array $fields, int $ts, int $farmers_id)
	{
	$req = [
		'name',
		'price',
		'address',
		'description',
		'stock',
		'update_ts',
		'image',
		'pricetxt',
		'price_unit',
		'linkcart',
		'externalid'
		];
	foreach($req AS $field)
	 if (!array_key_exists($field, $fields))
		 dn("setProduct: Missing $field", $fields);

	// specific to all sources.
	$conn = getConn();
	foreach($fields AS $field => $fval)
		${$field} = pg_escape_string($conn, $fval);

	$sql="UPDATE sc_products SET
		name=					'$name',
		price=				$price,
		address=			'$address',
		description=	'$description',
		stock=				$stock,
		update_ts=		$ts,
		image=				'$image',
		pricetxt=			'$pricetxt',
		price_unit=		'$price_unit',
		linkcart=			'$linkcart'
		WHERE externalid = '$externalid'
		";
	if (!$res = pg_query($conn, $sql))
		{
		die("Unable to query $sql");
		}
	if (pg_affected_rows($res))
		return true;
	$sql="INSERT INTO sc_products
		(
		name, price, address,
		description, stock, update_ts,
		insert_ts, externalid, sc_farmers_id,
		image, pricetxt, price_unit,
		linkcart
		)
	VALUES
		(
		'$name', $price, '$address',
		'$description', $stock, $update_ts,
		$ts, '$externalid', $farmers_id,
		'$image', '$pricetxt', '$price_unit',
		'$linkcart'
		)";
	if (!$res = pg_query($conn, $sql))
		{
		dn($product, "Unable to query $sql");
		}
	return true;
	}
function getFarmersMap()
	{
	$sql="SELECT * FROM sc_farmers";
	$conn = getConn();
	$res = pg_query($conn, $sql);
	$rows = pg_num_rows($res);
	$return = [];
	for ($i=0; $i<$rows; $i++)
		{
		$row = pg_fetch_array($res, $i, PGSQL_ASSOC);
		$return[$row['externalid']]=$row;
		}
	return $return;
	}

