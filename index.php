<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

// Configuração de paginação
$limit = 10; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Consultar todas as estruturas com paginação
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $conn->prepare("SELECT * FROM estrutura WHERE nomeEstrutura LIKE :search LIMIT :limit OFFSET :offset");
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$estruturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar o total de registros para a paginação
$countStmt = $conn->prepare("SELECT COUNT(*) FROM estrutura WHERE nomeEstrutura LIKE :search");
$countStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$countStmt->execute();
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nome Estrutura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Estruturas Cadastradas</h2>
            <div>
                <a href="novoNomeEstrutura.php" class="btn btn-primary">Nome Estrutura</a>
                <a href="criarEstrutura.php" class="btn btn-success">Criar Estrutura</a>
            </div>
        </div>

        <!-- Barra de busca -->
        <form class="mb-3" method="GET" action="">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar estrutura..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
            </div>
        </form>

        <!-- Tabela de estruturas -->
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome Estrutura</th>
                    <th>Data Criação</th>
                    <th>Data Alteração</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estruturas as $estrutura): ?>
                    <tr>
                        <td><?= htmlspecialchars($estrutura['id']) ?></td>
                        <td><?= htmlspecialchars($estrutura['nomeEstrutura']) ?></td>
                        <td><?= htmlspecialchars($estrutura['dataCriacao']) ?></td>
                        <td><?= htmlspecialchars($estrutura['dataAlteracao']) ?></td>
                        <td>
                            <a href="editarEstrutura.php?id=<?= $estrutura['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="excluirEstrutura.php?id=<?= $estrutura['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta estrutura?');">Excluir</a>
                            <a href="verEstrutura.php?id=<?= $estrutura['id'] ?>" class="btn btn-info btn-sm">Ver Estrutura</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- Toasts de sucesso e erro -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastSuccess" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Operação realizada com sucesso!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="toastError" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Ocorreu um erro ao realizar a operação.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (isset($_GET['success'])): ?>
        var toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
        toastSuccess.show();
        <?php elseif (isset($_GET['error'])): ?>
        var toastError = new bootstrap.Toast(document.getElementById('toastError'));
        toastError.show();
        <?php endif; ?>
    </script>
</body>
</html>
