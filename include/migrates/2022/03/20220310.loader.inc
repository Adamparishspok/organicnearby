<?php


$sql[]="ALTER TABLE sc_products
	ADD pricetxt VARCHAR,
	ADD price_unit VARCHAR,
	ADD linkcart VARCHAR";

$sql[]="ALTER TABLE products
	ADD pricetxt VARCHAR,
	ADD price_unit VARCHAR,
	ADD linkcart VARCHAR";
