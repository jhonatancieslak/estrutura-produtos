<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

// Obtendo o ID da estrutura para excluir
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Iniciar transação para garantir que ambas as exclusões ocorram
        $conn->beginTransaction();

        // Excluir os componentes associados à estrutura
        $stmt = $conn->prepare("DELETE FROM componenteEstrutura WHERE idEstrutura = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Excluir a estrutura
        $stmt = $conn->prepare("DELETE FROM estrutura WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Confirmar transação
        $conn->commit();
        
        // Redirecionar com sucesso
        header("Location: index.php?success=deleted");
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conn->rollBack();
        header("Location: index.php?error=deletefail");
    }
} else {
    header("Location: index.php?error=notfound");
}
exit;
