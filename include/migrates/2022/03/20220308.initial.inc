<?php

$sql[]="INSERT INTO authlogins
	(login, password, authmethod, active)
	VALUES
	('yesthatguy@gmail.com', 'mySecretGarden', 'plain', true)";

$sql[]="insert into authlogins
	(login, password, authmethod, active)
	values
	('jmswatzell@msn.com', 'iN8lIvingcOlor', 'plain', true);";

$sql[]="CREATE TABLE sc_farmers (
	id serial primary key,
	source varchar,
	name varchar not null,
	address varchar,
	phone varchar,
	website varchar,
	general_info varchar,
	company_email varchar,
	externalid varchar unique not null,
	insert_ts INTEGER NOT NULL,
	update_ts INTEGER NOT NULL,
	bubbleid varchar
	)";

$sql[]="CREATE TABLE farmers (
	id serial primary key,
	source varchar,
	name varchar,
	address varchar,
	phone varchar,
	website varchar,
	general_info varchar,
	company_email varchar,
	externalid varchar unique not null,
	insert_ts INTEGER NOT NULL,
	update_ts INTEGER NOT NULL,
	bubbleid varchar
	)";

$sql[]="CREATE TABLE sc_products (
	id SERIAL PRIMARY KEY,
	sc_farmers_id INTEGER NOT NULL REFERENCES sc_farmers(id),
	name VARCHAR,
	price MONEY,
	address VARCHAR,
	description VARCHAR,
	image VARCHAR,
	stock integer,
	externalid VARCHAR UNIQUE NOT NULL,
	insert_ts INTEGER NOT NULL,
	update_ts INTEGER NOT NULL,
	bubbleid varchar
	)";

$sql[]="CREATE TABLE products (
	id SERIAL PRIMARY KEY,
	farmers_id INTEGER NOT NULL REFERENCES farmers(id),
	name VARCHAR,
	price MONEY,
	address VARCHAR,
	description VARCHAR,
	image VARCHAR,
	stock integer,
	externalid VARCHAR UNIQUE NOT NULL,
	insert_ts INTEGER NOT NULL,
	update_ts INTEGER NOT NULL,
	bubbleid varchar
	)";

$sql[]="CREATE TABLE comments (
	id serial,
	authlogins_id INTEGER NOT NULL REFERENCES authlogins(id),
	tablename VARCHAR NOT NULL,
	table_id INTEGER NOT NULL,
	ts INTEGER NOT NULL,
	COMMENTS VARCHAR
	)";
