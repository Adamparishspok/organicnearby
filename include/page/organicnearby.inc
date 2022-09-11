<?php

namespace sb;
class IncludePageOrganicnearby extends IncludePage
{

function whichts($path)
	{
	if ($file = $this->env->which($path))
		filemtime($file);
	}

/**
 *
 * @param type $lat
 * @param type $lon
 * @return boolean TRUE if lat/lon found.
 */
function resolveLatLon(&$lat, &$lon)
	{
	$lat = $lon = 0;
	if ($t = retval($this->env->session, 'lat'))
		$lat = $t;
	if ($t = retval($this->env->session, 'lon'))
		$lon = $t;

	// request lat/lon are from browser settings. Has our location been 
	// overidden by /location.php?
	if (!retval($this->env->session, 'locSource'))
		{
		if ($t = $this->req('numeric', 'lat'))
			$lat = $t;
		if ($t = $this->req('numeric', 'lon'))
			$lon = $t;
		}
	if ($lat && $lon)
		{
		$this->env->session['lat'] = $lat;
		$this->env->session['lon'] = $lon;
		return true;
		}
	else
		{
		$d = $this->geoip();
		$this->env->session['lat'] = $d['lat'];
		$this->env->session['lon'] = $d['lon'];
		$this->env->session['locSource'] = 'geoip';
		}
	$this->nearestCounty();
	return false;
	}

function nearestCounty()
	{
	if (retval($this->session, 'locSource') == 'address')
		return true;
	$lon = $this->session['lon'];
	$lat = $this->session['lat'];
	$this->session['locAddress'] = $this->getNearestCounty($lat, $lon);
	return true;
	}

function getNearestCounty($lat, $lon)
	{
	if (!is_numeric($lon))
		return false;
	if (!is_numeric($lat))
		return false;
	$sql="WITH mypoint AS
			(
			SELECT POINT($lon, $lat)::geometry AS here,
				$lon AS lon,
				$lat AS lat
			)
		SELECT
			zip.*,
			st_distance(mypoint.here, zip.location) / 1000 AS distance,
			st_x(zip.location::geometry) AS zip_lon,
			st_y(zip.location::geometry) AS zip_lat
		FROM
			mypoint, zip
		ORDER BY
			st_distance(mypoint.here, zip.location) ASC
		limit 1
			";
	if (!$res = $this->DB->Query($sql))
		return $this->error($this->DB);
	while ($row = $res->Next())
		return $row['title'];
	}

function resolveProdScraped(&$table, &$table_id, &$Product, &$ScProduct)
	{
	static $saveSource, $saveSource_id, $saveProduct, $saveScProduct = null;
	if ($saveSource)
		{
		$table = $saveSource;
		$table_id = $saveSource_id;
		$Product = $saveProduct;
		$ScProduct = $saveScProduct;
		return true;
		}
	if (($table = $this->req('text', 'table')) == 'products')
		{
		$find = null;
		if ($table_id = $this->req('int', 'table_id'))
			$find = $table_id;
		$Product = $this->DB->findOrCreate('products', $find, 1);
		$find = [];
		if ($externalid = $Product->Get('externalid'))
			$find = [ 'externalid' => $externalid ];
		$ScProduct = $this->DB->findOrCreate('sc_products', $find, 1);
		}
	/**
	Althought defensive coding might suggest to enable this check, it's
	actually needed to be loosened so that add a new product works as expected,
	Better to have an empty ORM item than none at all.
	*/
	elseif(true) # ($this->req('text', 'table') == 'sc_products')
		{
		$table_id = $this->req('int', 'table_id');
		$ScProduct = $this->DB->findOrCreate('sc_products', $table_id, 1);
		$find = [];
		if ($externalid = $ScProduct->Get('externalid'))
			$find = [ 'externalid' => $externalid ];
		$Product = $this->DB->findOrCreate('products', $find, 1);
		}
	else
		return null;
	$saveSource = $table;
	$saveSource_id = $table_id;
	$SaveProduct = $Product;
	$SaveScProduct = $ScProduct;
	$return = new IncludeOrmFake($Product, $ScProduct, $table, $table_id);
	return $return;
	}

function resolveFarmer(&$table, &$table_id, &$F, &$SF)
	{
	if ($this->resolveFarmerByURL($F, $SF, $table, $table_id))
		{
		}
//	elseif ($this->resolveFarmerByOwnership($F, $SF, $table, $table_id))
//		{}
	else
		return false; //$this->error("Unable to resolve farmer record!");
	$fF = new IncludeOrmFake($F, $SF, $table, $table_id);
	return $fF;
	}

function geoip($remote_addr=null)
	{
	if (!$remote_addr)
		$remote_addr = $this->env->server['REMOTE_ADDR'];
	if ($this->devmode() && substr($remote_addr, 0, 4) == '192.')
		{
		$fp = fopen("http://monitor.effortlessis.com", 'r');
		$remote_addr = fgets($fp, 1024);
		}
	if (!$access_key = $this->env->get('settings', 'ipapi', 'apikey'))
		return $this->error("Unable to get APIkey for GeoIP lookup.");
	$fp = fopen("http://api.ipapi.com/$remote_addr?access_key=$access_key", 'r');
	$in = '';
	while ($line = fgets($fp, 65535))
		$in.=$line;
	fclose($fp);
	$data = json_decode($in, true);
	$return = [
		'lat' => $data['latitude'],
		'lon' => $data['longitude']
		];
	return $return;
	}

function resolveFarmerByURL(&$F, &$SF, &$table, &$table_id)
	{
	if (!$table)
		if (!$table = $this->req('text', 'table'))
			return false;
	if (!$table_id)
		if (!$table_id = $this->Req('int', 'table_id'))
			return false;
	if ($table == 'farmers')
		{
		$F = $this->DB->find($table, $table_id, 1);
		$find = [];
		if ($eId = $F->Get('externalid'))
			$find= [ 'externalid' => $eId ];
		$SF = $this->DB->findOrCreate('sc_farmers', $find, 1);
		return true;
		}
	elseif ($table == 'sc_farmers')
		{
		$SF = $this->DB->find($table, $table_id, 1);
		$find = [];
		if ($eId = $SF->Get('externalid'))
			$find= [ 'externalid' => $eId ];
		$F = $this->DB->findorCreate('farmers', $find, 1);
		return true;
		}
	return false;
	}

function resolveFarmerByOwnership(&$F, &$SF, &$table, &$table_id)
	{
	dn("I think this is deprecated / useless.");
	$find = [ 'authlogins_id' => $this->SEC->Get('id') ];
	$Fs = $this->DB->FindOrCreate('farmers', $find);
	$table = 'farmers';
	$table_id = $F->Get('id');
	$find = [];
	if ($F->Get('externalid'))
		$find = [
			'externalid' => $F->Get('externalid'),
			'sc_sources_id' => $F->Get('sc_sources_id'),
			];
	if ($find)
		$SF = $this->DB->Find('sc_farmers', $find, 1);
	else
		$SF = $this->DB->Create('sc_farmers', [], 1);
	return true;
	}


/**
	@param numeric $lat
	@param numeric $lon
	@param string $search optional
	@param string $where optional
	@param array $todb optional
	@param string $orderby optional one of: "distance", "name", "random"
 */
function productSearch($lat, $lon, $search='', $where = '', $todb=[], $orderby='distance')
	{
	$todb = array_merge($todb, ['@BOOL.empty' => 'TRUE']);

	if ($search)
		$this->setWhereSearch($search, $where, $todb);

	$fields = "
			mypoint.lat AS mylat,
			mypoint.lon AS mylon,
			sc_products.id AS sc_products_id,
			sc_products.sc_farmers_id AS sc_products_sc_farmers_id,
			sc_products.name AS sc_products_name,
			sc_products.price AS sc_products_price,
			sc_products.address AS sc_products_address,
			sc_products.description AS sc_products_description,
			sc_products.externalid AS sc_products_externalid,
			sc_products.insert_ts AS sc_products_insert_ts,
			sc_products.update_ts AS sc_products_update_ts,
			sc_products.bubbleid AS sc_products_bubbleid,
			sc_products.image AS sc_products_image,
			sc_products.stock AS sc_products_stock,
			st_y(sc_products.location::geometry) AS sc_products_lat,
			st_x(sc_products.location::geometry) AS sc_products_lon,
			CASE WHEN (sc_products.location IS NOT NULL AND mypoint.lon != 0)
				THEN st_distance(mypoint.here, sc_products.location) / 1000
				ELSE null END AS sc_products_km,
			sc_farmers.name AS sc_farmers_farmername,

			products.id AS products_id,
			products.farmers_id AS products_farmers_id,
			products.name AS products_name,
			products.price AS products_price,
			products.address AS products_address,
			products.description AS products_description,
			products.externalid AS products_externalid,
			products.insert_ts AS products_insert_ts,
			products.update_ts AS products_update_ts,
			products.bubbleid AS products_bubbleid,
			products.image AS products_image,
			products.stock AS products_stock,
			st_y(products.location::geometry) AS products_lat,
			st_x(products.location::geometry) AS products_lon,
			CASE WHEN (products.location IS NOT NULL AND mypoint.lon != 0)
				THEN st_distance(mypoint.here, products.location) / 1000
				ELSE null END AS products_km,
			farmers.name AS farmers_farmername,

			st_y(sc_farmers.location::geometry) AS sc_farmers_lat,
			st_x(sc_farmers.location::geometry) AS sc_farmers_lon,
			CASE WHEN (sc_farmers.location IS NOT NULL AND mypoint.lon != 0)
				THEN st_distance(mypoint.here, sc_farmers.location) / 1000
				ELSE null END AS sc_farmers_km,
			sc_farmers.address AS sc_farmers_address,

			st_y(farmers.location::geometry) AS farmers_lat,
			st_x(farmers.location::geometry) AS farmers_lon,
			CASE WHEN (farmers.location IS NOT NULL AND mypoint.lon != 0)
				THEN st_distance(mypoint.here, farmers.location) / 1000
				ELSE null END AS farmers_km,
			farmers.address AS farmers_address";

	$orders = [
		'distance' => "ORDER BY
			CASE 
				WHEN products_km IS NOT NULL THEN products_km
				WHEN sc_products_km IS NOT NULL THEN sc_products_km
				WHEN farmers_km IS NOT NULL THEN farmers_km
				ELSE sc_farmers_km END ASC,
			LOWER(CASE WHEN products_name IS NOT NULL
				THEN products_name
				ELSE sc_products_name END) ASC",
		'name' => "ORDER BY
			lower(CASE WHEN products_name IS NOT NULL
				THEN products_name
				ELSE sc_products_name END) ASC",
		'random' => "ORDER BY RANDOM()",
		];

	if (!isset($orders[$orderby]))
		return $this->Error("Invalid sorting order specified ($orderby)");
	$ORDERBY = $orders[$orderby];

	$sql="WITH mypoint AS
			(
			SELECT POINT($lon, $lat)::geometry AS here,
				$lon AS lon,
				$lat AS lat
			),
		allrecords AS
			(
			-- Scraped Records
			SELECT
				$fields
			FROM mypoint
			JOIN sc_products ON
				(true)
			JOIN sc_farmers ON
				(
				sc_products.sc_farmers_id = sc_farmers.id
				)
			LEFT OUTER JOIN (products JOIN farmers ON
					(
					products.farmers_id = farmers.id
					))
			ON
				(
				sc_products.externalid = products.externalid
				)
			WHERE
				[@BOOL.empty] = TRUE
				AND products.deleted_ts IS NULL
				AND farmers.deleted_ts IS NULL
				$where
			UNION

			-- Farmer Records
			SELECT
				$fields
			FROM mypoint
			JOIN products ON
				(true)
			JOIN farmers ON
				(
				products.farmers_id = farmers.id
				AND farmers.authlogins_id IS NOT NULL
				AND farmers.deleted_ts IS NULL
				)
			LEFT OUTER JOIN (sc_products JOIN sc_farmers ON
					(
					sc_products.sc_farmers_id = sc_farmers.id
					))
			ON
				(
				-- any time farmer records have a scrape match, they appear
				-- in the first half of this query.
				FALSE
				)
			WHERE
				[@BOOL.empty] = TRUE
				AND products.deleted_ts IS NULL
				$where
			)
		SELECT * FROM allrecords
		$ORDERBY";
//	dn($this->DB->SrqShow($sql, $todb));
	$res = $this->DB->Srq($sql, $todb);
	return $res;
	}

