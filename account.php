<?php

#DESCRIPTION Allows farmers to set up and manage their own account.
namespace sb;
class PageAccount extends \sb\PageFarmer
{
/**
 * Fields to edit/display
 * @var array [fieldname => defaultDisplay]
 */
var $fields = [
	'name' => 1,
	'address' => 0,
	'phone' => 1,
	'website' => 1,
	'company_email' => 1,
	'general_info' => 0,
	];

var $page_title = "Farmer Account";

function init()
	{
	// note: this only runs when called as account.php.
	$this->env->dis['role_active'] = 'active';
	return true;
	}

function finit()
	{
	return true;
	}

function page_prompt()
	{
	$this->getTableAndId(null, ['farmers', 'sc_farmers']);
	$authlogins_id = $this->getAuthLoginsIdAdmin();
	if (!$AuthLogin = $this->DB->Find('authlogins', $authlogins_id, true))
		return $this->error("Unable to find user requested!");

	$find = [ 'authlogins_id' => $authlogins_id ];
	$Fs = $this->DB->Find('farmers', $find);
	$dis = &$this->dis['Prompt'];
	$dis = [
		'authlogins_id' => $AuthLogin->Get('id'),
		'login' => $AuthLogin->Get('login')
		];
	while ($F = $Fs->Fetch())
		{
		$SF = null;
		if ($F->Get('externalid'))
			{
			$find = ['externalid' => $F->Get('externalid') ];
			$SF = $this->DB->Find('sc_farmers', $find, 1);
			}
		if (!$SF)
			$SF = $this->DB->Create('sc_farmers', [], 1);
		$fields = [ 'name', 'deleted_ts' ];
		$row = [ 'id' => $F->Get('id'),
			'table' => 'farmers' ];
		foreach($fields AS $field)
			{
			if (!$row[$field] = $F->Get($field))
				$row[$field] = $SF->Get($field);
			}
		$row['visibility'] = $row['deleted_ts']
			? "Disabled on ".$this->DateX->Date('m/d/Y', $row['deleted_ts'])
			: 'Currently Visible';
		$row['urlname'] = $this->urlify($F->Get('name'));
		$dis['Farms'][]=$row;
		}
	if ($this->SEC->getRoles('page', 'admin'))
		{
		$FindUser = [
			'show' => '',
			'user' => $this->req('text', 'user'),
			];
		if ($user = $this->req('text', 'user'))
			{
			$find = [ 'login' => '%'.$this->DB->Escape($user).'%' ];
			$o = [
				'ilike'  => [ 'login' ],
				'orderby' => 'LOWER(login) asc',
				'limit' => 100,
				];
			$Authlogins = $this->DB->Find('authlogins', $find, false, $o);
			while ($Login = $Authlogins->Fetch())
				{
				$FoundUser = $Login->Get();
				$FoundUser['user'] = $this->req('text', 'user');
				$FindUser['FoundUsers'][] = $FoundUser;
				}
			}
		$dis['FindUser'] = $FindUser;
		}
	return true;
	}

function page_adduser()
	{
	if (!$this->SEC->getRoles('page', 'admin'))
		return $this->error("Invalid function");
	$dis = &$this->dis['AddUser'];
	$dis['login'] = '';
	return true;
	}

function page_saveuser()
	{
	if (!$this->SEC->getRoles('page', 'admin'))
		return $this->error("Invalid function");
	if (!$login = $this->req('email', 'login'))
		return $this->error("Error retrieving login account - was it a valid email?");
	$save = [
		'login' => trim(strtolower($login)),
//		'recovery_email' => trim(strtolower($login)),
//		'active' => true,
		];
	$Authlogin = $this->DB->FindOrCreate('authlogins', $save, 1);
	if ($Authlogin->get('id'))
		return $this->error("User already exists: ".$Authlogin->Get('login'));
	$Authlogin->Set('recovery_email', $Authlogin->Get('login'));
	$Authlogin->Set('active', true);
//	$this->DB->Verbose(true);
//	$this->DB->Transaction('begin', 'saveuser');
	if (!$Authlogin->Save())
		return $this->error($Authlogin);
	$dis = &$this->dis['SaveUser'];
	$dis['authlogins_id'] = $Authlogin->Get('id');
	return true;
	}

function page_Edit()
	{
	$this->getTableAndId(null, ['farmers', 'sc_farmers']);
	$dis = &$this->dis['Edit'];
	$authlogins_id = $this->getAuthLoginsIdAdmin();
	if (!$Farmer = $this->getFarmer())
		{
		$F = $this->DB->create('farmers', [ 'authlogins_id' => $authlogins_id ], 1);
		$ScF = $this->DB->create('farmers', [], true);
		$Farmer = new IncludeOrmFake($F, $ScF, 'farmers', null);
		}
	$dis['authlogins_id'] = $authlogins_id;
	if (!$this->isMe($Farmer))
		if (!$this->SEC->getRoles('page', 'admin'))
			return $this->error("Permission denied");
	return $this->displayFarmer($dis, $Farmer, "/assets/img/upload.png");
	}


function page_cancel()
	{
	header('Location: /'); //farmer.php?action[farm]');
	die();
	}

function page_save()
	{
	$this->getTableAndId(null, ['farmers', 'sc_farmers']);
	$authlogins_id = $this->getAuthLoginsIdAdmin();
	if (!$Farmer = $this->getFarmer())
		{
		$F = $this->DB->Create('farmers', [ 'authlogins_id' => $authlogins_id], true);
		$ScF = $this->DB->Create('sc_farmers', [], true);
		$Farmer = New IncludeOrmFake($F, $ScF, 'farmers', null);
		}
	if (!$this->isMe($Farmer))
		if (!$this->SEC->getRoles('page', 'admin'))
			return $this->error("Permission denied");

	if (!$this->SEC->Get('id'))
		return $this->error("You must be logged in to use this function.");
//	if ($this->SEC->Get('id') !== $Farmer->Get('authlogins_id'))
//		return $this->error("Attempt to save record not belonging to self has been logged.");
	foreach($this->fields AS $field => $displayIgnored)
		{
		$val = $this->req('text', $field);
		if ($this->req('int', 'active', $field))
			$Farmer->Set($field, $val);
		else
			$Farmer->Set($field, null);
		}
	if ($this->req('int', 'deleted'))
		{
		if (!$Farmer->Get('deleted_ts'))
			$Farmer->Set('deleted_ts', time());
		}
	else
		{
		if ($Farmer->Get('deleted_ts'))
			$Farmer->Set('deleted_ts', null);
		}
	if (!$this->checkUrl($Farmer))
		return false;
// how to check for what's gonna be saved
	if (!$Farmer->Save())
		return false;
	$dis = &$this->dis['Save'];
	$dis['authlogins_id'] = $authlogins_id;
	$dis['table'] = $this->req('text', 'table');
	$dis['table_id'] = $this->req('int', 'table_id');
	if (!$dis['table_id'])
		{
		if ($dis['table_id'] = $Farmer->Second->get('id'))
			$dis['table'] = 'sc_farmers';
		else
			{
			$dis['table_id'] = $Farmer->First->get('id');
			$dis['table'] = 'farmers';
			}
		}
	$dis['urlify'] = $this->urlify($Farmer->Get('name'));
	$dis['show'] = '';
	return true;
	}

/**
 * Gets the authlogins_id from stdin IF the end user has admin privs,
 * otherwise returns $SEC->Get('id').
 * @return int authlogins.id
 */
function getAuthloginsIdAdmin()
	{
	$authlogins_id = null;
	if ($this->SEC->getRoles('page', 'admin'))
		$authlogins_id = $this->req('int', 'authlogins_id');
	if (!$authlogins_id)
		$authlogins_id = $this->SEC->Get('id');
	return $authlogins_id;
	}


function checkUrl($Farmer)
	{
	$url = null;
	if (!$url = trim($this->req('text', 'url')))
		return $Farmer->Set('url', null);
	$url = strtolower($url);
	// Check that it doesn't replace an existing asset
	$path = "/$url";
	if ($this->env->which($path))
		return $this->error("Short address conflict with existing asset");
	// Check that it doesn't contain ".php"
	if (strpos($url, '.php') !== false)
		return $this->error("Unable to set short address: script file reference failed");
	// Check that it doesn't containt "/admin/"
	if (strpos($url, 'admin') !== false)
		return $this->error("References to admin functions are prohibited");
	// Check that it is only [a-zA-Z0-9]
	$match="/[a-zA-Z0-9]*/";
	$left = preg_replace($match, '', $url);
	if (strlen($left))
		return $this->error("Only alphanumeric characters allowed. No spaces or special characters allowed. These characters aren't allowed: \"$left\"");
	$find = ['url' => $url ];
	if ($Check = $this->DB->Find('farmers', $find, true))
		if ($Check->Get('id') != $Farmer->Get('id'))
			return $this->error("The short address '$url' is already used by somebody else");
	return $Farmer->Set('url', $url);
	}

function page_image()
	{
	$this->getTableAndId(null, ['farmers', 'sc_farmers']);
	if (!$F = $this->getFarmer())
		return false;
	$dis = &$this->dis['Image'];
	$dis['table'] = $this->req('text', 'table');
	$dis['table_id'] = $this->req('int', 'table_id');
	$dis['urlify'] = $this->urlify($F->Get('name'));
	$dis['image'] = $F->Get('image').'';
	return true;
	}

function page_ImgSave()
	{
	$this->getTableAndId(null, ['farmers', 'sc_farmers']);
	if (!$F = $this->getFarmer())
		return false;
	if (!$this->savePicture($F))
		return false;
/*
	$saved = $this->savePicture($F);
	if ($saved === 1)
		return $this->error("No picture was found!");
	if (!$saved)
		return false;
*/
	$this->dis = [];
	$dis = &$this->dis['ImgSave'];
	$dis['table'] = $this->req('text', 'table');
	$dis['table_id'] = $this->req('int', 'table_id');
	$dis['show']='';
	$dis['image'] = $F->Get('image');
	return true;
	}

/**
 *
 * @param IncludeOrmCrud $F Crud object with field "image" in it.
 * @param bool $noImageOk - is it an error condition if no image was uploaded? 
 * @param $__FILES[disk/camera]
 * @return int <ul>
 *	<li> false/error
 *	<li> 1 No image found.
 *	<li> 2 Image found and saved.
 *	<ul>
 */
function savePicture(&$F, $noImageOk=false)
	{
	$fp = fopen("/tmp/savePicture", 'w');
	fwrite($fp, var_export($this->files, true));
	fwrite($fp, var_export($this->request, true));
	fclose($fp);
	$fileInfo = retval($this->files, 'disk');
	if (!retval($fileInfo, 'size'))
		$fileInfo = retval($this->files, 'camera');
	if (!retval($fileInfo, 'size'))
	 	{
		if ($noImageOk)
			return true;
		return $this->error("No image was uploaded!");
		}
	if (preg_match("/^images\/([0-9]+)$/", $F->Get('image'), $r))
		$I = $this->DB->Find("images", $r[1], 1);
	else
		$I = $this->DB->Create("images", [], 1);
	if (!$I->Set($fileInfo))
		return $this->error($I);
	if (!$I->Save())
		return $this->error($I);
	$fdata = $I->GetFdata(true);
	$F->set('image', $fdata['tmp_name']);
	if (!$F->Save())
		return $this->error("Unable to save image data to farmers record");
	return true;
	}

function Page_add()
	{
	$table = $table_id = $Product = $ScProduct = \null;
	$this->resolveProdScraped($table, $table_id, $Product, $ScProduct);
	$dis = &$this->dis['Add'];
	$find = [];

	$AdminProducts = $this->env->getPageInstance("/admin/products.php");
	$AdminProducts->displayProduct($Product, $ScProduct, $dis);
	if (!$dis['image'] = $Product->Get('image'))
		$dis['image'] = $ScProduct->Get('image');
	if (!$dis['table'] = $this->req('text', 'table'))
		$dis['table'] = 'products';
	$dis['table_id'] = $this->req('int', 'table_id'); //Product->Get('id');
	$dis['urlify'] = 'ignored';
	return true;
	}

function Page_SaveProduct($imageMissingOk=1)
	{
	if (!$this->resolveProdScraped($table, $table_id, $P, $SP))
		return false;
	$fields = [ 'name', 'price', 'pricetxt' , 'price_unit', 'address',
		'description', 'stock', 'linkcart' ];
	foreach($fields AS $field)
		if ($this->req('int', 'active', $field))
			{
			if (!$P->Set($field, ''.$this->req('text', $field)))
				return $this->error($P);
			}
		else
			{
			if (!$P->Set($field, null))
				return $this->error($P);
			}
	$target = null;
	$this->getTableAndId(null, ['farmers', 'sc_farmers'], $target);
	if (!$F = $this->getFarmer($target['table'], $target['table_id']))
		return false;
	if ($this->req('int', 'deleted'))
		{
		if (!$P->Get('deleted_ts'))
			$P->Set('deleted_ts', time());
		}
	else
		{
		if ($P->Get('deleted_ts'))
			$P->Set('deleted_ts', null);
		}
	$this->DB->Transaction('begin', 'saveProduct');
	if (!$P->Set('farmers_id', $F->First->Get('id')))
		return $this->error("Unable to set farmers id");
	if (!$this->savePicture($P, $imageMissingOk))
		return $this->error("Unable to save picture");
	if (!$P->Save())
		return $this->error($P);
	$this->DB->Transaction('commit', 'saveProduct');
	$dis = &$this->dis['SaveProduct'];
	$dis['show']='';
	$dis['table'] = $target['table'];
	$dis['table_id'] = $target['table_id'];
	$dis['urlname'] = $this->urlify($F->get('name'));
	return true;
	}

function page_signup()
	{
	Header("Location: /account.php");
	die();
	}

}
