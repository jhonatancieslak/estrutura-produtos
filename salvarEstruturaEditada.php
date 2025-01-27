<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

$idEstrutura = $_POST['idEstrutura'];
$sucesso = true;

try {
    // Exclusão dos componentes marcados para exclusão
    if (isset($_POST['excluir'])) {
        foreach ($_POST['excluir'] as $codigoParaExcluir) {
            $stmt = $conn->prepare("DELETE FROM componenteEstrutura WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
            $stmt->bindParam(':idEstrutura', $idEstrutura);
            $stmt->bindParam(':codigo', $codigoParaExcluir);
            if (!$stmt->execute()) $sucesso = false;
        }
    }

    // Atualizar componentes existentes
    if (isset($_POST['codigoExistente'])) {
        foreach ($_POST['codigoExistente'] as $index => $codigo) {
            $quantidade = $_POST['quantidadeExistente'][$index];
            $descricao = $_POST['descricaoExistente'][$index] ?? '';

            $stmt = $conn->prepare("UPDATE componenteEstrutura SET qntdUtilizada = :quantidade, descricaoComponente = :descricao WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
            $stmt->bindParam(':quantidade', $quantidade);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':idEstrutura', $idEstrutura);
            $stmt->bindParam(':codigo', $codigo);

            if (!$stmt->execute()) $sucesso = false;
        }
    }

    // Inserir ou somar novos componentes
    if (isset($_POST['codigoNovo'])) {
        foreach ($_POST['codigoNovo'] as $index => $codigo) {
            $quantidade = $_POST['quantidadeNovo'][$index];
            $descricao = $_POST['descricaoNovo'][$index] ?? '';

            // Verificar se o componente já existe
            $checkStmt = $conn->prepare("SELECT qntdUtilizada FROM componenteEstrutura WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
            $checkStmt->bindParam(':idEstrutura', $idEstrutura);
            $checkStmt->bindParam(':codigo', $codigo);
            $checkStmt->execute();
            $existingComponent = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingComponent) {
                // Componente já existe: somar a quantidade
                $novaQuantidade = $existingComponent['qntdUtilizada'] + $quantidade;
                $stmt = $conn->prepare("UPDATE componenteEstrutura SET qntdUtilizada = :novaQuantidade WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
                $stmt->bindParam(':novaQuantidade', $novaQuantidade);
                $stmt->bindParam(':idEstrutura', $idEstrutura);
                $stmt->bindParam(':codigo', $codigo);
            } else {
                // Inserir novo componente com descrição
                $stmt = $conn->prepare("INSERT INTO componenteEstrutura (idEstrutura, codigo, qntdUtilizada, descricaoComponente) VALUES (:idEstrutura, :codigo, :quantidade, :descricao)");
                $stmt->bindParam(':idEstrutura', $idEstrutura);
                $stmt->bindParam(':codigo', $codigo);
                $stmt->bindParam(':quantidade', $quantidade);
                $stmt->bindParam(':descricao', $descricao);
            }

            if (!$stmt->execute()) $sucesso = false;
        }
    }

    header("Location: editarEstrutura.php?id=$idEstrutura&success=" . ($sucesso ? "1" : "0"));
} catch (Exception $e) {
    header("Location: editarEstrutura.php?id=$idEstrutura&error=1");
}
?>
