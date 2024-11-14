<?php 
require_once 'includes/db.inc.php';
try {
   

    $stmt = $pdo->prepare("DELETE FROM products"); 
    $stmt->execute();

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>