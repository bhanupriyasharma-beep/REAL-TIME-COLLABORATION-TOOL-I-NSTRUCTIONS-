<?php
require 'database.php';

$name = $_POST['name'] ?? 'Untitled';
$content = $_POST['content'] ?? '';

$stmt = $pdo->prepare("INSERT INTO docs (name, content) VALUES (?, ?)");
$success = $stmt->execute([$name, $content]);

if ($success) {
    echo json_encode(['id' => $pdo->lastInsertId()]);
} else {
    echo json_encode(['error' => 'Could not create file']);
}
?>
