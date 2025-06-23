<?php
include "db.php";
$data = json_decode(file_get_contents("php://input"), true);
$name = $data["name"];

$stmt = $pdo->prepare("INSERT INTO files (name, content) VALUES (?, '')");
$stmt->execute([$name]);
$id = $pdo->lastInsertId();

echo json_encode(["id" => $id, "content" => ""]);
?>
