<?php
include 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivoPlanilha']) && isset($_POST['idEstrutura'])) {
    $idEstrutura = $_POST['idEstrutura'];
    $filePath = $_FILES['arquivoPlanilha']['tmp_name'];

    if (!file_exists($filePath) || empty($filePath)) {
        echo '<div class="alert alert-danger">Erro: Arquivo não encontrado ou caminho inválido.</div>';
        exit;
    }

    $planilha = IOFactory::load($filePath);
    $sheet = $planilha->getActiveSheet();
    $dados = $sheet->toArray();

    $codigos = [];
    foreach ($dados as $index => $linha) {
        if ($index === 0) continue; // Pular cabeçalho
        $codigo = isset($linha[3]) ? trim($linha[3]) : null; 
        $quantidade = isset($linha[2]) ? (int)$linha[2] : 0;

        if (!empty($codigo)) { 
            $codigos[$codigo] = $quantidade;
        }
    }

    $cadastrados = [];
    $naoCadastrados = [];
    foreach ($codigos as $codigo => $quantidade) {
        $stmt = $conn->prepare("SELECT codigo FROM componentes WHERE codigo = :codigo");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $cadastrados[$codigo] = $quantidade;
        } else {
            $naoCadastrados[$codigo] = $quantidade;
        }
    }

    if (count($naoCadastrados) > 0) {
        echo "<h4>Os seguintes códigos não foram encontrados:</h4>";
        echo "<ul>";
        foreach ($naoCadastrados as $codigo => $quantidade) {
            echo "<li>Código: $codigo, Quantidade: $quantidade</li>";
        }
        echo "</ul>";
        echo "<button class='btn btn-primary'>Gravar sem códigos não encontrados</button>";
    } else {
        echo "<h4>Todos os códigos foram encontrados. Deseja gravar a estrutura?</h4>";
        echo "<button class='btn btn-primary'>Confirmar Gravação</button>";
    }
}
