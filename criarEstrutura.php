<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

// Consultar as estruturas para o dropdown
$stmt = $conn->prepare("SELECT id, nomeEstrutura FROM estrutura");
$stmt->execute();
$estruturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detectar se a página foi redirecionada após um sucesso ou erro
$success = isset($_GET['success']);
$error = isset($_GET['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Componente para Estrutura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <!-- Toasts de sucesso e erro no topo -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
            <div id="toastSuccess" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" <?= $success ? 'data-bs-autohide="false"' : '' ?>>
                <div class="d-flex">
                    <div class="toast-body">
                        Componentes adicionados com sucesso!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <div id="toastError" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" <?= $error ? 'data-bs-autohide="false"' : '' ?>>
                <div class="d-flex">
                    <div class="toast-body">
                        Ocorreu um erro ao adicionar componentes.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Título e Botões de Voltar e Importar XLS -->
        <div class="d-flex justify-content-between align-items-center mt-5">
            <h2>Adicionar Componentes para Estrutura</h2>
            <div>
                <a href="index.php" class="btn btn-secondary">Voltar</a>
                <a href="estruturaXls.php" class="btn btn-info">Importar XLS</a>
            </div>
        </div>

        <!-- Selecionar Estrutura -->
        <form action="salvarComponenteEstrutura.php" method="POST" id="componentesForm">
            <div class="mb-3">
                <label for="idEstrutura" class="form-label">Estrutura</label>
                <select name="idEstrutura" id="idEstrutura" class="form-select" required>
                    <option value="">Selecione a estrutura</option>
                    <?php foreach ($estruturas as $estrutura): ?>
                        <option value="<?= $estrutura['id'] ?>"><?= htmlspecialchars($estrutura['nomeEstrutura']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Campos para adicionar componentes manualmente -->
            <div id="componentesContainer" class="mt-3">
                <div class="row componente-item">
                    <div class="col-md-4 mb-3">
                        <label for="codigo[]" class="form-label">Código</label>
                        <input type="text" class="form-control codigo-input" name="codigo[]" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="produto[]" class="form-label">Produto</label>
                        <input type="text" class="form-control produto-input" name="produto[]" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="quantidade[]" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" name="quantidade[]" required>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary" id="addMore">Adicionar Mais</button>
            <button type="submit" class="btn btn-success mt-3">Gravar</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Exibir o toast de acordo com o sucesso ou erro detectado no PHP
        <?php if ($success): ?>
            var toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
            toastSuccess.show();
        <?php elseif ($error): ?>
            var toastError = new bootstrap.Toast(document.getElementById('toastError'));
            toastError.show();
        <?php endif; ?>

        // Adicionar mais campos de componentes
        document.getElementById('addMore').addEventListener('click', function () {
            const container = document.getElementById('componentesContainer');
            const newFields = document.createElement('div');
            newFields.classList.add('row', 'componente-item');
            newFields.innerHTML = `
                <div class="col-md-4 mb-3">
                    <label for="codigo[]" class="form-label">Código</label>
                    <input type="text" class="form-control codigo-input" name="codigo[]" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="produto[]" class="form-label">Produto</label>
                    <input type="text" class="form-control produto-input" name="produto[]" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="quantidade[]" class="form-label">Quantidade</label>
                    <input type="number" class="form-control" name="quantidade[]" required>
                </div>
            `;
            container.appendChild(newFields);
        });

        // Buscar nome do produto com base no código inserido
        document.addEventListener('input', function(event) {
            if (event.target.classList.contains('codigo-input')) {
                const codigo = event.target.value;
                const produtoInput = event.target.closest('.componente-item').querySelector('.produto-input');

                if (codigo) {
                    fetch(`buscarProduto.php?codigo=${codigo}`)
                        .then(response => response.json())
                        .then(data => {
                            produtoInput.value = data.produto || "Produto não encontrado";
                        });
                } else {
                    produtoInput.value = '';
                }
            }
        });
    </script>
</body>
</html>
