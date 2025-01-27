<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

// Verificar se o formulário foi submetido corretamente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['nomeEstrutura'])) {
    $id = (int)$_POST['id'];
    $nomeEstrutura = $_POST['nomeEstrutura'];

    try {
        // Verificar se o nome já existe em outra estrutura
        $stmt = $conn->prepare("SELECT COUNT(*) FROM estrutura WHERE nomeEstrutura = :nomeEstrutura AND id != :id");
        $stmt->bindParam(':nomeEstrutura', $nomeEstrutura);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists) {
            // Nome duplicado, redirecionar com erro
            header("Location: editarEstrutura.php?id=$id&error=duplicado");
        } else {
            // Atualizar a estrutura
            $stmt = $conn->prepare("UPDATE estrutura SET nomeEstrutura = :nomeEstrutura, dataAlteracao = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindParam(':nomeEstrutura', $nomeEstrutura);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Redirecionar com sucesso
            header("Location: nomeEstrutura.php?success=edited");
        }
    } catch(PDOException $e) {
        // Erro no banco de dados, redirecionar com erro
        header("Location: editarEstrutura.php?id=$id&error=database");
    }
} else {
    // Dados incompletos, redirecionar com erro
    header("Location: editarEstrutura.php?id=$id&error=invalid");
}
exit;
