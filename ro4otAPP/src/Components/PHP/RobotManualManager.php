<?php
require_once 'RobotManualFinder.php';
require_once 'CommandManager.php';
require_once 'Logger.php';

class RobotManualManager {
    private ?RobotManualFinder $SelectedRobot;
    private string $RobotUnitsPath = '../../Jsons/RobotUnits.json';

    private function __construct() {}
    private static $instance = null;
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new RobotManualManager();
        }
        return self::$instance;
    }

    public function changePath($newPath)
    {
        $this->RobotUnitsPath = $newPath;
    }

    public function GetRobotUnits()
    {
        $robotUnits = file_exists($this->RobotUnitsPath) ? json_decode(file_get_contents($this->RobotUnitsPath), true) : [];
        return $robotUnits;
    }

    private function CheckIfRobotExists($name) : bool
    {
        $robotUnits = $this->GetRobotUnits();
        foreach($robotUnits as $unit)
        {
            if($unit['Name'] === $name)
            {
                return true;
            }
        }
        return false;
    }

    public function SelectRobot($name)
    {
        if($this->CheckIfRobotExists($name))
        {
            $this->SelectedRobot = new RobotManualFinder($unit['IP'], $unit['Port']);
        }
    }

    public function SendCommandToSelectedRobot($command)
    {
        if(isset($this->SelectedRobot))
        {
            $CommandManager = new CommandManager($this->SelectedRobot->Ip, $this->SelectedRobot->Port, Logger::getInstance());
            $CommandManager->sendCommand($command);
        }
    }


}