	function setWhereSearch($search, &$where, &$todb)
		{
		if (!$search = trim($search))
			return true;
		$match="/[\s,]+/";
		if (!$words = preg_split($match, strtolower($search)))
			return true;
		$words = $this->expandWordsProduct($words);
		$wAdd = [];
		$count = 1;
		foreach($words AS $word)
			{
			$pWord = $this->DB->escape($word);
			$wAdd[]="sc_products.name ILIKE '%$pWord%' 
				OR sc_products.description ILIKE '%$pWord%'
				OR products.name ILIKE '%$pWord%'
				OR products.description ILIKE '%$pWord%'
				";
//			$todb["@TEXT.word$count"] = $word;
			}
		$where .= "AND ( " . implode("\n\tOR\n\t", $wAdd). " ) ";
		return true;
		}

	/**
	 * Adds words that include other words. EG: "wine" includes "cabernet"
	 * @param type $words
	 */
	function expandWordsProduct($words)
		{
		$expand=[
			'wine' => [
				'blanc',
				'cabernet',
				'chardonnay',
				'gris',
				'merlot',
				'noir',
				'pinot',
				'reisling',
				'sauvignon',
				'syrah',
				'zinfandel',
				],
			'beer' => [
				'saison',
				'ale',
				'hop',
				'IPA',
				'kolsch',
				'marzen',
				'pilsner',
				'porter',
				'schwarzbier',
				'stout',
				],
			'cheese' => [
				'asiago',
				'blue cheese',
				'bocconcini',
				'brie',
				'burrata',
				'camembert',
				'cheddar',
				'colby',
				'cotija',
				'cottage',
				'emmental',
				'feta',
				'gargonzola',
				'gouda',
				'gruyere',
				'halloumi',
				'havarti',
				'jack',
				'jarlsbert',
				'labneh',
				'limburger',
				'mascarpone',
				'mozzarella',
				'muenster',
				'nuefchatel',
				'paneer',
				'parmesan',
				'provolone',
				'ricotta',
				'romano',
				],
			'meat' => [
				'beef',
				'chicken',
				'steak',
				'lamb',
				'mutton',
				'sirloin',
				'rump',
				'thigh',
				'breast',
				]
			];
		$return = $words;
		$add = [];
		foreach($words AS $word)
			{
			if (isset($expand[$word]))
				$add = array_merge($add, $expand[$word]);
			}
		$return = array_merge($add, $words);
		return $return;
		}

/**
 *
 * @param string $search word or words to search for. EG "black berries".
 * More words widen search
 * @param numeric  $lat location of searching party, used for distance calculation.
 * @param numeric $lon location of searching party, used for distance calculation.
 * @param string $orderby OPTIONAL one of "name", "distance"
 * @param integer $sc_sources_id OPTIONAL integer of sc_sources.id record.
 * @return type
 */
function farmerQuery($search, $lat, $lon, $orderby='distance', $sc_sources_id = null)
	{
	/**
	if (is_null($orderby))
		$orderby="GREATEST(sc_farmers.update_ts, farmers.update_ts) DESC,
			LOWER(CASE WHEN farmers.name IS NOT NULL THEN farmers.name ELSE sc_farmers.name END) ASC";
	 */
	$this->farmerWhereSearch($search, $where, $todb);
	$fields = "sc_farmers.id AS sc_farmers_id,
			sc_farmers_sources.source AS sc_farmers_source,
			sc_farmers.name AS sc_farmers_name,
			sc_farmers.address AS sc_farmers_address,
			sc_farmers.phone AS sc_farmers_phone,
			sc_farmers.website AS sc_farmers_website,
			sc_farmers.general_info AS sc_farmers_general_info,
			sc_farmers.company_email AS sc_farmers_company_email,
			sc_farmers.update_ts AS sc_farmers_update_ts,
			sc_farmers.image AS sc_farmers_image,
			sc_farmers.externalid AS sc_farmers_externalid,
			st_y(sc_farmers.location::geometry) AS sc_farmers_lat,
			st_x(sc_farmers.location::geometry) AS sc_farmers_lon,

			farmers.authlogins_id,
			farmers.id AS farmers_id,
			farmers_sources.source AS farmers_source,
			farmers.name AS farmers_name,
			farmers.address AS farmers_address,
			farmers.phone AS farmers_phone,
			farmers.website AS farmers_website,
			farmers.general_info AS farmers_general_info,
			farmers.company_email AS farmers_company_email,
			farmers.update_ts AS farmers_update_ts,
			farmers.image AS farmers_image,
			farmers.externalid AS farmers_externalid,
			st_y(farmers.location::geometry) AS farmers_lat,
			st_x(farmers.location::geometry) AS farmers_lon,
			$lon AS mylon,
			$lat AS mylat,
			CASE WHEN (sc_farmers.location IS NOT NULL AND mypoint.lon != 0)
				THEN st_distance(mypoint.here, sc_farmers.location) / 1000
				ELSE null END AS sc_farmers_km,
			CASE WHEN (farmers.location IS NOT NULL AND Mypoint.lon != 0)
				THEN st_distance(mypoint.here, farmers.location) / 1000
				ELSE null END AS farmers_km";

	$orders = [
		'name' => "lower(CASE WHEN farmers_name IS NOT NULL
				THEN farmers_name
				ELSE sc_farmers_name END) ASC",
		'distance' => "CASE WHEN farmers_km IS NOT NULL
				THEN farmers_km ELSE sc_farmers_km END",
		];
	if (!isset($orders[$orderby]))
		return $this->error("Invalid Orderby specified");
	$ORDERBY = $orders[$orderby];
	if (!$sc_sources_id = floor($sc_sources_id))
		$sc_sources_id = 'NULL';

