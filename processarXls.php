<?php
// processarXls.php
include 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEstrutura = $_POST['idEstrutura'];
    $arquivo = $_FILES['file']['tmp_name'];

    $componentesEncontrados = [];
    $componentesNaoEncontrados = [];

    try {
        $spreadsheet = IOFactory::load($arquivo);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $index => $row) {
            if ($index === 0 || empty($row[2]) || empty($row[3])) continue; // Ignorar cabeçalho e linhas incompletas

            $codigo = trim($row[2]); // Coluna "CODIGO"
            $quantidade = (int) $row[3]; // Coluna "QTD."

            $stmt = $conn->prepare("SELECT descricao FROM componentes WHERE codigo = :codigo");
            $stmt->bindParam(":codigo", $codigo);
            $stmt->execute();
            $componente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($componente) {
                $componentesEncontrados[] = [
                    'codigo' => $codigo,
                    'descricao' => $componente['descricao'],
                    'quantidade' => $quantidade
                ];
            } else {
                $componentesNaoEncontrados[] = [
                    'codigo' => $codigo,
                    'quantidade' => $quantidade
                ];
            }
        }

        echo json_encode([
            "status" => "success",
            "componentesEncontrados" => $componentesEncontrados,
            "componentesNaoEncontrados" => $componentesNaoEncontrados
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Erro ao processar o arquivo: " . $e->getMessage()
        ]);
    }
}
?>
