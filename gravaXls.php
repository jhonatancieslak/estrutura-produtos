<?php
// gravaXls.php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEstrutura = $_POST['idEstrutura'];
    $componentes = json_decode($_POST['componentes'], true);

    if (!$idEstrutura || !$componentes) {
        echo json_encode([
            "status" => "error",
            "message" => "Estrutura ou componentes inválidos."
        ]);
        exit;
    }

    $sucesso = true;

    try {
        foreach ($componentes as $componente) {
            $codigo = $componente['codigo'];
            $quantidade = $componente['quantidade'];

            // Buscar a descrição do componente na tabela componentes
            $descStmt = $conn->prepare("SELECT descricao FROM componentes WHERE codigo = :codigo");
            $descStmt->bindParam(":codigo", $codigo);
            $descStmt->execute();
            $descricaoComponente = $descStmt->fetchColumn();

            // Verificar se o componente já existe na estrutura
            $checkStmt = $conn->prepare("SELECT qntdUtilizada FROM componenteEstrutura WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
            $checkStmt->bindParam(":idEstrutura", $idEstrutura);
            $checkStmt->bindParam(":codigo", $codigo);
            $checkStmt->execute();
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Somar a quantidade existente e atualizar a descrição
                $novaQuantidade = $existing['qntdUtilizada'] + $quantidade;
                $updateStmt = $conn->prepare("UPDATE componenteEstrutura 
                                              SET qntdUtilizada = :novaQuantidade, descricaoComponente = :descricao 
                                              WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
                $updateStmt->bindParam(":novaQuantidade", $novaQuantidade);
                $updateStmt->bindParam(":descricao", $descricaoComponente);
                $updateStmt->bindParam(":idEstrutura", $idEstrutura);
                $updateStmt->bindParam(":codigo", $codigo);

                if (!$updateStmt->execute()) {
                    $sucesso = false;
                }
            } else {
                // Inserir novo componente com descrição
                $insertStmt = $conn->prepare("INSERT INTO componenteEstrutura (idEstrutura, codigo, descricaoComponente, qntdUtilizada) 
                                              VALUES (:idEstrutura, :codigo, :descricao, :quantidade)");
                $insertStmt->bindParam(":idEstrutura", $idEstrutura);
                $insertStmt->bindParam(":codigo", $codigo);
                $insertStmt->bindParam(":descricao", $descricaoComponente);
                $insertStmt->bindParam(":quantidade", $quantidade);

                if (!$insertStmt->execute()) {
                    $sucesso = false;
                }
            }
        }

        if ($sucesso) {
            echo json_encode([
                "status" => "success",
                "message" => "Componentes gravados com sucesso."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Erro ao gravar alguns componentes."
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Erro ao processar a gravação: " . $e->getMessage()
        ]);
    }
}
?>