	$sql = "
		WITH mypoint AS
			(
			SELECT POINT($lon, $lat)::geometry AS here,
				$lon AS lon,
				$lat AS lat
			),
		allresults AS
			(
			SELECT
				$fields,
				'sc_sub' AS portion
			FROM mypoint
			JOIN sc_farmers ON
				(
				$sc_sources_id IS NULL
				OR $sc_sources_id = sc_farmers.sc_sources_id
				)
			JOIN sc_sources sc_farmers_sources ON
				(
				sc_farmers.sc_sources_id = sc_farmers_sources.id
				)
			LEFT OUTER JOIN farmers ON
				(
				sc_farmers.sc_sources_id = farmers.sc_sources_id
				AND sc_farmers.externalid = farmers.externalid
				)
			LEFT OUTER JOIN sc_sources farmers_sources ON
				(
				farmers.sc_sources_id = farmers_sources.id
				)
			WHERE true
				$where
			UNION
			SELECT
				$fields,
				'f_sub' AS portion
			FROM mypoint
			JOIN farmers ON
				(
--				farmers.authlogins_id IS NOT NULL
				true
				AND
					(
					$sc_sources_id IS NULL
					OR $sc_sources_id = farmers.sc_sources_id
					)
				AND farmers.externalid IS NULL
				)
			LEFT OUTER JOIN sc_sources farmers_sources ON
				(
				farmers.sc_sources_id = farmers_sources.id
				)
			LEFT OUTER JOIN sc_farmers ON
				(
				-- have to left join to avoid 'missing FROM' errors
				-- but we don't actually want to join to any records
				-- in sc_farmers.... 
				farmers.externalid = sc_farmers.externalid
				AND farmers.sc_sources_id = sc_farmers.sc_sources_id
				)
			LEFT OUTER JOIN sc_sources sc_farmers_sources ON
				(
				sc_farmers.sc_sources_id = sc_farmers_sources.id
				)
			WHERE true
				$where
			)
		SELECT
			*
		FROM allresults
		ORDER BY
			$ORDERBY
		";
	$res = $this->DB->Query($sql);
	return $res;
	}

