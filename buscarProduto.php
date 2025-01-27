<?php
include 'db.php';

if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];
    $stmt = $conn->prepare("SELECT descricao FROM componentes WHERE codigo = :codigo");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        echo json_encode(['produto' => $produto['descricao']]);
    } else {
        echo json_encode(['produto' => null]);
    }
} else {
    echo json_encode(['error' => 'Código não fornecido']);
}
?>
