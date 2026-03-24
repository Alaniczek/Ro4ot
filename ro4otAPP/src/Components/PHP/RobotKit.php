<?php
class RobotKit 
{
    public string $Ip;
    public string $Port;
    public string $Name;
    public string $Description;

    public function __construct($ip, $port, $name, $description) 
    {
        $this->Ip = $ip;
        $this->Port = $port;
        $this->Name = $name;
        $this->Description = $description;
    }
}