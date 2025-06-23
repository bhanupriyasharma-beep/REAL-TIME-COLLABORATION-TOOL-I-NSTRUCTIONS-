<?php
require 'database.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $stmt = $pdo->query("SELECT id, name FROM docs ORDER BY updated_at DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'create':
        $name = $_POST['name'] ?? '';
        $content = $_POST['content'] ?? '';

        if ($name !== '') {
            $stmt = $pdo->prepare("INSERT INTO docs (name, content) VALUES (?, ?)");
            if ($stmt->execute([$name, $content])) {
                echo json_encode(['id' => $pdo->lastInsertId()]);
            } else {
                echo json_encode(['error' => 'Insert failed']);
            }
        } else {
            echo json_encode(['error' => 'Missing name']);
        }
        break;

    case 'load':
        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM docs WHERE id = ?");
            $stmt->execute([$id]);
            $doc = $stmt->fetch();
            echo json_encode($doc ?: []);
        } else {
            echo json_encode(['error' => 'Invalid ID']);
        }
        break;

    case 'save':
        $id = (int) ($_POST['id'] ?? 0);
        $content = $_POST['content'] ?? '';
        if ($id && $content !== '') {
            $stmt = $pdo->prepare("UPDATE docs SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $success = $stmt->execute([$content, $id]);
            echo json_encode(['status' => $success ? 'ok' : 'error']);
        } else {
            echo json_encode(['status' => 'invalid']);
        }
        break;

    case 'delete':
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM docs WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'invalid']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
