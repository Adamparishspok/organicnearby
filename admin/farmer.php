<?php

namespace sb;

class PageAdminFarmer extends \sb\IncludePage
{

var	$skip = [ 'sc_sources_id', 'externalid', 'insert_ts', 'update_ts', 'bubbleid',
	'id', 'location', 'location_addr', 'image' ];

function init()
	{
	return true;
	}

function finit()
	{
	return true;
	}

/**
 * Finds table and table_id from req, then resolves appropriate / matching
 *	farmer and sc_farmer records before returning an ormFake of the two
 *	put together.
 * @param string $table string ref
 * @param integer $table_id integer ref
 * @param IncludeOrmCrudFarmer $F
 * @param IncludeOrmCrudSc_farmer $SF
 * @return \sb\IncludeOrmFake
 */
function resolveScrapedFarmer(&$table, &$table_id, &$F, &$SF)
	{
	if (!$table = $this->req('text', 'table'))
		return $this->error("Unable to find table");
	if ($table_id = $this->Req('int', 'table_id'))
		{}
	elseif (!$this->req('int', 'new'))
		return $this->error("Unable to find table id");
	if ($table == 'sc_farmers')
		{
		$SF = $this->DB->Find($table, $table_id, 1);
		$find = [];
		if ($externalid = $SF->Get('externalid'))
			$find = ['externalid' => $externalid ];
		$F = $this->DB->findOrCreate('farmers', $find, 1);
		}
	elseif ($table == 'farmers')
		{
		$F = $this->req('int', 'new')
			? $this->DB->Create($table, [], 1)
			: $this->DB->Find($table, $table_id, 1);
		$find = [];
		if ($externalid = $F->Get('externalid'))
			$find = ['externalid' => $externalid ];
		$SF = $this->DB->findOrCreate('sc_farmers', $find, 1);
		}
	$return = new IncludeOrmFake($F, $SF, $table, $table_id);
	return $return;
	}

function page_prompt()
	{
	$dis = &$this->dis['Prompt'];
	if (!$fF = $this->resolveScrapedFarmer($table, $table_id, $F, $SF))
		return false;
	$dis['table'] = $table;
	$dis['table_id'] = $table_id;
	$x = $fF->Get();
	$fields = $SF->Get();
	$fields['url']='Vanity URL';
	foreach($fields AS $field => $val)
		{
		if (!in_Array($field, $this->skip))
			{
			$disabled = is_null($F->Get($field)) ? 'DISABLED' : '';
			$checked = $disabled ? '' : 'CHECKED';
			if ($this->req('int', 'new'))
				{
				$checked = 'CHECKED';
				$disabled = '';
				}
			$dis['Fields'][]=[
				'field' => $field,
				'scraped' => $SF->Get($field),
				'eval' => $fF->Get($field),
				'disabled' => $disabled,
				'checked' => $checked,
				];
			}
		}
	if (!$fF->Get('authlogins_id'))
		$dis['NewAuthlogin']['show'] = '';
	elseif ($authlogins_id = $fF->get('authlogins_id'))
		{
		$AL = $this->DB->Find('authlogins', $authlogins_id, 1);
		$dis['NewAuthlogin']['authlogins_email'] = $AL->Get('login');
		}
	// show comemnts
	$find = [ 'tablename' => 'farmers', 'table_id' => $F->Get('id') ];
	if ($CS = $this->DB->Find('comments', $find))
		{
		while ($row = $CS->Next())
			{
			$A = $this->DB->Find('authlogins', $row['authlogins_id'], true);
			$row['login'] = $A->Get('login');
			$row['date'] = $this->DateX->Date('m/d/Y', $row['ts']);
			$dis['Comments'][]=$row;
			}
		}
	$dis['id']=$SF->Get('id');
	$dis['externalid'] = $SF->Get('externalid');
	return true;
	}

function page_save()
	{
	$AuthLogin = null;
	if (!$this->DB->Transaction('begin', 'save'))
		return $this->error("Unable to begin transaction");
	$this->DB->Verbose(true);
	if (!$table = $this->req('text', 'table'))
		return $this->error("Unable to get table");

	$farmers_id = $this->req('int', 'table_id');
	$authlogins_email = $this->req('email', 'authlogins_email');
	if ($table == 'sc_farmers' && $farmers_id)
		{
		// we have a scraped farmer record (most common case)
		if (!$SF = $this->DB->Find('sc_farmers', $farmers_id, true))
			return $this->error('Unable toi get farmer scrape record');
		$find = ['externalid' => $SF->Get('externalid') ];
		}
	elseif ($table == 'farmers' && $farmers_id)
		{
		// we have a locally created farmer record
		$SF = $this->DB->Create('sc_farmers', [], true);
		$find = [ 'id' => $farmers_id ];
		}
	elseif ($this->req('text', 'authlogins_email') && (!$authlogins_email) )
		return $this->error("New Login account must be a valid email address");
	else
		{
		// we are adding a new farmer record.
		$SF = $this->DB->Create('sc_farmers', [], 1);
		$find = [];
		}

	// This is the local farmer record we are editing or creating.
	$F = $this->DB->findOrCreate('farmers', $find, 1);
	if (!$this->updateAuthlogin($F))
		return false;

	// start with defaulting to scraped record.
	$save = $SF->Get();
	unset($save['insert_ts']);
	unset($save['update_ts']);
	unset($save['id']);
	// fill in values from the scrape.
	foreach($save AS $field => $val)
	 if (!in_array($field, $this->skip))
		{
		$val = $this->req('text', $field);
		$save[$field]=$val;
		}
	// NULLify unchecked fields.
	if (!$monitor = retval($this->request, 'monitor'))
		{}
	else foreach($monitor AS $field => $ignore)
		{
		if (!$this->req('int', 'active', $field))
			$save[$field] = NULL;
		}
	// fields that don't exist in $SF but do exist in $F
	$fOnly = ['url'];
	foreach($fOnly AS $f)
	 if ($this->req('int', 'active', $f))
		$save[$f] = $this->req('text', $f);

	// CHECK THAT VANITY ADDRESS DOESN'T CONFLICT.
	if (retval($save, 'url'))
		{
		$find = ['url' => $save['url']];
		if ($CHECK = $this->DB->Find('farmers', $find, true))
		 if ($CHECK->get('id') != $F->Get('id'))
			return $this->error("Vanity address $save[$f] is already used");
		}

	// set the appropriate times
	if (!$F->Get('insert_ts'))
		$F->Set('insert_ts', time());
	$F->Set('update_ts', time());
	$F->Set($save);
	if (!$F->Save())
		return $this->Error("Unable to save: ".$F->Error());
	if (!$this->DB->Transaction('commit', 'save'))
		return $this->error("Unable to commit transaction");
	$dis = &$this->dis['Save'];
	foreach($save AS $field => $val)
		$dis['Fields'][]=[
			'field' => $field,
			'value' => $val
		];
	// save comments if any
	if ($comments = $this->req('text', 'comments'))
		{
		$save = [
			'tablename' => 'farmers',
			'table_id' => $F->Get('id'),
			'ts' => $F->Get('update_ts'),
			'authlogins_id' => $this->SEC->Get('id'),
			'comments' => $comments,
			];
		if (!$C = $this->DB->Create('comments', $save))
			return $this->error("Unable to get comments table");
		if (!$C->Save())
			return $this->error("Unable to save comments");
		}
	return true;
	}

function updateAuthlogin($F)
	{
	$authlogins_email = $this->req('email', 'authlogins_email');
	$authlogins_email = trim(strtolower($authlogins_email));

	if ($F->Get('authlogins_id'))
		{
		if (!$authlogins_email)
			return $this->error("Login email address cannot be blank once created.");
		$AuthLogin = $this->DB->Find('authlogins', $F->Get('authlogins_id'), 1);
		$AuthLogin->Set('login', $authlogins_email);
		}
	// New account, no email address specified. It's OK! We're creating a farmer
	// without a login record. 
	elseif (!$authlogins_email)
		return true;
	else
		{
		$authlogin_find = [ 'login' => $authlogins_email ];
		$AuthLogin = $this->DB->findOrCreate('authlogins', $authlogin_find, 1);
		if ($AuthLogin->Get('id'))
			return $this->error("Login email already exists!");
		}
	if (!$AuthLogin->Save())
		return $this->error($AuthLogin);
	if (!$F->Get('authlogins_id'))
		$F->Set('authlogins_id', $AuthLogin->Get('id'));
	// just in case:
	if ($F->Get('authlogins_id'))
		if ($F->get('authlogins_id') != $AuthLogin->Get('id'))
			return $this->error("Farmer authlogin ID and authlogin record ID do not match");
	return true;
	}

}
