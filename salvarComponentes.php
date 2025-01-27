<?php
include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$codigos = $data['codigos'] ?? [];

$sucesso = true;

foreach ($codigos as $item) {
    $stmt = $conn->prepare("INSERT INTO componenteEstrutura (codigo, quantidade) VALUES (:codigo, :quantidade) ON DUPLICATE KEY UPDATE quantidade = quantidade + :quantidade");
    $stmt->bindParam(':codigo', $item['codigo']);
    $stmt->bindParam(':quantidade', $item['quantidade']);
    
    if (!$stmt->execute()) {
        $sucesso = false;
        break;
    }
}

echo json_encode(['success' => $sucesso]);
?>
