<?php
require 'database.php';

$id = $_POST['id'] ?? 0;
$content = $_POST['content'] ?? '';

if ($id && $content !== '') {
    $stmt = $pdo->prepare("UPDATE docs SET content = ? WHERE id = ?");
    $success = $stmt->execute([$content, $id]);

    echo json_encode(['status' => $success ? 'ok' : 'error']);
} else {
    echo json_encode(['status' => 'invalid']);
}
?>
