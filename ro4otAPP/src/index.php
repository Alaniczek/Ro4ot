<?php
require_once 'Components/PHP/Logger.php';
require_once 'Components/PHP/QueueManager.php';
require_once 'Components/PHP/RobotManager.php';

session_start();

$Logger = logger::getInstance();
$Logger->changePath('Jsons/LogHistory.json');

$Queue = new QueueManager('Jsons/Queue.json');

$robotManager = RobotManager::getInstance();
$robotManager->changePath('Jsons/RobotUnits.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['RM_SelectRobot'])) {
        $_SESSION['selected_robot'] = $_POST['RM_Selected_Name'];
    }
}

if (isset($_SESSION['selected_robot'])) {
    $robotManager->selectRobotByName($_SESSION['selected_robot']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'clear') {
            $Logger->clearLog();
        } else {
            $robotManager->sendCommandToSelectedRobot($_POST['action']);
        }
    }

    if (isset($_POST['AddQueue'])) {
        $action = $_POST['AddQueue'];
        if ($action === 'clearQueue') {
            $Queue->clear();
        } elseif ($action === 'startQueue') {
            $cmd = $Queue->pop();
            if ($cmd) {
                $robotManager->sendCommandToSelectedRobot($cmd);
                $Logger->log("Wysłano z kolejki: " . $cmd);
            }
        } else {
            $Queue->add($action);
        }
    }

    if (isset($_POST['Searcher'])) {
        $robotManager->sendCommandToSelectedRobot("I {$_POST['PC_IP']} {$_POST['PC_port']}X");
    }
//    else if(isset($_POST['AutoSearcher'])){
//        $localIP = getHostByName(getHostName());
//        $robotManager->sendCommandToSelectedRobot("I {$localIP} {$_POST['PC_port']}X");
//    }

    if (isset($_POST['RM_Create'])) {
        $ip = $_POST['RM_esp_ip'] ?? '';
        $port = $_POST['RM_esp_port'] ?? '';
        $name = $_POST['RM_Name'] ?? '';
        $model = $_POST['RM_Model'] ?? 'UNKNOWN';

        if (!empty($name) && !empty($ip) && !empty($port)) {
            $robotManager->createRobot($name, $ip, $port, $model);
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
echo $_SERVER['SERVER_PORT'];
$logs = $Logger->GiveEveryLogs();
$currentQueue = $Queue->getItems();
?>
<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <title>Sterowanie Robotem</title>
    <link rel="stylesheet" href="Components/CSS/SettingsMenu.css">
    <link rel="stylesheet" href="Components/CSS/ControlBridge.css">
</head>
<body>
<script src="Components/JS/ButtonMaker.js"></script>
<h1>Panel Sterowania</h1>
<div class="ControlBridge">
    <div class="ControlSection">
        <form method="post">
            <div class="CommandButtons"></div>
            <button type="submit" name="action" value="clear"
                    style="font-size: 20px; padding: 10px; background: #D3D3D3; float: right;">LOG CLEAR
            </button>
        </form>
        <h3>LOGI SYSTEMOWE</h3>
        <textarea style="width: 100%; height: 300px; font-family: monospace;">
        <?php
        $logger = logger::getInstance();
        $logs = $logger->GiveEveryLogs();

        foreach ($logs as $log) {
            echo "[" . $log['data'] . "] " . $log['zdarzenie'] . "\n";
        }
        ?>
        </textarea>
    </div>

    <div class="QueueSection">
        <h2>ADD TOQUEUE</h2>
        <h5>IT IS NOT AUTOMATIC, YOU MUST CLICK TO SEND :> </h5>

        <form method="post">
            <div class="QueueButtons"></div>
            <button type="submit" name="AddQueue" value="clearQueue" id="SubmitQueue">CLEAR QUEUE</button>
            <button type="submit" name="AddQueue" value="startQueue" id="StartQueue">START QUEUE</button>
        </form>
        <textarea name="QueueContent" id="QueueContent" style="width: 100%; height: 100px; font-family: monospace;">
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
    </div>
</div>

<div class="ActiveRobotsSection">
    <h3>ACTIVE ROBOTS - SOON -</h3>
</div>

<div class="SettingsMenu">
    <div id="IP_Port_Form">
        <form method="post">
            <h4>Send Ip and port of your PC to ESP</h4>
            <input type="text" placeholder="Wpisz IP ESP" name="PC_IP">
            <!--    <input type="text" placeholder="Wpisz port" name="esp_port">-->
            <input type="text" placeholder="Wpisz port odbioru PC" name="PC_port">
            <input type="submit" value="Send_IP_PORT" name="Searcher">
            <input type="submit" value="Auto_Send_IP_PORT" name="AutoSearcher">
        </form>
    </div>
    <div id="RobotMaker_Form">
        <form method="post">
            <!--    RM = ROBOT MAKER-->
            <h4>RobotMaker</h4>
            <input type="text" placeholder="Wpisz IP" name="RM_esp_ip" required>
            <input type="text" placeholder="Wpisz port" name="RM_esp_port" required>
            <input type="text" placeholder="Wpisz Nazwe BEZ SPACJI!" name="RM_Name" required>
            <input type="text" placeholder="Wpisz MODEL" name="RM_Model">
            <input type="submit" value="Dodaj Robota" name="RM_Create">
        </form>
    </div>
    <div id="RobotSelector_Form">
        <form method="post">
            <h4>Wybierz Robota</h4>
            <select name="RM_Selected_Name" required>
                <?php
                $robots = $robotManager->getRobotUnits();
                foreach ($robots as $robot):
                    ?>
                    <option value="<?= $robot['Name'] ?>"><?= $robot['Name'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Wybierz" name="RM_SelectRobot">
        </form>
    </div>
</div>

<script>
    const maker = new ButtonMakerFromJSON('Jsons/Command.json');

    // Używasz poprawnej metody 'render' z odpowiednim parametrem name
    maker.render('.CommandButtons', 'action');
    maker.render('.QueueButtons', 'AddQueue');
</script>
</body>
</html>
