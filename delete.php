<?php
require 'database.php';

$id = $_POST['id'] ?? 0;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM docs WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['status' => 'deleted']);
} else {
    echo json_encode(['status' => 'invalid']);
}
?>
