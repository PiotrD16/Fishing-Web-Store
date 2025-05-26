<?php
require_once 'Product.php';

class Bait extends Product {
    private $weight;

    public function __construct($name, $price, $description, $category, $weight) {
        parent::__construct($name, $price, $description, $category);
        $this->weight = $weight;
    }

    public function getWeight() {
        return $this->weight;
    }

    public function saveToDatabase(PDO $pdo) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category, weight) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$this->name, $this->price, $this->description, $this->category, $this->weight]);
    }
}
