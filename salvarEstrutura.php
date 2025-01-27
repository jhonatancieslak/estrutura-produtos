<?php
// Incluir a conexão com o banco de dados
include 'db.php';

// Verificar se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeEstrutura = $_POST['nomeEstrutura'];

    try {
        // Verificar se já existe uma estrutura com o mesmo nome
        $stmt = $conn->prepare("SELECT COUNT(*) FROM estrutura WHERE nomeEstrutura = :nomeEstrutura");
        $stmt->bindParam(':nomeEstrutura', $nomeEstrutura);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists) {
            // Nome duplicado, redirecionar com erro
            header("Location: novoNomeEstrutura.php?error=duplicado");
        } else {
            // Inserir nova estrutura
            $stmt = $conn->prepare("INSERT INTO estrutura (nomeEstrutura) VALUES (:nomeEstrutura)");
            $stmt->bindParam(':nomeEstrutura', $nomeEstrutura);
            $stmt->execute();

            // Redirecionar com sucesso
            header("Location: index.php?success=1");
        }
    } catch(PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>
