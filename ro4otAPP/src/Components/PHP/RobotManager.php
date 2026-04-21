<?php
require_once 'RobotKit.php';
require_once 'CommandManager.php';
require_once 'Logger.php';

class RobotManager
{
    private ?RobotKit $selectedRobot = null;
    private ?CommandManager $commandManager = null;
    private string $robotUnitsPath = '../../Jsons/RobotUnits.json';
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function changePath(string $newPath): void
    {
        $this->robotUnitsPath = $newPath;
    }

    public function getRobotUnits(): array
    {
        return file_exists($this->robotUnitsPath) ? json_decode(file_get_contents($this->robotUnitsPath), true) : [];
    }

    public function createRobot(string $name, string $ip, string $port, string $model = "UNKNOWN"): void
    {
        if (empty($this->robotUnitsPath)) {
            throw new Exception("NO PATH CONNECTED!");
        }

        $robots = $this->getRobotUnits();

        foreach ($robots as $robot) {
            if ($robot['Name'] === $name) {
                return;
            }
        }

        $robots[] = [
            "IP" => $ip,
            "Port" => $port,
            "Name" => $name,
            "MODEL" => $model,
            "LastStatus" => date('Y-m-d H:i:s'),
            "IsOnline" => false
        ];

        file_put_contents($this->robotUnitsPath, json_encode($robots, JSON_PRETTY_PRINT));
    }

    public function getSelectedRobot(): RobotKit
    {
        if ($this->selectedRobot === null) {
            throw new Exception("Brak wybranego robota. Użyj najpierw selectRobotByName().");
        }
        return $this->selectedRobot;
    }

    public function selectRobotByName(string $name): void
    {
        foreach ($this->getRobotUnits() as $unit) {
            if ($unit['Name'] === $name) {
                $this->selectedRobot = new RobotKit($unit['IP'], $unit['Port'], $unit['Name'], $unit['MODEL']);
                $this->commandManager = new CommandManager($unit['IP'], $unit['Port'], Logger::getInstance());
                return;
            }
        }
        $this->selectedRobot = null;
        $this->commandManager = null;
    }

    public function sendCommandToSelectedRobot(string $command): void
    {
        if ($this->commandManager === null) {
            throw new Exception("Brak wybranego robota lub CommandManagera.");
        }
        $this->commandManager->sendCommand($command);
    }
    public function forcePingToAllRobots(): void
    {
        $Robots = $this->getRobotUnits();
        $logger = logger::getInstance();
        $serverIp = getHostByName(getHostName());
        $serverPort = $_SERVER['SERVER_PORT'];
        $pingCommand = 'I ' . $serverIp . ' ' . $serverPort;

        foreach ($Robots as $units) {
            $this->commandManager = new CommandManager($units['IP'], (int)$units['Port'], $logger);
            $logger->log("Wysłano ping do robota: " . $units['Name'] . " (" . $units['IP'] . ":" . $units['Port'] . ")");
            $this->commandManager->sendCommand($pingCommand . "X");
        }
    }
}