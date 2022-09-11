<?php

$sql[]="CREATE TABLE newsletter
	(id SERIAL PRIMARY KEY,
	email VARCHAR NOT NULL,
	inserted_ts INTEGER DEFAULT NULL,
	stopped_ts INTEGER DEFAULT NULL
	)";
