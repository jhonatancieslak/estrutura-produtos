<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

// Obtendo o ID da estrutura para editar
$idEstrutura = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Consultando detalhes da estrutura e seus componentes
$stmt = $conn->prepare("SELECT * FROM estrutura WHERE id = :id");
$stmt->bindParam(':id', $idEstrutura, PDO::PARAM_INT);
$stmt->execute();
$estrutura = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$estrutura) {
    echo "Estrutura não encontrada!";
    exit;
}

// Consultando componentes associados à estrutura
$componentesStmt = $conn->prepare("SELECT * FROM componenteEstrutura WHERE idEstrutura = :idEstrutura");
$componentesStmt->bindParam(':idEstrutura', $idEstrutura, PDO::PARAM_INT);
$componentesStmt->execute();
$componentes = $componentesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estrutura - <?= htmlspecialchars($estrutura['nomeEstrutura']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Editar Estrutura - <?= htmlspecialchars($estrutura['nomeEstrutura']) ?></h2>
        <form action="salvarEstruturaEditada.php" method="POST" id="editarEstruturaForm">
            <input type="hidden" name="idEstrutura" value="<?= $idEstrutura ?>">

            <!-- Adicionar novo componente -->
            <div class="mt-4">
                <h5>Adicionar Novo Componente</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="codigoNovo" class="form-label">Código</label>
                        <input type="text" id="codigoNovo" class="form-control" placeholder="Código do Produto">
                    </div>
                    <div class="col-md-4">
                        <label for="descricaoNovo" class="form-label">Descrição</label>
                        <input type="text" id="descricaoNovo" class="form-control" placeholder="Descrição" readonly>
                    </div>
                    <div class="col-md-2">
                        <label for="quantidadeNovo" class="form-label">Quantidade</label>
                        <input type="number" id="quantidadeNovo" class="form-control" placeholder="Quantidade">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" onclick="adicionarComponente()">Adicionar</button>
                    </div>
                </div>
            </div>

            <!-- Botões para salvar e voltar -->
            <div class="d-flex justify-content-between mt-3">
                <button type="submit" class="btn btn-success">Salvar Estrutura</button>
                <a href="nomeEstrutura.php" class="btn btn-secondary">Voltar</a>
            </div>

            <!-- Listagem e edição de componentes existentes -->
            <div id="componentesContainer" class="mt-3">
                <?php foreach ($componentes as $componente): ?>
                    <div class="row componente-item mb-3" data-codigo="<?= htmlspecialchars($componente['codigo']) ?>">
                        <input type="hidden" name="codigoExistente[]" value="<?= htmlspecialchars($componente['codigo']) ?>">
                        <input type="hidden" name="descricaoExistente[]" value="<?= htmlspecialchars($componente['descricaoComponente']) ?>">
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($componente['codigo']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Descrição</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($componente['descricaoComponente'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantidade</label>
                            <input type="number" class="form-control" name="quantidadeExistente[]" value="<?= htmlspecialchars($componente['qntdUtilizada']) ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-btn" onclick="marcarParaExcluir(this)">Excluir</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>

    <!-- Toasts de sucesso e erro -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastSuccess" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Estrutura atualizada com sucesso!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="toastError" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Ocorreu um erro ao atualizar a estrutura.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function marcarParaExcluir(button) {
            button.closest('.componente-item').remove();
            const codigo = button.closest('.componente-item').querySelector('input[name="codigoExistente[]"]').value;
            const excluirInput = document.createElement('input');
            excluirInput.type = 'hidden';
            excluirInput.name = 'excluir[]';
            excluirInput.value = codigo;
            document.getElementById('editarEstruturaForm').appendChild(excluirInput);
        }

        function adicionarComponente() {
            const codigo = document.getElementById('codigoNovo').value;
            const descricao = document.getElementById('descricaoNovo').value;
            const quantidade = document.getElementById('quantidadeNovo').value;

            if (codigo && quantidade) {
                const existente = document.querySelector(`.componente-item[data-codigo="${codigo}"]`);
                
                if (existente) {
                    if (confirm("O código já existe. Deseja somar a quantidade?")) {
                        const quantidadeExistente = existente.querySelector('input[name="quantidadeExistente[]"]');
                        quantidadeExistente.value = parseInt(quantidadeExistente.value) + parseInt(quantidade);
                    }
                } else {
                    const container = document.getElementById('componentesContainer');
                    const newFields = document.createElement('div');
                    newFields.classList.add('row', 'componente-item', 'mb-3');
                    newFields.dataset.codigo = codigo;
                    newFields.innerHTML = `
                        <input type="hidden" name="codigoNovo[]" value="${codigo}">
                        <input type="hidden" name="descricaoNovo[]" value="${descricao}">
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" value="${codigo}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Descrição</label>
                            <input type="text" class="form-control" value="${descricao}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantidade</label>
                            <input type="number" class="form-control" name="quantidadeNovo[]" value="${quantidade}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-btn" onclick="marcarParaExcluir(this)">Excluir</button>
                        </div>
                    `;
                    container.insertBefore(newFields, container.firstChild);
                }

                document.getElementById('codigoNovo').value = '';
                document.getElementById('descricaoNovo').value = '';
                document.getElementById('quantidadeNovo').value = '';
            } else {
                alert("Informe o código e a quantidade do componente.");
            }
        }

        document.getElementById('codigoNovo').addEventListener('blur', function () {
            const codigo = this.value;
            if (codigo) {
                fetch(`buscarProduto.php?codigo=${codigo}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('descricaoNovo').value = data.produto || "Produto não encontrado";
                    });
            }
        });

        function showToast(success = true) {
            const toast = new bootstrap.Toast(success ? document.getElementById('toastSuccess') : document.getElementById('toastError'));
            toast.show();
            if (success) {
                setTimeout(() => window.location.href = "index.php", 2000);  // Redirecionar após 2 segundos
            }
        }
    </script>
</body>
</html>
