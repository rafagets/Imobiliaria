<?php
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "everton");

define("DB_DSN_MYSQL", 'mysql:host='.DB_HOST.';dbname='.DB_NAME);
define("DB_DSN_FIREBIRD", 'firebird:host='.DB_HOST.';dbname='.DB_NAME);

date_default_timezone_set("America/Sao_Paulo");
?>