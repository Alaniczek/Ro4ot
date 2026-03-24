<?php
require_once 'RobotKit.php';
require_once 'CommandManager.php';
require_once 'Logger.php';

class RobotManager {
    private ?RobotKit $SelectedRobot;
    private string $RobotUnitsPath = '../../Jsons/RobotUnits.json';

    private function __construct() {}
    private static $instance = null;
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new RobotManager();
        }
        return self::$instance;
    }

    //PATH 
    public function changePath($newPath) : void 
    {
        $this->RobotUnitsPath = $newPath;
    }

    //JSON MANAGEMENT VIEW    
    public function GetRobotUnits() : array
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

    //SELECTOR
    public function getSelectedRobot(): RobotKit
    {
        if ($this->SelectedRobot === null) {
            throw new Exception("Brak wybranego robota. Użyj najpierw SelectRobotByName().");
        }
        return $this->SelectedRobot;
    }

    public function SelectRobotByName(string $name): void
    {
        $robotUnits = $this->GetRobotUnits();
        
        foreach ($robotUnits as $unit) {
            if ($unit['Name'] === $name) {
                $this->SelectedRobot = new RobotKit($unit['IP'], $unit['Port']);
                return;
            }
        }
        
        $this->SelectedRobot = null;
    }


    //COMMAND SENDER
    public function SendCommandToSelectedRobot($command) : void 
    {
        $robot = $this->getSelectedRobot();
    
        $CommandManager = new CommandManager($robot->Ip, $robot->Port, Logger::getInstance());
        $CommandManager->sendCommand($command);
    }


}

