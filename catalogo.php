<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Lonas de Freio - JP2</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="main-header">
     
        
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
        <h1>CATÁLOGO DE PRODUTOS</h1>
    </div>
    
    <main class="site-content">
        <div class="search-container">
            <form method="GET" action="catalogo.php">
                <div class="search-group-full">
                    <h4>Buscar no catálogo</h4>
                    <div class="search-box">
                        <input type="text" name="busca" placeholder="Digite código, aplicação ou referência" value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
                        <button type="submit" class="search-button">Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="produtos-lista">
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

            $termoBusca = isset($_GET['busca']) ? $_GET['busca'] : '';
            
            $sql = "SELECT l.* FROM Lonas l";
            if (!empty($termoBusca)) {
                $sql .= " LEFT JOIN LonaCarros lc ON l.id = lc.lona_id 
                          LEFT JOIN Carros c ON lc.carro_id = c.id
                          WHERE l.codigo LIKE ? OR 
                          l.referencias LIKE ? OR 
                          c.marca LIKE ? OR 
                          c.modelo LIKE ?
                          GROUP BY l.id";
                $stmt = $conn->prepare($sql);
                $param = "%" . $termoBusca . "%";
                $stmt->bind_param("ssss", $param, $param, $param, $param);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql);
            }

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="produto-item">';
                    
                    // Left section - Car models
                    echo '<div class="produto-info-left">';
                    
                    // Get cars for this lona
                    $carSql = "SELECT c.* FROM Carros c 
                              JOIN LonaCarros lc ON c.id = lc.carro_id 
                              WHERE lc.lona_id = ? 
                              ORDER BY c.marca, c.modelo";
                    $carStmt = $conn->prepare($carSql);
                    $carStmt->bind_param("i", $row['id']);
                    $carStmt->execute();
                    $carResult = $carStmt->get_result();
                    
                    // Display cars grouped by brand
                    $currentBrand = "";
                    echo '<div class="aplicacoes-lista">';
                    
                    if ($carResult->num_rows > 0) {
                        while ($car = $carResult->fetch_assoc()) {
                            // Show brand as header if it's a new brand
                            if ($car['marca'] != $currentBrand) {
                                $currentBrand = $car['marca'];
                                echo '<h3>' . htmlspecialchars($currentBrand) . '</h3>';
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
                    } else {
                        echo '<p class="modelo-item">Informação não disponível</p>';
                    }
                    echo '</div>';
                    
                    // Dimensions with haste info
                    echo '<p class="dimensoes">';
                    if ($row['haste'] == 'Com') {
                        echo 'COM HASTE / ';
                    } else if ($row['haste'] == 'Sem') {
                        echo 'SEM HASTE / ';
                    }
                    echo 'Dim.: ' . htmlspecialchars($row['medidas']) . '</p>';
                    
                    // Add special note if available
                    if (isset($row['nota']) && !empty($row['nota'])) {
                        echo '<p class="nota">' . htmlspecialchars($row['nota']) . '</p>';
                    }
                    echo '</div>';
                    
                    // Center section - Product image and code
                    echo '<div class="produto-imagem">';
                    echo '<h2 class="produto-codigo">' . htmlspecialchars($row['codigo']) . '</h2>';
                    echo '<img src="' . htmlspecialchars($row['imagem']) . '" alt="' . htmlspecialchars($row['codigo']) . '">';
                    echo '</div>';
                    
                    // Right section - Conversion numbers
                    echo '<div class="produto-info-right">';
                    echo '<h3>Nº Conversão</h3>';
                    $referencias = explode(',', $row['referencias']);
                    echo '<div class="referencias-lista">';
                    foreach ($referencias as $referencia) {
                        if (!empty(trim($referencia))) {
                            echo '<p class="referencia-item">' . htmlspecialchars(trim($referencia)) . '</p>';
                        }
                    }
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<a href="detalhes_lona.php?id=' . urlencode($row['id']) . '" class="ver-mais-btn">Ver mais...</a>';
                    echo '</div>';
                    
                    echo '<div class="produto-separador"></div>';
                }
            } else {
                echo '<p class="no-results">Nenhuma lona encontrada.</p>';
            }

            $conn->close();
            ?>
        </div>
    </main>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>JP2 Freios</h3>
                <p>Especialistas em lonas e peças para sistemas de freios industriais e automotivos, fornecendo qualidade e segurança desde 1995.</p>
                <div class="footer-social">
                   
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Contato</h3>
                <div class="footer-contact-item">
                    <i class="fa fa-map-marker"></i>
                    <p>Rua Edmundo Navarro de Andrade, 2290 - Parque Industrial<br>Campinas - SP</p>
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