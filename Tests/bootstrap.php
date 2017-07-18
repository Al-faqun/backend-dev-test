<?php
require_once(dirname(__FILE__, 2) . '/vendor/autoload.php');

$dbname = 
$username =
$password =
$dsn = "mysql:host=localhost;dbname={$dbname};charset=utf8";
$opt = array(
	\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
	\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
	\PDO::ATTR_EMULATE_PREPARES => false,
	\PDO::MYSQL_ATTR_FOUND_ROWS => true
);
$test_pdo = new \PDO($dsn, $username, $password, $opt);
