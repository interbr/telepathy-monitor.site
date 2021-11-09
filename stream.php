<?php
ignore_user_abort(true);
header("Cache-Control: no-cache");
header("Content-Type: text/event-stream");
header('X-Accel-Buffering: no');
require('/var/www/telepathy-monitor.site/telepathy-monitor.site/config/connection.php');
require('/var/www/telepathy-monitor.site/telepathy-monitor.site/worldmap/autoload.php');
use GeoIp2\Database\Reader;
$reader = new Reader("worldmap/GeoLite2-City.mmdb");

$serverTime = 1;

$ip = $_SERVER['REMOTE_ADDR'];
$record = $reader->city($ip);
$country = $record->country->name;
$state = $record->mostSpecificSubdivision->name;
$city = $record->city->name;
$latitude = $record->location->latitude;
$longitude = $record->location->longitude;
$hostname = gethostbyaddr($ip); 
$ping_pdo->exec("INSERT INTO `cardhistory`(`card`, `country`, `state`, `city`, `latitude`, `longitude`, `ip`, `hostname`) VALUES ('0','$country','$state','$city','$latitude','$longitude','$ip','$hostname')");
$tele_id = $ping_pdo->lastInsertId();

date_default_timezone_set('Europe/Berlin');
require_once ('/var/www/mailer.php');
$mail = new PHPMailer();
$mail->IsSendmail();
$mail->SetFrom('f@telepathy-monitor.site','Felix Longolius');
$mail->AddAddress('f@fleo.at');
$mail->Subject = 'connect telepathy-monitor.site';
$mail->IsHTML(false);
$mail->CharSet = 'UTF-8';
$mail->Body = $ip.' | '.$hostname.' | '.$country.' | '.$state.' | '.$city.' | '.$latitude.' | '.$longitude.' | '.date('l jS \of F Y H:i:s');
$mail->Send();

$card2stream = $ping_pdo->prepare("SELECT * FROM `lastcard` WHERE `id`=1;");
$last102stream = $ping_pdo->prepare("SELECT `card`, latitude, longitude, `time` FROM `cardhistory` ORDER BY id DESC LIMIT 1000;");
$lastVisit2stream = $ping_pdo->prepare("SELECT `card`, latitude, longitude, `time` FROM `cardhistory` ORDER BY id DESC LIMIT 1;");

$last102stream->execute();
$last102stream_datas = $last102stream->fetchAll(PDO::FETCH_OBJ);
echo 'id: ' . $serverTime . '', PHP_EOL;
echo 'event: telepathy-monitor.site.cardhistory', PHP_EOL;
echo 'data: ' . json_encode($last102stream_datas), PHP_EOL;
echo PHP_EOL;

$lastold = 0;
$lastVold = 0;

$countBasic = 0;

echo 'telepathy-monitor.site Ready', PHP_EOL;
echo PHP_EOL;

	while (1) {

        $serverTime = time();

        if ($countBasic % 50 == 49) {
            echo 'telepathy-monitor.site Ping (every 50 dits)', PHP_EOL;
            echo PHP_EOL;
        }

        $card2stream->execute();
		$card2stream_datas = $card2stream->fetchAll(PDO::FETCH_OBJ);

            foreach ($card2stream_datas as $card2stream_data) {
                $last = $card2stream_data->time;
                if ($last > $lastold) {
			echo 'id: ' . $serverTime . '', PHP_EOL;
			echo 'event: telepathy-monitor.site.card', PHP_EOL;
			echo 'data: ' . json_encode($card2stream_datas), PHP_EOL;
			echo PHP_EOL;
                $lastold = $last;
            }
        }

        $lastVisit2stream->execute();
		$lastVisit2stream_datas = $lastVisit2stream->fetchAll(PDO::FETCH_OBJ);

        foreach ($lastVisit2stream_datas as $lastVisit2stream_data) {
            $lastV = $lastVisit2stream_data->time;
            if ($lastV > $lastVold) {
            if ($lastVisit2stream_data->card == "0" || $lastVisit2stream_data->card == "6") {
        echo 'id: ' . $serverTime . '', PHP_EOL;
        echo 'event: telepathy-monitor.site.cardhistory', PHP_EOL;
        echo 'data: ' . json_encode($lastVisit2stream_datas), PHP_EOL;
        echo PHP_EOL;
        $lastVold = $lastV;
            }
        }
    }

		while (ob_get_level() > 0) {
			ob_end_flush();
			}
			flush();
        if (connection_aborted()) {
            $ping_pdo->exec("INSERT INTO `cardhistory`(`card`, `country`, `state`, `city`, `latitude`, `longitude`, `ip`, `hostname`) VALUES ('6','$country','$state','$city','$latitude','$longitude','$ip','$hostname')");
            break;
            }

	$countBasic++;

	sleep(1);
}
