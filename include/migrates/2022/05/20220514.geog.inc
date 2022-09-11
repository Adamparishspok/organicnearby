<?php

$sql[]="ALTER TABLE sc_farmers ADD location GEOGRAPHY(POINT)";
$sql[]="ALTER TABLE sc_farmers ADD location_addr VARCHAR";

$sql[]="ALTER TABLE farmers ADD location GEOGRAPHY(POINT)";
$sql[]="ALTER TABLE farmers ADD location_addr VARCHAR";

$sql[]="ALTER TABLE sc_products ADD location GEOGRAPHY(POINT)";
$sql[]="ALTER TABLE sc_products ADD location_addr VARCHAR";

$sql[]="ALTER TABLE products ADD location GEOGRAPHY(POINT)";
$sql[]="ALTER TABLE products ADD location_addr VARCHAR";
