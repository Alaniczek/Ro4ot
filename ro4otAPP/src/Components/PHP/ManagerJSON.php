<?php
class ManagerJSON {
    private string $path;
    
    public function __construct(string $path) {
        $this->path = $path;
        if (file_exists($this->path)) {
            $data = json_decode(file_get_contents($this->path), true);
        }
    }

    public function read() {
        if (file_exists($this->path)) {
            return json_decode(file_get_contents($this->path), true);
        }
        return null;
    }

    public function changePath(string $newPath) {
        $this->path = $newPath;
    }
    
    public function renameKey(string $oldKey, string $newKey) {
    $data = $this->read();
    if ($data !== null && isset($data[$oldKey])) {
        $data[$newKey] = $data[$oldKey];
        unset($data[$oldKey]);
        file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
    }
}

    public function changeValueKEY(string $key, $value) {
        $data = $this->read();
        if ($data !== null) {
            $data[$key] = $value;
            file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    public function deleteKEY(string $key) {
        $data = $this->read();
        if ($data !== null && array_key_exists($key, $data)) {
            unset($data[$key]);
            file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    public function GiveEveryKey() {
        $data = $this->read();
        if ($data !== null) {
            return array_keys($data);
        }
        return null;
    }
}
