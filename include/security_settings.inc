<?php

$perms = [
	'/admin/farmer.php' => [
			'type' => 'page',
			'roles' => ['admin'],
			'perms' => ['execute']],
	'/admin/farmers.php' => [
			'type' => 'page',
			'roles' => ['admin'],
			'perms' => ['execute']],
	'/admin/products.php' => [
			'type' => 'page',
			'roles' => ['admin'],
			'perms' => ['execute']],
	'/admin/todo.php' => [
			'type' => 'page',
			'roles' => ['admin'],
			'perms' => ['execute']],
	'/account.php' => [
			'type' => 'page',
			'roles' => ['farmers'],
			'perms' => ['execute']],
	'/contact.php' => [
			'type' => 'page',
			'roles' => [''],
			'perms' => ['execute']],
	'/farmer.php' => [
			'type' => 'page',
			'roles' => [''],
			'perms' => ['execute']],
	'/farmers.php' => [
			'type' => 'page',
			'roles' => [''],
			'perms' => ['execute']],
	'/index.php' => [
			'type' => 'page',
			'roles' => [''],
			'perms' => ['execute']],
	'/img.php' => [
			'type' => 'page',
			'roles' => [''],
			'perms' => ['execute']],
	'/location.php' => [
			'type' => 'page',
			'roles' => [''],
			'perms' => ['']],
	'/newsletter.php' => [
			'type' => 'page',
			'roles' => [''],
			'perms' => ['execute']],
	'/product.php' => [
			'type' => 'page',
			'roles' => [''],
			'perms' => ['execute']],
	];

$roles_query = "SELECT
		'farmers' AS role,
		1 AS role_id
	FROM authlogins
	JOIN farmers ON
		(
		authlogins.id = [authlogins.id]
		AND authlogins.id = farmers.authlogins_id
		)
	UNION
	SELECT
		'admin' AS role,
		2 AS role_id
	FROM authlogins
	JOIN admin ON
		(
		authlogins.id = [authlogins.id]
		AND authlogins.id = admin.authlogins_id
		)
	";
