<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Estrutura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Criar Nova Estrutura</h2>
            <!-- Botão de Voltar -->
            <a href="nomeEstrutura.php" class="btn btn-secondary">Voltar</a>
        </div>
        <form action="salvarEstrutura.php" method="POST" class="mt-3">
            <div class="mb-3">
                <label for="nomeEstrutura" class="form-label">Nome Estrutura</label>
                <input type="text" class="form-control" id="nomeEstrutura" name="nomeEstrutura" required>
            </div>
            <button type="submit" class="btn btn-success">Salvar</button>
        </form>
    </div>

    <!-- Toasts de sucesso e erro -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastSuccess" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Estrutura criada com sucesso!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="toastError" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Erro ao criar estrutura. Nome duplicado!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($_GET['success'])): ?>
    <script>
        var toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
        toastSuccess.show();
    </script>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <script>
        var toastError = new bootstrap.Toast(document.getElementById('toastError'));
        toastError.show();
    </script>
    <?php endif; ?>
</body>
</html>
