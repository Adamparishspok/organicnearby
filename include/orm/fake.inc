<?php

namespace sb;
class IncludeOrmFake extends \sb\IncludeBasic
{

/**
 *
 * @param IncludeOrmCrud $First
 */
var $First = null;

/**
 *
 * @param IncludeOrmCrud $Second
 */
var $Second = null;

/**
 * one of "farmers", "sc_farmers", "products", "sc_products"
 * @var string
 */
var $table = '';

/**
 * id of $table
 * @var int 
 */
var $table_id = null;

/**
 *
 * @param IncludeOrmCrud $First
 * @param IncludeOrmCrud $Second
 * @param string $table source table. One of <ul>
 *	<li>"farmers", "sc_farmers", "products", "sc_products"
 *	<li>'guess' infer based on preferring table/id of scraped records.
 *	</ul>
 * @param int $table_id
 * @return boolean
 */
function __CONSTRUCT(IncludeOrmCrud $First, IncludeOrmCrud $Second, $table, $table_id)
	{
	$this->First = $First;
	$this->Second = $Second;
	if ($table === 'guess')
		{
		if ($Second->Get('id'))
			{
			$table = $Second->table();
			$table_id = $Second->Get('id');
			}
		elseif ($First->Get('id'))
			{
			$table = $First->table();
			$table_id = $First->Get('id');
			}
		else
			{
			dn("Unexpected state found. Fix me Ben!");
			}
		}
	$this->table = $table;
	$this->table_id = $table_id;
	return true;
	}

function table()
	{
	return $this->table;
	}

function isClaimed()
	{
	return $this->get('authlogins_id');
	}

function get($field=null)
	{
	if (is_null($field))
		return $this->dis();
	if ($field == 'table')
		return $this->table;
	if ($field == 'table_id')
		return $this->table_id;
	$check = [ $this->First, $this->Second ];
	foreach($check AS $O)
	 if ($O->Get($field, 'exists'))
		{
		$val = $O->Get($field);
		if (!is_null($val))
			if ($field != 'id' || $O->table() == $this->table)
			return $val;
		}
	return null;
	}

/**
 * Sets values for the First ORM object passed in.
 *
 * @param type $a
 * @param type $b
 * @return type
 */
function set($a=null, $b=null)
	{
	return $this->First->Set($a, $b);
	}

function save()
	{
	return $this->First->Save();
	}

function dis()
	{
	$fdis = $this->First->Get();
	$sdis = $this->Second->Get();
	$return = [
		'table' => $this->table,
		'table_id' => $this->table_id,
		];
	foreach($fdis AS	$field => $val)
	 if ($field != 'id')
		{
		if (!is_null($val))
			{
			$return[$field]=$val;
			}
		elseif (!isset($sdis[$field]))
			{}
		elseif (!is_null($val = $sdis[$field]))
			$return[$field]=$val;
		unset($sdis[$field]);
		}
	foreach($sdis AS $field => $val)
	 if ($field != 'id')
		$return[$field]=$val;
	return $return;
	}

}
