<?php
class RobotKit
{
    public string $Ip;
    public string $Port;
    public string $Name;
    public string $Model;

    public function __construct($ip, $port, $name, $model)
    {
        $this->Ip = $ip;
        $this->Port = $port;
        $this->Name = $name;
        $this->Model = $model;
    }
}