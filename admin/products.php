<?php

namespace sb;

class PageAdminProducts extends \sb\IncludePageOrganicnearby
	{
	var $productFields = [ 'name', 'price', 'pricetxt', 'price_unit', 'address', 'description', 'stock', 'linkcart' ];

	function init()
		{
		return true;
		}

	function finit()
		{
		return true;
		}

	function page_prompt()
		{
		$dis = &$this->dis['Prompt'];
		$this->resolveLatLon($lat, $lon);
		$dis['lat'] = $lat;
		$dis['lon'] = $lon;
		$todb = [];

		$fF = $this->resolveFarmer($table, $table_id, $F, $SF);
		if ($F && $SF)
			$this->farmerInfo($dis, $F, $SF);

		$where = '';
		if ($table)
			{
			$where = "AND $table"."_id = [$table.id]";
			$todb["$table.id"] = $table_id;
			}

		$search = $this->Req('text', 'search');
		$dis['search'] = $search;
		if (!$res = $this->productSearch($lat, $lon, $search, $where, $todb, 'name'))
			return false;
		
		if (!$offset = $this->req('int', 'offset'))
			$offset=0;
		$start = $offset;
		$paginate = 50;
		$finish = $start + $paginate;
		if ($finish > $res->numRows())
			$finish = $res->numRows();
		$count = $finish - $start;
		$total = $res->numRows();
		$dis['count_msg'] = "Displaying $start to $finish of $total.";
		for ($i=$start; $i<$finish; $i++)
			{
			$row = $res->Next($i);

			$row['distance'] = number_format(IncludeGet::km2miles($row['sc_farmers_km']), 1);
			/**
			 * Used to create the field list above.
			foreach($row AS $f => $ig)
				echo "sc_farmers.$f AS farmers_$f,\n";
			foreach($row AS $f => $ig)
				echo "farmers.$f AS farmers_$f,\n";
			dn($row);
			 *
			 */
			$pfields = ['name', 'price', 'stock', 'address', 'image'];
			foreach($pfields AS $pfield)
				{
				$row[$pfield . "Class"]= (is_null($row["products_$pfield"])) ? 'scraped' : 'overridden';
				if ($t = retval($row, "products_$pfield"))
					$row[$pfield] = $t;
				elseif ($t = retval($row, "sc_products_$pfield"))
					$row[$pfield] = $t;
				elseif ($t = retval($row, "farmers_$pfield"))
					$row[$pfield] = $t;
				elseif ($t = retval($row, "sc_farmers_$pfield"))
					$row[$pfield] = $t;
				}
//			$row['image'] = $row['sc_products_image'];
			// prefer to link to sc_products so that admin
			// can fix records.
			if ($row['table_id'] = $row['sc_products_id'])
				$row['table'] = 'sc_products';
			else
				{
				$row['table_id'] = $row['products_id'];
				$row['table'] = 'products';
				}
//			$row['farmers_externalid'] = $f_externalid;
			if (!retval($row, 'image'))
				$row['imagena'] = 'No Image';
			else
				$row['Image'] = ['image' => $row['image']];

			$row['search'] = $this->Req('text', 'search');
			$row['offset'] = $this->req('int', 'offset');
			$dis['Products'][]=$row;
			}
		if ($offset >= $paginate)
			{
			$set = [
				'offset' => $offset-$paginate,
				'search' => $search,
				];
			$dis['LinkPrev'] = $set;
			$dis['LinkPrevBottom'] = $set;
			}
		if ($offset + $paginate < $total)
			{
			$set = [
				'offset' => $offset+$paginate,
				'search' => $search,
				];
			$dis['LinkNext'] = $set;
			$dis['LinkNextBottom'] = $set;
			}
		return true;
		}


	/**
	 * Gets and displays farmer info
	 *
	 * @param array $dis
	 * @param alphanumeric $f_externalid obtained from remote table.
	 * @return boolean
	 */
	function farmerInfo(&$dis, $F, $SF)
		{
		$fields = [ 'name', 'website', 'phone', 'company_email' ];
		$Farmer = [];
		foreach($fields AS $field)
			{
			$Farmer[$field . "Class"] = is_null($F->Get($field)) ? 'scraped' : 'overridden';
			$Farmer[$field] = is_null($F->Get($field)) ? $SF->Get($field) : $F->Get($field);
			}
		if ($F->Get('authlogins_id', 1))
			{
			$Farmer['table'] = 'farmers';
			$Farmer['table_id'] = $F->Get('id');
			}
		else
			{
			$Farmer['table'] = 'sc_farmers';
			$Farmer['table_id'] = $SF->Get('id');
			}
		$dis['Farmer'] = $Farmer;
		return true;
		}

	function resolveProducts(&$table, &$table_id, &$P, &$SP)
		{
		if (!$table_id = $this->req('int', 'table_id'))
			return $this->error("Unable to get scraped product id");
		if (!$table = $this->req('text', 'table'))
			return $this->error("Unable to get product table");

		if (!in_array($table, [ 'products', 'sc_products' ]))
			return $this->error("Invalid table");

		if ($table == 'sc_products')
			{
			$SP = $this->DB->find($table, $table_id, true);
			$find = ['externalid' => $SP->Get('externalid')];
			$P = $this->DB->findOrCreate('products', $find, true);
			}
		else
			{
			$P  = $this->DB->find($table, $table_id, true);
			if (!$P->Get('externalid'))
				$SP = $this->DB->Create('sc_products', [], true);
			else
				{
				$find = ['externalid' => $P->Get('externalid') ];
				if (!$SP = $this->DB->Find('sc_products', true))
					return $this->error("How does a farmer record have an externalid without a matching scrape record?");
				}
			}
		$return = new IncludeOrmFake($P, $SP, $table, $table_id);
		return $return;
		}
	function page_edit()
		{
		$dis = &$this->dis['Edit'];
		if (!$this->resolveProducts($table, $table_id, $P, $SP))
			return false;
		if (!$SF = $this->DB->findOrCreate('sc_farmers', $SP->Get('sc_farmers_id'), true))
			return $this->error("Uanble to get scraped farmer record - how is this possible?");
		if (!$F = $this->DB->findOrCreate('farmers', $P->Get('farmers_id'), true))
			return $this->error("Uanble to get farmer record - how is this possible?");

		$this->farmerInfo($dis, $F, $SF);

		$this->displayProduct($P, $SP, $dis);
		$dis['table'] = $table;
		$dis['table_id'] = $table_id;
		$dis['search'] = $this->req('text', 'search');
		$dis['offset'] = $this->req('int', 'offset');
		return true;
		}

	function displayProduct($P, $SP, &$dis)
		{
		foreach($this->productFields AS $field)
			{
			$checked = 'CHECKED';
			if (!is_null($SP->Get($field)))
				$checked = (is_null($P->Get($field))) ? '' : 'CHECKED';
			$disabled = $checked ? '' : 'DISABLED';
			$dis["fval_$field"] = $SP->Get($field);
			$dis["eval_$field"] = (!is_null($P->Get($field))) ? $P->Get($field) : $SP->Get($field);
			$dis["checked_$field"] = $checked;
			$dis["disabled_$field"] = $disabled;
			}
		$dis['eval_price'] = str_replace('$', '', $dis['eval_price']);
		$dis['scraped_display'] = $SP->Get('id') ? '' : 'hideme';
		return true;
		}


	/**
	 * Look for the farmer record, and if not found, make one. 
	 * @param type $SP
	 */
	function ensureFarmerExists($SP, $P)
		{
		if ($f_id = $P->Get('farmers_id'))
			{
			$F = $this->DB->Find('farmers', $f_id, true);
			}
		elseif (!$sc_farmers_id = $SP->Get('sc_farmers_id'))
			{}
		elseif (!$SF = $this->DB->Find('sc_farmers', $sc_farmers_id, true))
			return $this->error("Unable to get scraped farmer record, this should never happen.");
		elseif (!$ffind = [ 'externalid' => $SF->Get('externalid')] )
			{}
		elseif (!$F = $this->DB->findOrCreate('farmers', $ffind, true))
			return $this->error("How does this hapen?");
		if (!$F)
			return $this->error("There was a problem resolving Farmer record");
		$F->Set('update_ts', time());
		if (!$F->Get('insert_ts'))
			$F->Set('insert_ts', $F->Get('update_ts'));
		if (!$F->Get('id'))
			{
			$F->Set('sc_sources_id', $SF->Get('sc_sources_id'));
			$F->Save();
			}
		return $F;
		}

	function page_save()
		{
		if (!$this->resolveProducts($table, $table_id, $P, $SP))
			return false;
		$this->DB->Transaction('begin', 'productsSave');
		if (!$monitor = $this->request['monitor'])
			{}
		else foreach($monitor AS $field => $ig)
			{
			if ($this->req('int', 'active', $field))
				$P->Set($field, $this->req('text', $field));
			else
				$P->Set($field, NULL);
			}
		if (!$F = $this->ensureFarmerExists($SP, $P))
			return $this->Error("Unable to resolve associated Farmer record");
		$P->Set('farmers_id', $F->Get('id'));
		if (!$P->Get('insert_ts'))
			$P->Set('insert_ts', time());
		$P->Set('update_ts', time());
		if (!$P->Save())
			return $this->error("Unable to save result from update");
		$dis = &$this->dis['Save'];
		$dis['farmers_externalid'] = $this->Req('text', 'farmers_externalid');
		$dis['search'] = $this->Req('text', 'search');
		$dis['offset'] = $this->req('int', 'offset');
		$this->DB->Transaction('commit', 'productsSave');
		return true;
		}

	}
