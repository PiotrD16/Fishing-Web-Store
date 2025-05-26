<?php
require_once 'Rod.php';
require_once 'Reel.php';
require_once 'Bait.php';
require_once 'Line.php';
require_once 'Net.php';

abstract class ProductFactory {
    public abstract function createProduct($name, $price, $description, $category, $extraAttributes);
}

class RodFactory extends ProductFactory {
    public function createProduct($name, $price, $description, $category, $extraAttributes) {
        return new Rod($name, $price, $description, $category, $extraAttributes['length']);
    }
}

class ReelFactory extends ProductFactory {
    public function createProduct($name, $price, $description, $category, $extraAttributes) {
        return new Reel($name, $price, $description, $category, $extraAttributes['gear_ratio']);
    }
}

class BaitFactory extends ProductFactory {
    public function createProduct($name, $price, $description, $category, $extraAttributes) {
        return new Bait($name, $price, $description, $category, $extraAttributes['weight']);
    }
}

class LineFactory extends ProductFactory {
    public function createProduct($name, $price, $description, $category, $extraAttributes) {
        return new Line($name, $price, $description, $category, $extraAttributes['length'], $extraAttributes['strength']);
    }
}

class NetFactory extends ProductFactory {
    public function createProduct($name, $price, $description, $category, $extraAttributes) {
        return new Net($name, $price, $description, $category, $extraAttributes['material']);
    }
}
?>
