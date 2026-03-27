<?php
class QueueManager {
    private $path;

    public function __construct($path) {
        $this->path = $path;
    }

    public function getItems() {
        if (!file_exists($this->path)) return [];
        return json_decode(file_get_contents($this->path), true) ?: [];
    }

    public function save($data) {
        file_put_contents($this->path, json_encode(array_values($data), JSON_PRETTY_PRINT));
    }

    public function add($item) {
        $items = $this->getItems();
        $items[] = $item;
        $this->save($items);
    }

    public function clear() {
        $this->save([]);
    }

    public function pop() {
        $items = $this->getItems();
        $item = array_shift($items);
        $this->save($items);
        return $item;
    }
}