function farmerWhereSearch($search, &$where, &$todb)
	{
	$where = '';
	$todb = [];
	if (!$search)
		return true;
	$match="/[\s,]+/";
	if (!$words = preg_split($match, strtolower($search)))
		return true;
	$words = $this->expandWordsFarmer($words);
	$wAdd = [];
	$count = 1;
	foreach($words AS $word)
		{
		$pWord = $this->DB->escape($word);
		$wAdd[]=
		    "   sc_farmers.name    ILIKE '%$pWord%'
			OR    sc_farmers.address ILIKE '%$pWord%'
			OR    sc_farmers_sources.source      =  '$pWord'
			OR    farmers.name    ILIKE '%$pWord%'
			OR    farmers.address ILIKE '%$pWord%'
			OR    farmers_sources.source      =  '$pWord'
		";
//			$todb["@TEXT.word$count"] = $word;
		}
	$where .= "AND ( " . implode("\n\tOR\n\t", $wAdd). " ) ";
	return true;
	}

function expandWordsFarmer($words)
	{
	return $words;
	}

/**
 * Given a string input, returns a sanitized string for a URL.
 * @param string $str
 * @return munged result. EG: "It's fun!" becomes "it-s-fun-"
 */
function urlify($str)
	{
	return preg_replace("/[^a-zA-Z0-9]+/m", '-', trim($str));
	}


