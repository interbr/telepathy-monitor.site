<?php
header("Cache-Control: no-cache");
header("Content-Type: text/event-stream");
header('X-Accel-Buffering: no');
require('/var/www/telepathy-monitor.site/telepathy-monitor.site/config/connection.php');

$card2stream = $ping_pdo->prepare("SELECT * FROM `lastcard` WHERE `id`=1;");

$lastold = 0;

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

		while (ob_get_level() > 0) {
			ob_end_flush();
			}
			flush();

	if (connection_aborted()) break;

	$countBasic++;

	sleep(1);
        }