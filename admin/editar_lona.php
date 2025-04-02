<?php
session_start();
// Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit;
// }

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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$codigo = $medidas = $haste = $referencias = $imagem = $nota = "";
$carros = [];

// Fetch the lona data if editing existing item
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM Lonas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $lona = $result->fetch_assoc();
        $codigo = $lona['codigo'];
        $medidas = $lona['medidas'];
        $haste = $lona['haste'];
        $referencias = $lona['referencias'];
        $imagem = $lona['imagem'];
        $nota = $lona['nota'];
        
        // Fetch associated cars
        $carQuery = "SELECT c.id, c.marca, c.modelo, c.ano_inicio, c.ano_fim, c.observacao 
                    FROM Carros c 
                    JOIN LonaCarros lc ON c.id = lc.carro_id 
                    WHERE lc.lona_id = ?";
        $stmtCar = $conn->prepare($carQuery);
        $stmtCar->bind_param("i", $id);
        $stmtCar->execute();
        $carResult = $stmtCar->get_result();
        
        while ($row = $carResult->fetch_assoc()) {
            $carros[] = $row;
        }
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['codigo'];
    $medidas = $_POST['medidas'];
    $haste = $_POST['haste'];
    $referencias = $_POST['referencias'];
    $nota = $_POST['nota'];
    
    // Handle image upload
    $imagem = $id > 0 ? $imagem : "uploads/sem-imagem.jpg"; // Default or existing
    if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
        $targetDir = "../uploads/";
        $fileName = basename($_FILES["imagem"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Allow certain file formats
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array($fileType, $allowTypes)) {
            // Upload file
            if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $targetFilePath)) {
                $imagem = "uploads/" . $fileName;
            }
        }
    }
    
    // Save or update lona information
    if ($id > 0) {
        // Update existing lona
        $stmt = $conn->prepare("UPDATE Lonas SET codigo = ?, medidas = ?, haste = ?, referencias = ?, imagem = ?, nota = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $codigo, $medidas, $haste, $referencias, $imagem, $nota, $id);
    } else {
        // Insert new lona
        $stmt = $conn->prepare("INSERT INTO Lonas (codigo, medidas, haste, referencias, imagem, nota) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $codigo, $medidas, $haste, $referencias, $imagem, $nota);
    }
    
    if ($stmt->execute()) {
        if ($id == 0) {
            $id = $conn->insert_id; // Get ID of newly inserted lona
        }
        
        // Now process the cars
        if (isset($_POST['car_marca']) && is_array($_POST['car_marca'])) {
            // First remove all existing car associations
            $deleteStmt = $conn->prepare("DELETE FROM LonaCarros WHERE lona_id = ?");
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();
            
            // Now add new ones
            for ($i = 0; $i < count($_POST['car_marca']); $i++) {
                if (!empty($_POST['car_marca'][$i]) && !empty($_POST['car_modelo'][$i])) {
                    // First check if car already exists
                    $marca = $_POST['car_marca'][$i];
                    $modelo = $_POST['car_modelo'][$i];
                    $ano_inicio = $_POST['car_ano_inicio'][$i] ?? null;
                    $ano_fim = $_POST['car_ano_fim'][$i] ?? null;
                    $observacao = $_POST['car_observacao'][$i] ?? null;
                    
                    $carStmt = $conn->prepare("SELECT id FROM Carros WHERE marca = ? AND modelo = ? AND 
                                            (ano_inicio = ? OR (ano_inicio IS NULL AND ? IS NULL)) AND 
                                            (ano_fim = ? OR (ano_fim IS NULL AND ? IS NULL)) AND
                                            (observacao = ? OR (observacao IS NULL AND ? IS NULL))");
                    $carStmt->bind_param("ssssssss", $marca, $modelo, $ano_inicio, $ano_inicio, $ano_fim, $ano_fim, $observacao, $observacao);
                    $carStmt->execute();
                    $carResult = $carStmt->get_result();
                    
                    $car_id = 0;
                    if ($carResult->num_rows > 0) {
                        $car = $carResult->fetch_assoc();
                        $car_id = $car['id'];
                    } else {
                        // Car doesn't exist, insert new
                        $insertCarStmt = $conn->prepare("INSERT INTO Carros (marca, modelo, ano_inicio, ano_fim, observacao) VALUES (?, ?, ?, ?, ?)");
                        $insertCarStmt->bind_param("sssss", $marca, $modelo, $ano_inicio, $ano_fim, $observacao);
                        $insertCarStmt->execute();
                        $car_id = $conn->insert_id;
                    }
                    
                    // Now link the car to the lona
                    if ($car_id > 0) {
                        $linkStmt = $conn->prepare("INSERT INTO LonaCarros (lona_id, carro_id) VALUES (?, ?)");
                        $linkStmt->bind_param("ii", $id, $car_id);
                        $linkStmt->execute();
                    }
                }
            }
        }
        
        header("Location: admin.php?success=1");
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id > 0 ? 'Editar' : 'Adicionar'; ?> Lona de Freio - JP2</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-form {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 33, 71, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #002147;
        }
        input[type="text"], select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .car-item {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 3px solid #ff6600;
        }
        .car-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .car-field {
            flex: 1;
            min-width: 120px;
        }
        .car-field input {
            width: 100%;
        }
        .btn-row {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #ff6600;
            color: white;
        }
        .btn-secondary {
            background-color: #002147;
            color: white;
        }
        .btn-danger {
            background-color: #d9534f;
            color: white;
        }
        .btn-add {
            background-color: #5cb85c;
            color: white;
        }
        .remove-car {
            color: #d9534f;
            cursor: pointer;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="contact-header">
            <p>Desde 1995</p>
            <p>(19) 3273-8199</p>
        </div>
        
        <div class="logo-container">
            <img src="../uploads/logo.png" alt="JP2 Logo" class="logo">
        </div>
        
        <nav class="main-nav">
            <ul>
                <div class="nav-left">
                    <li><a href="../index.php">INÍCIO</a></li>
                    <li><a href="../catalogo.php">CATÁLOGO</a></li>
                </div>
                <div class="nav-right">
                    <li><a href="admin.php" class="active">ADMIN</a></li>
                    <li><a href="logout.php">SAIR</a></li>
                </div>
            </ul>
        </nav>
    </header>
    
    <div class="orange-bar"></div>
    
    <div class="catalogo-title">
        <h1><?php echo $id > 0 ? 'EDITAR' : 'ADICIONAR'; ?> LONA DE FREIO</h1>
    </div>
    
    <main class="site-content">
        <div class="admin-form">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . ($id > 0 ? "?id=$id" : ""); ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="codigo">Código da Lona *</label>
                    <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($codigo); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="medidas">Dimensões/Medidas *</label>
                    <input type="text" id="medidas" name="medidas" value="<?php echo htmlspecialchars($medidas); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="haste">Haste</label>
                    <select id="haste" name="haste">
                        <option value="Com" <?php echo $haste == 'Com' ? 'selected' : ''; ?>>Com Haste</option>
                        <option value="Sem" <?php echo $haste == 'Sem' ? 'selected' : ''; ?>>Sem Haste</option>
                        <option value="N/A" <?php echo $haste == 'N/A' ? 'selected' : ''; ?>>Não Aplicável</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="referencias">Números de Referência (separados por vírgula)</label>
                    <textarea id="referencias" name="referencias" rows="3"><?php echo htmlspecialchars($referencias); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagem">Imagem do Produto</label>
                    <?php if (!empty($imagem) && $imagem != 'uploads/sem-imagem.jpg'): ?>
                        <p>Imagem atual: <img src="../<?php echo htmlspecialchars($imagem); ?>" alt="Imagem atual" style="max-height: 100px;"></p>
                    <?php endif; ?>
                    <input type="file" id="imagem" name="imagem" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="nota">Nota Especial</label>
                    <input type="text" id="nota" name="nota" value="<?php echo htmlspecialchars($nota); ?>">
                </div>
                
                <div class="form-group">
                    <label>Aplicações (Carros Compatíveis)</label>
                    <div id="cars-container">
                        <?php if (count($carros) > 0): ?>
                            <?php foreach ($carros as $carro): ?>
                                <div class="car-item">
                                    <div class="car-row">
                                        <div class="car-field">
                                            <label>Marca *</label>
                                            <input type="text" name="car_marca[]" value="<?php echo htmlspecialchars($carro['marca']); ?>" required>
                                        </div>
                                        <div class="car-field">
                                            <label>Modelo *</label>
                                            <input type="text" name="car_modelo[]" value="<?php echo htmlspecialchars($carro['modelo']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="car-row">
                                        <div class="car-field">
                                            <label>Ano Início</label>
                                            <input type="text" name="car_ano_inicio[]" value="<?php echo htmlspecialchars($carro['ano_inicio']); ?>" placeholder="Ex: 2015">
                                        </div>
                                        <div class="car-field">
                                            <label>Ano Fim</label>
                                            <input type="text" name="car_ano_fim[]" value="<?php echo htmlspecialchars($carro['ano_fim']); ?>" placeholder="Ex: 2020">
                                        </div>
                                    </div>
                                    <div class="car-row">
                                        <div class="car-field">
                                            <label>Observação</label>
                                            <input type="text" name="car_observacao[]" value="<?php echo htmlspecialchars($carro['observacao']); ?>" placeholder="Ex: Motor 1.6">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-danger remove-car">Remover</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="car-item">
                                <div class="car-row">
                                    <div class="car-field">
                                        <label>Marca *</label>
                                        <input type="text" name="car_marca[]" required>
                                    </div>
                                    <div class="car-field">
                                        <label>Modelo *</label>
                                        <input type="text" name="car_modelo[]" required>
                                    </div>
                                </div>
                                <div class="car-row">
                                    <div class="car-field">
                                        <label>Ano Início</label>
                                        <input type="text" name="car_ano_inicio[]" placeholder="Ex: 2015">
                                    </div>
                                    <div class="car-field">
                                        <label>Ano Fim</label>
                                        <input type="text" name="car_ano_fim[]" placeholder="Ex: 2020">
                                    </div>
                                </div>
                                <div class="car-row">
                                    <div class="car-field">
                                        <label>Observação</label>
                                        <input type="text" name="car_observacao[]" placeholder="Ex: Motor 1.6">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger remove-car">Remover</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="add-car" class="btn btn-add">Adicionar Carro</button>
                </div>
                
                <div class="btn-row">
                    <a href="admin.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </main>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>JP2 Freios</h3>
                <p>Especialistas em lonas e peças para sistemas de freios industriais e automotivos, fornecendo qualidade e segurança desde 1995.</p>
                <div class="footer-social">
                    <a href="#"><i class="fa fa-facebook"></i></a>
                    <a href="#"><i class="fa fa-instagram"></i></a>
                    <a href="#"><i class="fa fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Contato</h3>
                <div class="footer-contact-item">
                    <i class="fa fa-map-marker"></i>
                    <p>Rua Antonio Lapa, 78<br>Campinas - SP</p>
                </div>
                <div class="footer-contact-item">
                    <i class="fa fa-phone"></i>
                    <p>(19) 3273-8199</p>
                </div>
                <div class="footer-contact-item">
                    <i class="fa fa-envelope"></i>
                    <p>contato@jp2freios.com.br</p>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="../index.php">Início</a></li>
                    <li><a href="../catalogo.php">Catálogo</a></li>
                    <li><a href="admin.php">Admin</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> JP2 Freios Industriais e Automotivos. Todos os direitos reservados.</p>
        </div>
    </footer>
    
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add new car
            document.getElementById('add-car').addEventListener('click', function() {
                const container = document.getElementById('cars-container');
                const newCar = document.createElement('div');
                newCar.className = 'car-item';
                newCar.innerHTML = `
                    <div class="car-row">
                        <div class="car-field">
                            <label>Marca *</label>
                            <input type="text" name="car_marca[]" required>
                        </div>
                        <div class="car-field">
                            <label>Modelo *</label>
                            <input type="text" name="car_modelo[]" required>
                        </div>
                    </div>
                    <div class="car-row">
                        <div class="car-field">
                            <label>Ano Início</label>
                            <input type="text" name="car_ano_inicio[]" placeholder="Ex: 2015">
                        </div>
                        <div class="car-field">
                            <label>Ano Fim</label>
                            <input type="text" name="car_ano_fim[]" placeholder="Ex: 2020">
                        </div>
                    </div>
                    <div class="car-row">
                        <div class="car-field">
                            <label>Observação</label>
                            <input type="text" name="car_observacao[]" placeholder="Ex: Motor 1.6">
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger remove-car">Remover</button>
                `;
                container.appendChild(newCar);
                
                // Add event listener for the new remove button
                newCar.querySelector('.remove-car').addEventListener('click', function() {
                    container.removeChild(newCar);
                });
            });
            
            // Remove car (for existing items)
            document.querySelectorAll('.remove-car').forEach(button => {
                button.addEventListener('click', function() {
                    const carItem = this.closest('.car-item');
                    carItem.parentNode.removeChild(carItem);
                });
            });
        });
    </script>
</body>
</html> 