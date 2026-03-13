<?php
// --- KONFIGURACJA ---
require_once 'Components/PHP/Logger.php';
require_once 'Components/PHP/CommandManager.php';

$esp_ip = '192.168.243.143'; // ESP IP :> 
$esp_port = 4210;
// $plik_logow = 'logi.txt';


$Logger = logger::getInstance();
$Logger->changePath('Jsons/LogHistory.json');

if (isset($_GET['msg'])) {
    $wpis = date("H:i:s") . " [ESP -> PHP]: " . $_GET['msg'] . "\n";
    //file_put_contents($plik_logow, $wpis, FILE_APPEND);
    $Logger->log("ESP wysłał wiadomość: " . $_GET['msg']);
    exit;
}

$CommandManager = new CommandManager($esp_ip, $esp_port, $Logger);
if (isset($_POST['action'])) {
    $cmd = $_POST['action'];

    if ($cmd === 'clear') {
        //file_put_contents($plik_logow, "");
        $Logger->clearLog();
    } 
    else {
        $wpis = date("H:i:s") . " [PHP -> ESP]: Wyslano komende " . $cmd . "\n";
        //file_put_contents($plik_logow, $wpis, FILE_APPEND);
        //$Logger->log("Wysłano komendę do ESP: " . $cmd);
        
        $CommandManager->sendCommand($cmd);
    }

    // Odśwież stronę (czyści formularz przed F5)
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit;
}

// IT'S NOT 100% QUEUE :< 
if (isset($_POST['AddQueue']))
{
    $queueContent = json_decode(file_get_contents('Jsons/Queue.json'), true);

    $action = $_POST['AddQueue'];
    if($action === 'clearQueue')
    {
        file_put_contents('Jsons/Queue.json', json_encode([]));
    }else if($action === 'startQueue') {
        $order = $queueContent[0] ?? null;
        if ($order) {
            $CommandManager->sendCommand($order);
            $wpis = date("H:i:s") . " [PHP -> ESP]: Wyslano komende " . $order . "\n";
            //file_put_contents($plik_logow, $wpis, FILE_APPEND);
            $Logger->log("Wysłano komendę do ESP: " . $order);

            array_shift($queueContent); 
            file_put_contents('Jsons/Queue.json', json_encode($queueContent, JSON_PRETTY_PRINT));
        }
    }else
    {
        if (!is_array($queueContent)) {
            $queueContent = [];
        }
        $queueContent[] = $action;
        file_put_contents('Jsons/Queue.json', json_encode($queueContent, JSON_PRETTY_PRINT));
    }
}
//<button type="submit" name="AddQueue" value="clearQueue" style="font-size: 20px; padding: 10px; background: #D3D3D3;">CLEAR QUEUE</button>
//<button type="submit" name="AddQueue" value="startQueue" style="font-size: 20px; padding: 10px; background: #D3D3D3;">START QUEUE</button>
if(isset($_POST['Searcher']))
{
    $ip = $_POST['esp_ip'];
    $port = $_POST['esp_port'];
    //echo "Wpisane IP: " . $ip . "<br>";
    //echo "Wpisany Port: " . $port;
    $CommandManager->sendCommand('I'. ' ' . $ip . ' ' . $port . 'X');
    
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sterowanie Robotem</title>
</head>
<body>
    <form method="post">
    <h4>IP of ESP</h4>
    <input type="text" placeholder="Wpisz IP" name="esp_ip">
    <input type="text" placeholder="Wpisz port" name="esp_port">
    <input type="submit" value="Send_IP_PORT" name="Searcher">
</form>

    <script src="Components/JS/ButtonMaker.js"></script>
    <script src="Components/JS/ButtonForQueue.js"></script>

    <h1>Panel Sterowania</h1>
    <form method="post">
        <div class="CommandButtons"></div>
        <button type="submit" name="action" value="clear" style="font-size: 20px; padding: 10px; background: #D3D3D3; float: right;">LOG CLEAR</button>
    </form>
    <hr>
    <h3>LOGI SYSTEMOWE</h3>
    <textarea style="width: 100%; height: 300px; font-family: monospace;">
        <?php 
            // if (file_exists($plik_logow)) {
            //     echo file_get_contents($plik_logow);
            // }
            $logger = logger::getInstance();
            $logs = $logger->GiveEveryLogs();

            foreach ($logs as $log) {
                echo "[" . $log['data'] . "] " . $log['zdarzenie'] . "\n";
            }

        ?>
    </textarea>
    

    <hr>
    <h2>ADD TOQUEUE</h2>
    <h5>IT IT NOT AUTOMATIC, YOU MUST CLICK TO SEND :> </h5>
    
    <form method="post">
        <div class="QueueButtons"></div>
        <button type="submit" name="AddQueue" value="clearQueue" style="font-size: 20px; padding: 10px; background: #D3D3D3;">CLEAR QUEUE</button>
        <button type="submit" name="AddQueue" value="startQueue" style="font-size: 20px; padding: 10px; background: #D3D3D3;">START QUEUE</button>
    </form>
    <textarea name="QueueContent" id="QueueContent" style="width: 50%; height: 100px; font-family: monospace;">
        <?php
            $jsonFile = 'Jsons/Queue.json';

            if (file_exists($jsonFile)) {
                $content = file_get_contents($jsonFile);
                $queue = json_decode($content, true);
                echo json_encode(is_array($queue) ? $queue : []);
            } else {
                echo json_encode([]);
            }
        ?>
    </textarea>


    <script>
       const maker = new ButtonMakerFromJSON('Jsons/Command.json');
    
    // Używasz poprawnej metody 'render' z odpowiednim parametrem name
        maker.render('.CommandButtons', 'action'); 
        maker.render('.QueueButtons', 'AddQueue');
    </script>
</body>
</html>
