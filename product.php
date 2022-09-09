<?php

#DESCRIPTION Displays a product to an end user.
namespace sb;
class PageProduct extends \sb\IncludePageOrganicnearby
{

function init()
	{
	return $this->getTableAndId('product.php');
	}

function finit()
	{
	return true;
	}

function page_prompt()
	{
	$dis = &$this->dis['Prompt'];
	$dis['show'] = '';

	if (!$fP = $this->resolveProdScraped($table, $table_id, $P, $SP))
		return false;
	$dis = $fP->get();

	$F = $this->DB->FindOrCreate('farmers', $P->Get('farmers_id'), 1);
	$SF = $this->DB->findOrCreate('sc_farmers', $SP->Get('sc_farmers_id'), 1);
	$fF = new IncludeOrmFake($F, $SF, 'guess', null, null);

	if ($this->SEC->Get('id') && $fF->Get('authlogins_id') == $this->SEC->Get('id'))
		$dis['Edit'] = [
			'table' => $fP->Get('table'),
			'table_id' => $fP->get('table_id'),
			'farmers_id' => $fF->First->Get('id'),
			'urlname' => $this->urlify($fF->Get('name')),
			];

	if ($location = $fP->Get('location'))
		{}
	else
		$location = $fF->Get('location');

	if ($location)
		{
		$loc = IncludeGet::Loc2Array($location);
		$dis['Map'] = $loc;
		}

	$fields = [ 'price', 'stock' ];
	foreach($fields AS $field)
		{
		$dis['FValues'][]=[
			'field' => ucfirst($field),
			'value' => $fP->Get($field),
			];
		}
	if ($dis['linkcart'])
		{
		$dis['LinkCart'] = [
			'link' => $dis['linkcart'],
			'table' => $table,
			'table_id' => $table_id,
			];
		}

	$dis['farmer'] = $fF->Get('name');
	$dis['farmer_table'] = substr($table, 0, 3) == 'sc_' ? 'sc_farmers' : 'farmers';
	$dis['farmer_table_id'] = ($dis['farmer_table'] == 'farmers') ?
		$F->Get('id') : $SF->Get('id');
	$dis['farmer_urlify'] = $this->urlify($fF->Get('name'));
	return true;
	}

function page_purchase()
	{
	if (!$fP = $this->resolveProdScraped($table, $table_id, $P, $SP))
		return false;
	if (!$linkcart = $fP->Get('linkcart'))
		return $this->error("Unable to find target link");
	Header("Location: $linkcart");
	$this->renderTo('none');
	return true;
	}

}
