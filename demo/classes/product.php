<?php
abstract class Product {
    protected $name;
    protected $price;
    protected $description;
    protected $category;

    public function __construct($name, $price, $description, $category) {
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->category = $category;
    }

    public function getName() {
        return $this->name;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getCategory() {
        return $this->category;
    }

    public abstract function saveToDatabase(PDO $pdo);
}
