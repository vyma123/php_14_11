<?php
require_once 'includes/db.inc.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $query = "DELETE FROM products WHERE id = :id";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
