<?php
require_once 'Product.php';

class Rod extends Product {
    private $length;

    public function __construct($name, $price, $description, $category, $length) {
        parent::__construct($name, $price, $description, $category);
        $this->length = $length;
    }

    public function getLength() {
        return $this->length;
    }

    public function saveToDatabase(PDO $pdo) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category, length) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$this->name, $this->price, $this->description, $this->category, $this->length]);
    }
}
