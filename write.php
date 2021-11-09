<?php
require('/var/www/telepathy-monitor.site/telepathy-monitor.site/config/connection.php');
require('/var/www/telepathy-monitor.site/telepathy-monitor.site/worldmap/autoload.php');
use GeoIp2\Database\Reader;
$reader = new Reader("worldmap/GeoLite2-City.mmdb");

if (isset($_POST["doing"])) {
    $card = $_POST["card"];
    $ip = $_SERVER['REMOTE_ADDR'];
    $record = $reader->city($ip);
    $country = $record->country->name;
    $state = $record->mostSpecificSubdivision->name;
    $city = $record->city->name;
    $latitude = $record->location->latitude;
    $longitude = $record->location->longitude;
    $hostname = gethostbyaddr($ip); 
    $ping_pdo->exec("INSERT INTO `cardhistory`(`card`, `country`, `state`, `city`, `latitude`, `longitude`, `ip`, `hostname`) VALUES ('$card','$country','$state','$city','$latitude','$longitude','$ip','$hostname')");
    $ping_pdo->exec("UPDATE `lastcard` SET `card`='$card',`country`='$country',`state`='$state',`city`='$city',`latitude`='$latitude',`longitude`='$longitude',`time`=now() WHERE `id`=1;");
}
date_default_timezone_set('Europe/Berlin');
require_once ('/var/www/mailer.php');
$mail = new PHPMailer();
$mail->IsSendmail();
$mail->SetFrom('f@telepathy-monitor.site','Felix Longolius');
$mail->AddAddress('f@fleo.at');
$mail->Subject = 'telepathy-monitor.site';
$mail->IsHTML(false);
$mail->CharSet = 'UTF-8';
$mail->Body = $card.' | '.$ip.' | '.$hostname.' | '.date('l jS \of F Y H:i:s');
$mail->Send();
