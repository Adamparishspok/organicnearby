<?php

namespace sb;
trait OrmFarmersTraits
{

function on_presave()
	{
	if (!$this->get('insert_ts'))
		$this->set('insert_ts', time());
	$this->set('update_ts', time());
	if ($this->get('address') == $this->get('location_addr'))
		return true;
	if (!$this->get('address'))
		return true;
	$IG = new IncludeGeoapify($this->env);
	if (!$loc = $IG->addressTextToLatLon($this->get('address')))
		return true;
	if (!$this->set('location', implode(",", $loc)))
		return $this->error("Unable to set discovered location");
	if (!$this->set('location_addr', $this->get('address')))
		return $this->error("Unable to set location address");
	return true;
	}

}

class IncludeOrmFactoryresultFarmers extends IncludeOrmFactoryresult
{
use OrmFarmersTraits;
}

class IncludeOrmCrudFarmers extends IncludeOrmCrud
{
use OrmFarmersTraits;

function isClaimed()
	{
	return $this->get('authlogins_id');
	}

}