<?php
// Incluindo a conexão com o banco de dados e carregando o autoload do Composer
include 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$message = '';
$totalItems = 0;
$addedCount = 0;
$updatedCount = 0;

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    
    // Carregar a planilha
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $totalItems = count($rows) - 1; // Excluindo o cabeçalho
    $addedCount = 0;
    $updatedCount = 0;

    echo '<script>document.getElementById("progressBar").style.width = "0%";</script>';

    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // Ignorar cabeçalho

        $codigo = $row[0];
        $descricao = $row[1];

        // Verificar se o componente já existe
        $stmt = $conn->prepare("SELECT COUNT(*) FROM componentes WHERE codigo = :codigo");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists) {
            // Atualizar o componente existente
            $updateStmt = $conn->prepare("UPDATE componentes SET descricao = :descricao WHERE codigo = :codigo");
            $updateStmt->bindParam(':descricao', $descricao);
            $updateStmt->bindParam(':codigo', $codigo);
            $updateStmt->execute();
            $updatedCount++;
        } else {
            // Inserir novo componente
            $insertStmt = $conn->prepare("INSERT INTO componentes (codigo, descricao) VALUES (:codigo, :descricao)");
            $insertStmt->bindParam(':codigo', $codigo);
            $insertStmt->bindParam(':descricao', $descricao);
            $insertStmt->execute();
            $addedCount++;
        }

        // Atualizar a barra de progresso
        $progress = intval((($index) / $totalItems) * 100);
        echo "<script>document.getElementById('progressBar').style.width = '$progress%';</script>";
        ob_flush();
        flush();
    }

    // Mensagem final de sucesso com contagem
    $message = "Importação concluída: $addedCount cadastrados, $updatedCount atualizados.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Componentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Importar Componentes</h2>
        
        <!-- Formulário de Upload -->
        <form action="importaComponentes.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Selecione o arquivo (.xls)</label>
                <input type="file" class="form-control" id="file" name="file" accept=".xls" required>
            </div>
            <button type="submit" class="btn btn-primary">Importar</button>
        </form>

        <!-- Barra de Progresso -->
        <div class="progress mt-3">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
        </div>

        <!-- Mensagem de Resultados -->
        <?php if ($message): ?>
            <div class="alert alert-success mt-3" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
