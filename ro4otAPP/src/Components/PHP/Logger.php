<?php

class logger
{
    private $path = 'Jsons/LogHistory.json';


    //SINGLETON :>
    private function __construct() {}
    private static $instance = null;
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new logger();
        }
        return self::$instance;
    }

    public function changePath($newPath) {
        $this->path = $newPath;
    }

    public function log($event)
    {
        $currentData = file_exists($this->path) ? json_decode(file_get_contents($this->path), true) : [];
        $currentData[] = [
            'data' => date('Y-m-d H:i:s'),
            'zdarzenie' => $event
            //'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ];
        file_put_contents($this->path, json_encode($currentData, JSON_PRETTY_PRINT));
    }
    public static function clearLogByPath($Path)
    {
        file_put_contents($Path, json_encode([]));
    }

    public function clearLog()
    {
        file_put_contents($this->path, json_encode([]));
    }

    public function GiveEveryLogs()
    {
        if (file_exists($this->path)) {
            return json_decode(file_get_contents($this->path), true);
        }
        return [];
    }
    
    public function getRawLogs()
    {
        return file_exists($this->path) ? file_get_contents($this->path) : '';
    }
}
