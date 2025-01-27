<?php
include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$codigos = $data['codigos'] ?? [];

$naoEncontrados = [];
foreach ($codigos as $item) {
    $stmt = $conn->prepare("SELECT codigo FROM componentes WHERE codigo = :codigo");
    $stmt->bindParam(':codigo', $item['codigo']);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $naoEncontrados[] = $item;
    }
}

echo json_encode(['naoEncontrados' => $naoEncontrados]);
?>
