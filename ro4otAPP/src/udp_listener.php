<?php
require_once 'Components/PHP/Logger.php';

$logger = logger::getInstance();
$logger->changePath('Jsons/LogHistory.json');
//THIS SCRIPT YOU MUST RUN IN TERMINAL (>'-'<)
//php .\udp_listener.php
$socket = stream_socket_server("udp://0.0.0.0:4040", $errno, $errstr, STREAM_SERVER_BIND);
echo "ODPALONE\n";

while (true) {
    $pkt = stream_socket_recvfrom($socket, 1024, 0, $peer);
    if ($pkt) {
        $logger->log(trim($pkt));
        echo date('H:i:s') . ' ' . trim($pkt) . "\n";
    }
}
?>