/**
 * Updates request with table, table_id, and urlName parameters.
 *
 * So that links will be searchable, I've made the URL for products to
 * be EG "/product.php/$table/$id" - this "fills in" the $request variables
 * from this path for "normal" consumption in this page.
 * @var $mypage
 * @return boolean
 */
function getTableAndId(string $mypage=null, array $ok = ['sc_products', 'products', 'sc_farmers', 'farmers'], &$target='blank')
	{
	if ('blank' === $target)
		$target = &$this->request;
	if (is_null($mypage))
		$mypage = $this->server['PAGE_NAME'];
	while (substr($mypage, 0, 1) == '/')
		$mypage = substr($mypage, 1);
	$x = parse_url($this->server['REQUEST_URI']);
	$path = $x['path'];
	while (substr($path, 0, 1) == '/')
		$path = substr($path, 1);
	$path = str_replace('//', '/', $path);
	$y = explode("/", $path);
	// if the format is funky, we quit.
	if (sizeof($y) > 4)
		return true;
	if (sizeof($y) < 3)
		return true;
	unset($target['table']);
	unset($target['table_id']);
	if ($y[0] != $mypage)
		return true;
	if (!in_array($y[1], $ok))
		return true;
	if (!is_numeric($y[2]))
		return true;
	$target['table'] = $y[1];
	$target['table_id'] = $y[2];
//	$this->request['id'] = $y[2];
	// will I ever need this? Almost certainly not...
	$target['urlName'] = retval($y, 3);
	return true;
	}


}
