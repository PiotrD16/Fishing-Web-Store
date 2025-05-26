<?php
require_once 'Product.php';

class Reel extends Product {
    private $gearRatio;

    public function __construct($name, $price, $description, $category, $gearRatio) {
        parent::__construct($name, $price, $description, $category);
        $this->gearRatio = $gearRatio;
    }

    public function getGearRatio() {
        return $this->gearRatio;
    }

    public function saveToDatabase(PDO $pdo) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category, gear_ratio) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$this->name, $this->price, $this->description, $this->category, $this->gearRatio]);
    }
}
