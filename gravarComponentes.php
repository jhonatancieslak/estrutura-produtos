<?php
// gravarComponentes.php
include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$idEstrutura = $data['estruturaId'];
$componentes = $data['componentes'];
$sucesso = true;

try {
    foreach ($componentes as $componente) {
        $codigo = $componente['codigo'];
        $descricao = $componente['descricao'];
        $quantidade = $componente['quantidade'];

        // Verificar se o componente já existe na estrutura
        $stmt = $conn->prepare("SELECT qntdUtilizada FROM componenteEstrutura WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
        $stmt->bindParam(':idEstrutura', $idEstrutura);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        $existingComponent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingComponent) {
            // Componente já existe: somar a quantidade
            $novaQuantidade = $existingComponent['qntdUtilizada'] + $quantidade;
            $updateStmt = $conn->prepare("UPDATE componenteEstrutura SET qntdUtilizada = :novaQuantidade WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
            $updateStmt->bindParam(':novaQuantidade', $novaQuantidade);
            $updateStmt->bindParam(':idEstrutura', $idEstrutura);
            $updateStmt->bindParam(':codigo', $codigo);
            $sucesso = $updateStmt->execute() && $sucesso;
        } else {
            // Inserir novo componente
            $insertStmt = $conn->prepare("INSERT INTO componenteEstrutura (idEstrutura, codigo, descricaoComponente, qntdUtilizada) VALUES (:idEstrutura, :codigo, :descricao, :quantidade)");
            $insertStmt->bindParam(':idEstrutura', $idEstrutura);
            $insertStmt->bindParam(':codigo', $codigo);
            $insertStmt->bindParam(':descricao', $descricao);
            $insertStmt->bindParam(':quantidade', $quantidade);
            $sucesso = $insertStmt->execute() && $sucesso;
        }
    }

    echo json_encode([
        "status" => $sucesso ? "success" : "error",
        "message" => $sucesso ? "Componentes gravados com sucesso." : "Erro ao gravar alguns componentes."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro ao processar componentes: " . $e->getMessage()
    ]);
}
?>
