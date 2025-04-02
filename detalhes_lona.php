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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: catalogo.php");
    exit;
}

// Get lona details
$stmt = $conn->prepare("SELECT * FROM Lonas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: catalogo.php");
    exit;
}

$lona = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Lona <?php echo htmlspecialchars($lona['codigo']); ?> - JP2</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="main-header">
        <div class="contact-header">
            <p>Desde 1995</p>
            <p>(19) 3273-8199</p>
        </div>
        
        <div class="logo-container">
            <img src="uploads/logo.png" alt="JP2 Logo" class="logo">
        </div>
        
        <nav class="main-nav">
            <ul>
                <div class="nav-left">
                    <li><a href="index.php">INÍCIO</a></li>
                    <li><a href="catalogo.php" class="active">CATÁLOGO</a></li>
                </div>
                <div class="nav-right">
                    <li><a href="index.php#sobre-nos">SOBRE NÓS</a></li>
                    <li><a href="#">CONTATO</a></li>
                </div>
            </ul>
        </nav>
    </header>
    
    <div class="orange-bar"></div>
    
    <div class="catalogo-title">
        <h1>DETALHES DO PRODUTO</h1>
    </div>
    
    <main class="site-content">
        <div class="produto-detalhe">
            <div class="produto-content">
                <div class="produto-image-container">
                    <h2 class="produto-codigo-detalhe"><?php echo htmlspecialchars($lona['codigo']); ?></h2>
                    <img src="<?php echo htmlspecialchars($lona['imagem']); ?>" alt="<?php echo htmlspecialchars($lona['codigo']); ?>" class="produto-imagem-grande">
                </div>
                
                <div class="produto-info-container">
                    <div class="info-section">
                        <h3>Informações</h3>
                        <p><strong>Dimensões:</strong> <?php echo htmlspecialchars($lona['medidas']); ?></p>
                        <p><strong>Haste:</strong> <?php echo htmlspecialchars($lona['haste']); ?></p>
                        <?php if (!empty($lona['nota'])): ?>
                            <p class="nota"><?php echo htmlspecialchars($lona['nota']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="info-section">
                        <h3>Referências</h3>
                        <div class="referencias-lista detalhes-lista">
                            <?php
                            $referencias = explode(',', $lona['referencias']);
                            if (count($referencias) > 0) {
                                foreach ($referencias as $referencia) {
                                    if (!empty(trim($referencia))) {
                                        echo '<p class="referencia-item">' . htmlspecialchars(trim($referencia)) . '</p>';
                                    }
                                }
                            } else {
                                echo '<p>Nenhuma referência disponível</p>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3>Aplicações</h3>
                        <?php
                        // Get cars for this lona
                        $carSql = "SELECT c.* FROM Carros c 
                                  JOIN LonaCarros lc ON c.id = lc.carro_id 
                                  WHERE lc.lona_id = ? 
                                  ORDER BY c.marca, c.modelo";
                        $carStmt = $conn->prepare($carSql);
                        $carStmt->bind_param("i", $id);
                        $carStmt->execute();
                        $carResult = $carStmt->get_result();
                        
                        $currentBrand = "";
                        
                        if ($carResult->num_rows > 0) {
                            echo '<div class="aplicacoes-lista detalhes-lista">';
                            while ($car = $carResult->fetch_assoc()) {
                                // Show brand as header if it's a new brand
                                if ($car['marca'] != $currentBrand) {
                                    $currentBrand = $car['marca'];
                                    echo '<h4>' . htmlspecialchars($currentBrand) . '</h4>';
                                }
                                
                                // Display model with years
                                echo '<p class="modelo-item">';
                                echo htmlspecialchars($car['modelo']);
                                
                                // Add years if available
                                if (!empty($car['ano_inicio']) || !empty($car['ano_fim'])) {
                                    echo ' (';
                                    if (!empty($car['ano_inicio']) && !empty($car['ano_fim'])) {
                                        echo htmlspecialchars($car['ano_inicio']) . '-' . htmlspecialchars($car['ano_fim']);
                                    } else if (!empty($car['ano_inicio'])) {
                                        echo htmlspecialchars($car['ano_inicio']) . '+';
                                    } else if (!empty($car['ano_fim'])) {
                                        echo 'até ' . htmlspecialchars($car['ano_fim']);
                                    }
                                    echo ')';
                                }
                                
                                // Add observation if available
                                if (!empty($car['observacao'])) {
                                    echo ' - ' . htmlspecialchars($car['observacao']);
                                }
                                
                                echo '</p>';
                            }
                            echo '</div>';
                        } else {
                            echo '<p>Nenhuma aplicação disponível</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <a href="catalogo.php" class="voltar-botao">Voltar para o Catálogo</a>
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
                    <li><a href="index.php">Início</a></li>
                    <li><a href="catalogo.php">Catálogo</a></li>
                    <li><a href="index.php#sobre-nos">Sobre Nós</a></li>
                    <li><a href="#">Contato</a></li>
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