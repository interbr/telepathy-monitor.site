<?php
require_once('/var/www/telepathy-monitor.site/telepathy-monitor.site/config/configuration.php');
$ping_pdo = new PDO("mysql:host=199.217.112.159:3306;dbname=telepathy", "telepathy", $dbpw);