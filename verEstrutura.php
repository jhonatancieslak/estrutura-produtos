<?php
// Incluindo a conexão com o banco de dados e a biblioteca de código de barras
include 'db.php';
require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Função para gerar código de barras em PNG
function gerarCodigoBarras($codigo) {
    if (empty($codigo)) return '';
    $generator = new BarcodeGeneratorPNG();
    return '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($codigo, $generator::TYPE_CODE_128)) . '" style="height: 20px;">';
}

// Obtendo o ID da estrutura
$idEstrutura = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Consultando detalhes da estrutura
$stmt = $conn->prepare("SELECT * FROM estrutura WHERE id = :id");
$stmt->bindParam(':id', $idEstrutura, PDO::PARAM_INT);
$stmt->execute();
$estrutura = $stmt->fetch(PDO::FETCH_ASSOC);

// Consultando componentes da estrutura
$componentesStmt = $conn->prepare("SELECT codigo, descricaoComponente AS descricao, SUM(qntdUtilizada) AS quantidade
                                   FROM componenteEstrutura
                                   WHERE idEstrutura = :idEstrutura
                                   GROUP BY codigo, descricao
                                   ORDER BY codigo");
$componentesStmt->bindParam(':idEstrutura', $idEstrutura, PDO::PARAM_INT);
$componentesStmt->execute();
$componentes = $componentesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Estrutura - <?= htmlspecialchars($estrutura['nomeEstrutura'] ?? 'Estrutura Não Encontrada') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos de formatação para impressão */
        @media print {
            .print-button { display: none; }
        }
        .container { max-width: 700px; font-size: 11px; }
        h2 { font-size: 18px; }
        p, th, td { font-size: 9px; }
        .description { font-size: 9px; line-height: 1.1; }
        table.table { font-size: 9px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Cabeçalho da Estrutura -->
        <div class="text-center mb-3">
            <img src="logocliente.jpg" alt="Logo" style="width: 150px;">
            <h2>ESTRUTURA - <?= htmlspecialchars($estrutura['nomeEstrutura'] ?? 'Estrutura Não Encontrada') ?></h2>
            <p>Criado em: <?= htmlspecialchars($estrutura['dataCriacao'] ?? 'Não Informado') ?> / 
               Alterado: <?= (!empty($estrutura['dataAlteracao']) && $estrutura['dataAlteracao'] != $estrutura['dataCriacao']) ? htmlspecialchars($estrutura['dataAlteracao']) : "Sem alteração" ?>
            </p>
        </div>
        <hr>

        <!-- Tabela de Componentes -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>Descrição</th>
                    <th>QNTD</th>
                    <th>Cód.</th>
                    <th>Barras</th>
                    <th>Confere</th>
                </tr>
            </thead>
            <tbody>
                <?php $item = 1; ?>
                <?php foreach ($componentes as $componente): ?>
                    <tr>
                        <td><?= $item++ ?></td>
                        <td class="description"><?= htmlspecialchars($componente['descricao'] ?? 'Sem Descrição') ?></td>
                        <td><?= htmlspecialchars($componente['quantidade'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($componente['codigo'] ?? 'Sem Código') ?></td>
                        <td><?= gerarCodigoBarras($componente['codigo'] ?? '') ?></td>
                        <td><input type="checkbox"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Rodapé -->
        <footer class="text-center mt-4">
            <p>Desenvolvimento: Jhonatan R. Cieslak</p>
        </footer>

        <!-- Botão de Impressão -->
        <div class="text-center mt-3">
            <button onclick="window.print()" class="btn btn-primary print-button">Imprimir</button>
        </div>
    </div>
</body>
</html>
