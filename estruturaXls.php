<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

// Consultar as estruturas para o dropdown
$stmt = $conn->prepare("SELECT id, nomeEstrutura FROM estrutura");
$stmt->execute();
$estruturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Componentes via XLS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Importar Componentes para Estrutura</h2>
            <a href="index.php" class="btn btn-secondary">Voltar</a>
        </div>

        <!-- Seleção de Estrutura -->
        <form id="importXlsForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="idEstrutura" class="form-label">Selecione a Estrutura</label>
                <select name="idEstrutura" id="idEstrutura" class="form-select" required>
                    <option value="">Escolha uma estrutura</option>
                    <?php foreach ($estruturas as $estrutura): ?>
                        <option value="<?= htmlspecialchars($estrutura['id']) ?>"><?= htmlspecialchars($estrutura['nomeEstrutura']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Upload de arquivo XLS -->
            <div class="mb-3">
                <label for="xlsFile" class="form-label">Selecione o arquivo XLS</label>
                <input type="file" id="xlsFile" name="xlsFile" class="form-control" accept=".xls,.xlsx" required>
            </div>
            
            <!-- Botão para processar o arquivo -->
            <button type="button" class="btn btn-primary" onclick="processarXls()">Processar Arquivo</button>
        </form>

        <!-- Progress bar para o carregamento dos componentes -->
        <div class="progress mt-4" id="progressBar" style="display: none;">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>

        <!-- Tabela de componentes encontrados e não encontrados -->
        <div id="componentesResultado" class="mt-4" style="display: none;">
            <h5>Componentes no Arquivo</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Quantidade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="componentesBody"></tbody>
            </table>
            <button type="button" class="btn btn-success" onclick="gravarComponentes()">Confirmar Gravação</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function processarXls() {
            const formData = new FormData();
            const estruturaId = document.getElementById("idEstrutura").value;
            const fileInput = document.getElementById("xlsFile");

            if (!estruturaId) {
                alert("Por favor, selecione uma estrutura.");
                return;
            }

            if (fileInput.files.length === 0) {
                alert("Por favor, selecione um arquivo XLS.");
                return;
            }

            formData.append("file", fileInput.files[0]);
            formData.append("idEstrutura", estruturaId);

            document.getElementById("progressBar").style.display = "block";
            document.querySelector(".progress-bar").style.width = "0%";
            document.querySelector(".progress-bar").innerText = "0%";

            fetch("processarXls.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    document.getElementById("componentesResultado").style.display = "block";
                    const componentesBody = document.getElementById("componentesBody");
                    componentesBody.innerHTML = "";

                    data.componentesEncontrados.forEach(componente => {
                        componentesBody.innerHTML += `
                            <tr>
                                <td>${componente.codigo}</td>
                                <td>${componente.descricao}</td>
                                <td>${componente.quantidade}</td>
                                <td>Encontrado</td>
                            </tr>
                        `;
                    });

                    data.componentesNaoEncontrados.forEach(componente => {
                        componentesBody.innerHTML += `
                            <tr class="table-danger">
                                <td>${componente.codigo}</td>
                                <td>Não cadastrado</td>
                                <td>${componente.quantidade}</td>
                                <td>Não Encontrado</td>
                            </tr>
                        `;
                    });
                } else {
                    alert("Erro ao processar o arquivo: " + data.message);
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                alert("Erro ao processar o arquivo.");
            });
        }

        function gravarComponentes() {
            const estruturaId = document.getElementById("idEstrutura").value;
            const componentesEncontrados = [];

            document.querySelectorAll("#componentesBody tr").forEach(row => {
                if (!row.classList.contains("table-danger")) {
                    const codigo = row.cells[0].innerText;
                    const descricao = row.cells[1].innerText;
                    const quantidade = parseInt(row.cells[2].innerText);

                    componentesEncontrados.push({ codigo, descricao, quantidade });
                }
            });

            if (componentesEncontrados.length === 0) {
                alert("Nenhum componente válido para gravação.");
                return;
            }

            fetch("gravaXls.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    idEstrutura: estruturaId,
                    componentes: JSON.stringify(componentesEncontrados),
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert(data.message);
                    window.location.href = "index.php"; // Redireciona após sucesso
                } else {
                    alert("Erro: " + data.message);
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                alert("Erro ao gravar os componentes.");
            });
        }
    </script>
</body>
</html>
