<?php

#DESCRIPTION Shows details for a farmer, allows someone to claim it.
namespace sb;
class PageFarmer extends \sb\IncludePageOrganicnearby
{
var $fields = [
	'name' => 1,
	'address' => 0,
	'phone' => 1,
	'website' => 1,
	'general_info' => 0,
	];

var $page_title = "Farmer Account";

/**
 * Replaces PageAccount init()
 * @return type
 */
function init()
	{
	if ($this->SEC->getRoles('page', 'admin'))
		$this->fields['source'] = 1;
	return $this->getTableAndId(null, ['farmers', 'sc_farmers']);
	}

function finit()
	{
	if (!$this->SEC->getRoles('page', 'admin'))
		return true;
	if ('prompt' == $this->getAction())
		$this->dis['Prompt']['Fields'][0]['FirstRow']['AdminEdit'] = [
			'table' => $this->req('text', 'table'),
			'table_id' => $this->req('int', 'table_id'),
			];
	return true;
	}

/**
 * This page operates in two contexts: one as the account management, and
 * another as a farmer display (extended into farmer.php)
 * @return boolean
 */
function page_Farm()
	{
	$this->getTableAndId(null, ['farmers', 'sc_farmers']);
	$dis = &$this->dis['Farm'];
	if (!$Farmer = $this->getFarmer())
		return false;
	if (!$this->displayFarmer($dis, $Farmer))
		return false;
	if (!$this->displayProducts($dis))
		return false;
	$dis['page_title'] = $this->page_title;
	return true;
	}

/**
 * Gets the fake farmer record referenced.
 * @staticvar type $Farmer
 * @return IncludeCrudFake
 */
function getFarmer($table=null, $table_id=null)
	{
	static $Farmer;
	if ($Farmer)
		return $Farmer;
	if ($Farmer = $this->resolveFarmer($table, $table_id, $Farmer, $ScFarmer))
		return $Farmer;
	}

/**
 * Contexts that this function is called: <ol>
 * <li> Displaying a farm for a non-logged-in-user
 * <li> Displaying a farm for a logged in user that may or may not be self.
 * <li> Editing a farm as a logged in user.
 * <li> (Soon) Editing a farm as an admin account
 * </ol>
 * @param type $dis
 * @param type $Farmer
 * @param type $uploadImage
 * @return boolean
 */
function displayFarmer(&$dis, $Farmer, $uploadImage='')
	{
	$scraped_display = 'hideme';
	if (isset($Farmer->Second))
		if (!is_null($Farmer->Second->Get('id')))
			$scraped_display = '';
	$dis['scraped_display'] = $scraped_display;
	if ($Farmer->Get('deleted_ts'))
		{
		// "Disabled" block is used when viewing account
		$dis['Disabled']['date'] = $this->DateX->Date('m/d/Y h:i a', $Farmer->Get('deleted_ts'));
		// "deleted_checked" var is used for editing account.
		$dis['deleted_checked'] = 'CHECKED';
		}
	$ftitles = [
		'name' => 'Farm/Vendor',
		];
	foreach($this->fields AS $field => $display)
	 if ($display)
		{
		// come up with a friendly-looking field name
		if (!$ftitle = retval($ftitles, $field))
			$ftitle = ucfirst(str_replace('_', ' ', $field));
		$FirstRow = [];
		$value = $Farmer->Get($field);
		if ($field == 'name')
			{
			if (!$image = $Farmer->Get('image'))
				$image = $uploadImage;
			$FirstRow = [
				'image' => $image, 'rowspan' => sizeof($this->fields),
				'sc_farmers_id' => $Farmer->Get('id'),
				'urlify' => $this->urlify($Farmer->get('name')),
				];
			if ($Farmer->table() != 'sc_farmers')
				{}
			elseif ($Farmer->isClaimed())
				{}
			else
				{
				$FirstRow['Claim'] = [
					'PAGE_NAME' => $this->server['PAGE_NAME'],
					'sc_farmers_id' => $Farmer->Get('id'),
					];
				}
			}
		elseif ($field == 'source' && $sources_id = $Farmer->Get('sc_sources_id'))
			{
			$Source = $this->DB->Find('sc_sources', $sources_id, true);
			$value = $Source->Get('source');
			}
		$checked = '';
		$disabled = 'DISABLED';
		if (is_null($Farmer->Second->get($field)))
			{
			$checked = 'CHECKED';
			$disabled = '';
			}
	 	elseif (!is_null($Farmer->First->get($field)))
			{
			$checked = 'CHECKED';
			$disabled = '';
			}
		$sc_value = '';
		if (isset($Farmer->Second))
			$sc_value = $Farmer->Second->get($field);
		$Atag = ($ftitle == 'Website') ? ['value' => $value ] : [];
		$dis['Fields'][] = [
			'Atag' => $Atag,
			'ftitle' => $ftitle,
			'field' => $field,
			'checked' => $checked,
			'disabled' => $disabled,
			'sc_value' => $sc_value,
			'scraped_display' => $scraped_display,
			'value' => $value,
			'FirstRow' => $FirstRow,
			];
		}
	// next two used in edit
	$dis['domain'] = $this->env->GetDomains()[0];
	$dis['url'] = $Farmer->Get('url');
	// used in preview
	if ($dis['url'])
		{
		$dis['ShortAddress'] = [
			'domain' => $dis['domain'],
			'url' => $dis['url'],
			];
		}
	if ($loc = $Farmer->Get('location'))
	 if ($loc = IncludeGet::loc2array($loc))
		{
		$dis['Map']=[
			'lat' => $loc['lat'],
			'lon' => $loc['lon'],
			];
		}
	if ($this->isMe($Farmer) || $this->SEC->getRoles('page', 'admin'))
		{
		$dis['Edit'] = [
			'url' => $dis['url'],
			'table' => $this->req('text', 'table'),
			'table_id' => $this->req('int', 'table_id'),
			'urlify' => $this->urlify($Farmer->Get('name')),
			'show' => '', 
			];
		}
	$sc_address = '';
	if (isset($Farmer->Second))
		$sc_address = $Farmer->Second->get('address');
	$checked_address = '';
	$disabled_address = 'DISABLED';
	if (is_null($Farmer->Second->Get('address')))
		{
		$disabled_address = '';
		$checked_address = 'CHECKED';
		}
	elseif (!is_null($Farmer->First->Get('address')))
		{
		$checked_address = 'CHECKED';
		$disabled_address = '';
		}
	$dis['.sc_address'] = nl2br($sc_address);
	$dis['checked_address'] = $checked_address;
	$dis['disabled_address'] = $disabled_address;
	$dis['address'] = $Farmer->Get('address');
	$dis['.address_str'] = nl2br(htmlentities($Farmer->Get('address')));

	$checked_general_info = '';
	$disabled_general_info = 'DISABLED';
	if (is_null($Farmer->Second->Get('general_info')))
		{
		$disabled_general_info = '';
		$checked_general_info = 'CHECKED';
		}
	elseif (!is_null($Farmer->First->Get('general_info')))
		{
		$checked_general_info = 'CHECKED';
		$disabled_general_info = '';
		}
	$dis['.sc_general_info'] = nl2br(htmlentities($Farmer->Get('general_info')));
	$dis['checked_general_info'] = $checked_general_info;
	$dis['disabled_general_info'] = $disabled_general_info;
	$dis['.general_info'] = htmlentities($Farmer->Get('general_info'));
	$dis['show'] = '';
	return true;
	}

function isMe($Farmer=null)
	{
	if (is_null($Farmer))
		if (!$Farmer = $this->GetFarmer())
			return false;
	if (!$Farmer->Get('authlogins_id', 'exists'))
		return false;
	return $Farmer->Get('authlogins_id') == $this->SEC->Get('id');
	}

/**
 *
 * @param array $dis Reference
 * @return boolean
 */
function displayProducts(&$dis)
	{
	if (!$Farmer = $this->getFarmer())
		return false;
	$res = $this->getMyProducts();
	$dis['Products']['show'] = '';
	$fields = [ 'name', 'price', 'address', 'description', 'image',];
	while ($iRow = $res->Next())
		{
		$row = [];
		if (retval($iRow, 'products_id'))
			$row['table'] = 'products';
		else
			$row['table'] = 'sc_products';
		$row['table_id'] = $iRow[$row['table'].'_id'];
		foreach($fields AS $field)
			if (strlen($val = retval($iRow, "products_$field")))
				$row[$field]=$val;
			else
				$row[$field] = retval($iRow, "sc_products_$field");
		$row['urlName'] = $this->urlify($row['name']);
		if ($this->isMe($Farmer) || $this->SEC->getRoles('page', 'admin'))
			{
			if (!retval($iRow, 'products_deleted_ts'))
				$Deleted = [];
			else
				$Deleted = [ 'date' => $this->DateX->Date('m/d/Y h:i a', retval($iRow, 'products_deleted_ts')) ];
			$row['Edit'] = [
				'table' => $row['table'],
				'table_id' => $row['table_id'],
				'Deleted' => $Deleted,
				];
			}
		$dis['Products']['Product'][]=$row;
		}
	if ($this->isMe($Farmer) || $this->SEC->getRoles('page', 'admin'))
	if ($Farmer->First->Get('id')) // can only add products when local
		// farmer record has been created.
		{
		$dis['Products']['Add'] = [
			'table' => $this->req('text', 'table'),
			'table_id' => $this->req('int', 'table_id'),
			'urlify'=> $this->urlify($Farmer->Get('name')),
			'show' => '',
			];
		}
	return true;
	}


function page_claim()
	{
	if ( ($table = $this->req('text', 'table')) != 'sc_farmers')
		return $this->error("Only unclaimed records can be claimed");
	if (!$table_id = $this->Req('int', 'table_id'))
		return $this->error("Farmer record missing");
	if (!$SF = $this->DB->Find($table, $table_id, 1))
		return $this->error("Record not found");
	$find = [ 'externalid' => $SF->Get('externalid') ];
	$email = $SF->Get('company_email');
	if ($F = $this->DB->Find('farmers', $find, 1))
		{
		if ($F->Get('company_email'))
			$email = $F->Get('company_email');
		if ($authlogins_id = $F->Get('authlogins_id'))
			{
			$AL = $this->DB->Find('authlogins', $F->Get('authlogins_id'), 1);
			$email = $AL->Get('login');
			}
		}
	$please = "Please contact us to get your account corrected.";
	if (!$email)
		return $this->error("This record does not appear to have an email address. $please");
	$dis = &$this->dis['Claim'];
	$dis['munged'] = $this->mungeEmail($email);
	if ($this->SEC->Get('login'))
		{
		$dis['LoggedIn'] = [
			'authlogins_login' => $this->SEC->Get('login'),
			];
		$find = [ 'authlogins_id' => $this->SEC->Get('id') ];
		if ($F = $this->DB->Find('farmers', $find, 1))
			$dis['FarmerLinked'] = [
				'farmer_name' => $F->Get('name'),
				];
		}
	else
		$dis['NotLoggedIn']['show'] = '';

	return true;
	}

/**
 * Converts user@domain.com to "u***@d*****.com"
 * @param type $email
 * @return type
 */
function mungeEmail($email)
	{
	$e = explode('@', $email);
	if (sizeof($e) !== 2)
		return $this->error("Unable to verify email address validity. $please");
	$fname = substr($e[0], 0, 1);
	for ($i=1; $i<strlen($e[0]); $i++)
		$fname .= ' _ ';
	$lname = substr($e[1], 0, 1);
	$pos = strpos($e[1], '.');
	for($i=1; $i<$pos; $i++)
		$lname .= ' _ ';
	$lname .= substr($e[1], $pos);
	return "$fname@$lname";
	}

function getMyProducts()
	{
	if ('sc_farmers' === $this->req('text', 'table'))
		return $this->getMyProductsScFarmers();
	return $this->getMyProductsFarmers();
	}

function getMyProductsFarmers()
	{
	$todb = [
		'@int.table_id' => $this->req('int', 'table_id')
		];

	$select = "sc_products.id AS sc_products_id,
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

			sc_farmers.id AS sc_farmers_id,
			sc_farmers.externalid AS sc_farmers_externalid,

			products.id AS products_id,
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
			products.deleted_ts AS products_deleted_ts,

			farmers.id AS farmers_id,
			farmers.externalid AS farmers_externalid";

	$sql="
		WITH allem AS
			(
			SELECT
				$select
			FROM farmers
			JOIN products ON
				(
				[@int.table_id] = farmers.id
				AND farmers.id = products.farmers_id
				AND products.externalid IS NULL
				)
			LEFT OUTER JOIN sc_products ON
				(
				products.externalid = sc_products.externalid
				)
			LEFT OUTER JOIN sc_farmers ON
				(
				sc_products.sc_farmers_id = sc_farmers.id
				)
			UNION
			SELECT
				$select
			FROM farmers
			JOIN sc_farmers ON
				(
				[@int.table_id] = farmers.id
				AND farmers.externalid IS NOT NULL
				AND farmers.externalid = sc_farmers.externalid
				)
			JOIN sc_products ON
				(
				sc_farmers.id = sc_products.sc_farmers_id
				)
			LEFT OUTER JOIN products ON
				(
				sc_products.externalid = products.externalid
				)
			)
		SELECT *
		FROM allem
			ORDER BY CASE
				WHEN products_name IS NOT NULL THEN lower(products_name)
				ELSE lower(sc_products_name) END ASC
		";
	$res = $this->DB->Srq($sql, $todb);
	return $res;
	}

function getMyProductsScFarmers()
	{
	$todb = [
		'@int.table_id' => $this->req('int', 'table_id')
		];
	$select = "sc_products.id AS sc_products_id,
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

			sc_farmers.id AS sc_farmers_id,
			sc_farmers.externalid AS sc_farmers_externalid,

			products.id AS products_id,
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

			farmers.id AS farmers_id,
			farmers.externalid AS farmers_externalid";

	$sql="SELECT
			$select
		FROM sc_farmers
		JOIN sc_products ON
			(
			sc_farmers.id = [@int.table_id]
			AND sc_farmers.id = sc_products.sc_farmers_id
			)
		LEFT OUTER JOIN farmers ON
			(
			farmers.id = -1
			)
		LEFT OUTER JOIN products ON
			(
			products.id = -1
			)
		UNION
		SELECT
			$select
		FROM sc_farmers
		JOIN farmers ON
			(
			sc_farmers.id = [@int.table_id]
			AND sc_farmers.externalid = farmers.externalid
			)
		JOIN products ON
			(
			farmers.id = products.farmers_id
			)
		LEFT OUTER JOIN sc_products ON
			(
			sc_products.id = -1
			)
		";
	$res = $this->DB->Srq($sql, $todb);
	return $res;
	}

}
