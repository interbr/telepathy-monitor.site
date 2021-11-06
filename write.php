<?php
require('/var/www/telepathy-monitor.site/telepathy-monitor.site/config/connection.php');
if (isset($_POST["doing"])) {
    $card = $_POST["card"];
    $ip = $_SERVER['REMOTE_ADDR'];
    $hostname = gethostbyaddr($ip); 
    $ping_pdo->exec("INSERT INTO `cardhistory`(`card`, `coords`, `ip`, `hostname`) VALUES ('$card','none','$ip','$hostname')");
    $ping_pdo->exec("UPDATE `lastcard` SET `card`='$card',`coords`='none' WHERE `id`=1;");
}