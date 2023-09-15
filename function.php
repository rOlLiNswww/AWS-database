<?php
function deleteProductByCode($product_code) {
    global $pdo;

    try {
        $query = "DELETE FROM Products WHERE Product_Code = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$product_code]);
    } catch (PDOException $e) {
        die("Error deleting product: " . $e->getMessage());
    }
}


function productCodeExists($product_code) {
    global $pdo; 

    try {
        $query = "SELECT Last_Modified FROM Products WHERE Product_Code = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$product_code]);
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC)['Last_Modified'];
        } else {
            return false; // Product_Code does not exist
        }
    } catch (PDOException $e) {
        die("Error executing query: " . $e->getMessage());
    }
}

function getAllProductCodesFromDatabase($supplierName) {
    global $pdo;

    $codes = [];
    try {
        $query = "SELECT Product_Code FROM Products WHERE Supplier_Name = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$supplierName]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codes[] = $row['Product_Code'];
        }
    } catch (PDOException $e) {
        die("Error fetching product codes: " . $e->getMessage());
    }
    
    return $codes;
}



?>