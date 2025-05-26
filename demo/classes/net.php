<?php
require_once 'Product.php';

class Net extends Product {
    private $material;

    public function __construct($name, $price, $description, $category, $material) {
        parent::__construct($name, $price, $description, $category);
        $this->material = $material;
    }

    public function getMaterial() {
        return $this->material;
    }

    public function saveToDatabase(PDO $pdo) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category, material) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$this->name, $this->price, $this->description, $this->category, $this->material]);
    }
}
