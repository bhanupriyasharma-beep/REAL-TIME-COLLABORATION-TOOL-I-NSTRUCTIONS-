<?php
require 'database.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM docs WHERE id = ?");
    $stmt->execute([$id]);
    $doc = $stmt->fetch();

    echo json_encode($doc ?: []);
} else {
    echo json_encode(['error' => 'Invalid ID']);
}
?>
