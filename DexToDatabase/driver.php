<?php
require 'DexToDatabase/InsertProduct.php';
require 'DexToDatabase/InsertDecoration.php';
require 'DexToDatabase/InsertInventory.php';
require 'DexToDatabase/InsertAddOn.php';

$data = file_get_contents('input.json');
$jsonDataArray = json_decode($data, true);

if (is_array($jsonDataArray)) {
    foreach ($jsonDataArray as $jsonData) {
        insertProduct($jsonData);
        insertInventory($jsonData);
        insertDecoration($jsonData);
        insertAddon($jsonData);
    }
}

?>