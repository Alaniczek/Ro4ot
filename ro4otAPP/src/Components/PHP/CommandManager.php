<?php
require_once 'Logger.php';

class CommandManager {
    private $esp_ip;
    private $esp_port;
    private $logger;
    private $commandListPath = '../../Jsons/Command.json';
    private $commandList = [];

    public function __construct($esp_ip, $esp_port , $logger) {
        $this->esp_ip = $esp_ip;
        $this->esp_port = $esp_port;
        $this->logger = $logger;
    }
    public function __constuct(RobotManualFinder $finder, Logger $logger) {
        $this->esp_ip = $finder->Ip;
        $this->esp_port = $finder->Port;
        $this->logger = $logger;
    }

    public function changeCommandListPath($newPath) {
        $this->commandListPath = $newPath;
    }

    public function GetCommandList()
    {
        $commandList = file_exists($this->commandListPath) ? json_decode(file_get_contents($this->commandListPath), true) : [];
        return $commandList;
    }

    public function delateCommandByName($name) 
    {
        $commandList = $this->GetCommandList();
        unset($commandList[$name]);
        file_put_contents($this->commandListPath, json_encode($commandList, JSON_PRETTY_PRINT));
    }
    public function delateCommandByOrder($order){
        $commandList = $this->GetCommandList();
        foreach($commandList as $key => $value)
        {
            if($value['order'] == $order)
            {
                unset($commandList[$key]);
                break;
            }
        }
        file_put_contents($this->commandListPath, json_encode($commandList, JSON_PRETTY_PRINT));
    }

    public function addCommand($name , $category, $order) {
        $list = $this->getCommandList();
        $list[$name] = [
            'category' => $category,
            'order' => $order
        ];
        file_put_contents($this->commandListPath, json_encode($list, JSON_PRETTY_PRINT));
    }
    
    
    public function sendCommand($cmd) {
        // Log the command being sent
        $this->logger->log("Wysłano komendę do ESP: " . $cmd);

        // Wyślij UDP do robota
        $sock = fsockopen("udp://{$this->esp_ip}", $this->esp_port);
        if ($sock) {
            fwrite($sock, $cmd);
            fclose($sock);
        }
    }
}

//CM = new CommandManager("192.168.1.100", 8266 , logger::getInstance());
/// Test if CommandManager works
//CM->addCommand("test_commandd", "test_categoryy", "test_order");
//commands = $CM->GetCommandList();
//CM->delateCommandByName("test_commandd");
//ar_dump($commands);