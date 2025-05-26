<?php
require_once 'Product.php';

class Line extends Product {
    private $length;
    private $strength;

    public function __construct($name, $price, $description, $category, $length, $strength) {
        parent::__construct($name, $price, $description, $category);
        $this->length = $length;
        $this->strength = $strength;
    }

    public function getLength() {
        return $this->length;
    }

    public function getStrength() {
        return $this->strength;
    }

    public function saveToDatabase(PDO $pdo) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category, length, strength) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$this->name, $this->price, $this->description, $this->category, $this->length, $this->strength]);
    }
}
