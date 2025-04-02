<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Lonas de Freio - JP2</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            margin: 15% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
        }

        .modal-content img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header>
        <h1>Gerenciar Lonas de Freio - JP2</h1>
    </header>

    <main>
        <!-- Form to Add or Update Lona -->
        <form method="POST" action="create_lona.php" enctype="multipart/form-data">
            <input type="hidden" name="id" id="id">
            <label for="codigo">Código:</label>
            <input type="text" id="codigo" name="codigo" required><br>

            <label for="medidas">Medidas:</label>
            <input type="text" id="medidas" name="medidas" required><br>

            <label for="referencias">Referências (separadas por vírgula):</label>
            <input type="text" id="referencias" name="referencias" required><br>

            <label for="aplicacao">Aplicação:</label>
            <input type="text" id="aplicacao" name="aplicacao" required><br>

            <label for="haste">Haste:</label>
            <select id="haste" name="haste" required>
                <option value="Com">Com</option>
                <option value="Sem">Sem</option>
            </select><br>

            <label for="imagem">Imagem:</label>
            <input type="file" id="imagem" name="imagem" accept="image/*"><br>

            <button type="submit" name="action" value="create">Adicionar Lona</button>
            <button type="submit" name="action" value="update">Atualizar Lona</button>
        </form>

        <!-- Display Existing Lonas -->
        <h2>Lonas Existentes</h2>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Medidas</th>
                    <th>Referências</th>
                    <th>Aplicação</th>
                    <th>Haste</th>
                    <th>Imagem</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "JP2Catalog";

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $id = isset($_POST['id']) ? $_POST['id'] : null;
                    $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : null;
                    $medidas = isset($_POST['medidas']) ? $_POST['medidas'] : null;
                    $referencias = isset($_POST['referencias']) ? $_POST['referencias'] : null;
                    $aplicacao = isset($_POST['aplicacao']) ? $_POST['aplicacao'] : null;
                    $haste = isset($_POST['haste']) ? $_POST['haste'] : null;
                    $imagem = isset($_FILES['imagem']['name']) && $_FILES['imagem']['name'] ? "uploads/" . basename($_FILES["imagem"]["name"]) : null;

                    if ($_POST['action'] == 'create' && $codigo && $medidas && $referencias && $aplicacao && $haste) {
                        if ($imagem) {
                            move_uploaded_file($_FILES["imagem"]["tmp_name"], $imagem);
                        }
                        $stmt = $conn->prepare("INSERT INTO Lonas (codigo, medidas, referencias, aplicacao, haste, imagem) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssss", $codigo, $medidas, $referencias, $aplicacao, $haste, $imagem);
                        $stmt->execute();
                        $stmt->close();
                    } elseif ($_POST['action'] == 'update' && $id) {
                        if ($imagem) {
                            move_uploaded_file($_FILES["imagem"]["tmp_name"], $imagem);
                            $stmt = $conn->prepare("UPDATE Lonas SET codigo=?, medidas=?, referencias=?, aplicacao=?, haste=?, imagem=? WHERE id=?");
                            $stmt->bind_param("ssssssi", $codigo, $medidas, $referencias, $aplicacao, $haste, $imagem, $id);
                        } else {
                            $stmt = $conn->prepare("UPDATE Lonas SET codigo=?, medidas=?, referencias=?, aplicacao=?, haste=? WHERE id=?");
                            $stmt->bind_param("sssssi", $codigo, $medidas, $referencias, $aplicacao, $haste, $id);
                        }
                        $stmt->execute();
                        $stmt->close();
                    } elseif ($_POST['action'] == 'delete' && $id) {
                        $stmt = $conn->prepare("DELETE FROM Lonas WHERE id=?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }

                // Fetch and display existing lonas
                $result = $conn->query("SELECT * FROM Lonas");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['codigo']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['medidas']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['referencias']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['aplicacao']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['haste']) . "</td>";
                        echo "<td><img src='" . htmlspecialchars($row['imagem']) . "' alt='Imagem' style='width:50px;height:auto;' onclick='openModal(this)'></td>";
                        echo "<td>
                                <form method='POST' action='create_lona.php' style='display:inline;'>
                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                    <button type='submit' name='action' value='delete'>Excluir</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Nenhuma lona encontrada.</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>

        <!-- Modal for Image Display -->
        <div id="imageModal" class="modal">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-content">
                <img id="modalImage" src="" alt="Imagem Grande">
            </div>
        </div>
    </main>

    <script>
        function openModal(imgElement) {
            var modal = document.getElementById("imageModal");
            var modalImg = document.getElementById("modalImage");
            modal.style.display = "block";
            modalImg.src = imgElement.src;
        }

        function closeModal() {
            var modal = document.getElementById("imageModal");
            modal.style.display = "none";
        }
    </script>
</body>

</html> 