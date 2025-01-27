<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idEstrutura = $_POST['idEstrutura'];
    $codigos = $_POST['codigo'];
    $produtos = $_POST['produto'];
    $quantidades = $_POST['quantidade'];

    try {
        foreach ($codigos as $index => $codigo) {
            $produto = $produtos[$index];
            $quantidade = (int)$quantidades[$index];

            // Verificar se o componente já existe para a estrutura
            $stmt = $conn->prepare("SELECT qntdUtilizada FROM componenteEstrutura WHERE idEstrutura = :idEstrutura AND codigo = :codigo");
            $stmt->bindParam(':idEstrutura', $idEstrutura);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->execute();
            $existingComponent = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingComponent) {
                // Componente já existe, perguntar se deseja somar a quantidade
                $existingQuantity = $existingComponent['qntdUtilizada'];
                $newQuantity = $existingQuantity + $quantidade;

                echo "<script>
                    if (confirm('O código $codigo já está cadastrado com quantidade de $existingQuantity. Deseja somar $quantidade para um total de $newQuantity?')) {
                        window.location.href = 'somarQuantidade.php?idEstrutura=$idEstrutura&codigo=$codigo&quantidade=$quantidade';
                    } else {
                        window.location.href = 'criarEstrutura.php?error=1';
                    }
                </script>";
                exit;
            } else {
                // Inserir novo componente
                $stmt = $conn->prepare("INSERT INTO componenteEstrutura (idEstrutura, codigo, descricaoComponente, qntdUtilizada) VALUES (:idEstrutura, :codigo, :produto, :quantidade)");
                $stmt->bindParam(':idEstrutura', $idEstrutura);
                $stmt->bindParam(':codigo', $codigo);
                $stmt->bindParam(':produto', $produto);
                $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // Redirecionar com sucesso
        header("Location: criarEstrutura.php?success=1");
    } catch (PDOException $e) {
        // Redirecionar com erro
        header("Location: criarEstrutura.php?error=1");
    }
}
exit;
