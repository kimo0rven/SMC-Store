<?php
require './db_connection.php';
$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

if ($categoryId > 0) {
    $stmt = $pdo->prepare("SELECT subcategory_id, subcategory_name FROM subcategories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}
