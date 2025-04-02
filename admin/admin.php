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

// Handle delete if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Delete associated car relationships first (foreign key constraint)
    $stmt = $conn->prepare("DELETE FROM LonaCarros WHERE lona_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Then delete the lona
    $stmt = $conn->prepare("DELETE FROM Lonas WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: admin.php?deleted=1");
        exit;
    } else {
        $error = "Error deleting product: " . $conn->error;
    }
}

// Get all lonas
$sql = "SELECT l.*, COUNT(lc.carro_id) AS car_count 
        FROM Lonas l 
        LEFT JOIN LonaCarros lc ON l.id = lc.lona_id 
        GROUP BY l.id 
        ORDER BY l.codigo";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - JP2</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #ff6600;
            color: white;
        }
        
        .btn-edit {
            background-color: #002147;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-delete {
            background-color: #d9534f;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: #002147;
            color: white;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f0f0f0;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .thumbnail {
            max-width: 60px;
            max-height: 60px;
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
        <h1>ADMINISTRAÇÃO DE PRODUTOS</h1>
    </div>
    
    <main class="site-content admin-container">
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                Produto salvo com sucesso!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="success-message">
                Produto excluído com sucesso!
            </div>
        <?php endif; ?>
        
        <div class="admin-header">
            <h2>Lonas de Freio</h2>
            <a href="editar_lona.php" class="btn btn-primary">Adicionar Nova Lona</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>Código</th>
                    <th>Dimensões</th>
                    <th>Haste</th>
                    <th>Nº Aplicações</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo htmlspecialchars($row['imagem']); ?>" alt="<?php echo htmlspecialchars($row['codigo']); ?>" class="thumbnail">
                            </td>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['medidas']); ?></td>
                            <td><?php echo htmlspecialchars($row['haste']); ?></td>
                            <td><?php echo $row['car_count']; ?> veículo(s)</td>
                            <td class="actions">
                                <a href="editar_lona.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Editar</a>
                                <a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Nenhum produto encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
</body>
</html> 