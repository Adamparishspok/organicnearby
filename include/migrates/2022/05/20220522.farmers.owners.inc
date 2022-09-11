<?php

$sql[]="ALTER TABLE farmers ADD authlogins_id INTEGER UNIQUE DEFAULT NULL REFERENCES authlogins(id)";
$sql[]="ALTER TABLE farmers ALTER externalid DROP NOT NULL";
$sql[]="ALTER TABLE farmers ALTER insert_ts SET DEFAULT EXTRACT(EPOCH FROM NOW ())";
$sql[]="ALTER TABLE farmers ALTER update_ts SET DEFAULT EXTRACT(EPOCH FROM NOW ())";
$sql[]="INSERT INTO farmers (authlogins_id) SELECT authlogins_id FROM users";
