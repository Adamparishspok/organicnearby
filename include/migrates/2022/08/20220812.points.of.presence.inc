<?php

$skip = true;

/**
 * Can be a building for the vendor, can be a store, can be a farmer's market,
 * it could be a farm.
 */
$sql[] = "CREATE TABLE pops (
	id SERIAL PRIMARY KEY,
	name VARCHAR NOT NULL,
	address VARCHAR,
	location GEOGRAPHY(point),
	location_addr VARCHAR
	)";

$sql[]="CREATE TABLE pops2farmers (
	id SERIAL PRIMARY KEY,
	pops_id INTEGER NOT NULL REFERENCES pops(id),
	farmers_id INTEGER NOT NULL REFERENCES farmers(id),
	unique(pops_id, farmers_id)
	)";

$sql[]="CREATE TABLE pops2products (
	id SERIAL PRIMARY KEY,
	pops_id INTEGER NOT NULL REFERENCES pops(id),
	products_id INTEGER NOT NULL REFERENCES products(id),
	unique(pops_id, products_id)
	)";