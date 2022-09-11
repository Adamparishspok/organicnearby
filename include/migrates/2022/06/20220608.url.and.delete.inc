<?php

$sql[]="ALTER TABLE farmers
	ADD url VARCHAR UNIQUE DEFAULT null,
	ADD deleted_ts INTEGER DEFAULT null";

$sql[]="ALTER TABLE products
	ADD deleted_ts INTEGER DEFAULT NULL";

$sql[]="ALTER TABLE sc_farmers
	ADD deleted_ts INTEGER DEFAULT NULL";

$sql[]="ALTER TABLE sc_products
	ADD deleted_ts INTEGER DEFAULT NULL";
