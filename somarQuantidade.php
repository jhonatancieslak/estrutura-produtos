<?php
include 'db.php';

$idEstrutura = $_GET['idEstrutura'];
$codigo = $_GET['codigo'];
$quantidade = (int)$_GET['quantidade'];

try {
    // Atualizar a quantidade
    $stmt = $conn->prepare("UPDATE componenteEstrutura SET qntdUtilizada = qntdUtilizada + :quantidade WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
    $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':idEstrutura', $idEstrutura);
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();

    header("Location: criarEstrutura.php?success=1");
} catch (PDOException $e) {
    header("Location: criarEstrutura.php?error=1");
}
exit;
