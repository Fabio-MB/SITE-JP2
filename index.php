<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JP2 Freios Industriais e Automotivos</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .hero {
            background-color: #002147;
            padding: 50px 0;
            color: white;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .hero h1 {
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .hero p {
            font-size: 18px;
            margin-bottom: 25px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 12px 25px;
            background-color: #ff6600;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #e65c00;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .section-title h2 {
            font-size: 28px;
            color: #002147;
            margin: 0;
            padding-bottom: 10px;
        }
        
        .section-title p {
            color: #666;
            margin-top: 5px;
        }
        
        .highlighted-border {
            width: 80px;
            height: 3px;
            background-color: #ff6600;
            margin: 0 auto;
        }
        
        .featured-products {
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 33, 71, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 33, 71, 0.2);
        }
        
        .product-image {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color:rgb(250, 250, 250);
            border-bottom: 1px solid #eee;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 140px;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-code {
            font-size: 18px;
            font-weight: bold;
            color: #002147;
            margin: 0 0 5px 0;
            border-left: 3px solid #ff6600;
            padding-left: 8px;
        }
        
        .product-meta {
            margin: 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .product-link {
            display: block;
            text-align: right;
            color: #ff6600;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .product-link:hover {
            color: #e65c00;
        }
        
        .about-section {
            background-color: #f5f5f5;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .about-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .about-text {
            flex: 1;
            padding: 20px;
            min-width: 300px;
        }
        
        .about-text h2 {
            color: #002147;
            font-size: 28px;
            margin: 0 0 20px 0;
        }
        
        .about-text p {
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .about-image {
            flex: 1;
            padding: 20px;
            min-width: 300px;
            text-align: center;
            background-color: #002147;
        }
        
        .about-image img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .about-text, .about-image {
                flex: 0 0 100%;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        
        
        <div class="logo-container">
            <img src="uploads/logo.png" alt="JP2 Logo" class="logo">
        </div>
        
        <nav class="main-nav">
            <ul>
                <div class="nav-left">
                    <li><a href="index.php" class="active">INÍCIO</a></li>
                    <li><a href="catalogo.php">CATÁLOGO</a></li>
                </div>
                <div class="nav-right">
                    <li><a href="#sobre-nos">SOBRE NÓS</a></li>
                    <li><a href="#">CONTATO</a></li>
                </div>
            </ul>
        </nav>
    </header>
    
    <div class="orange-bar"></div>
    
    <main class="site-content">
        <section class="hero">
            <div class="hero-content">
                <h1>FREIOS INDUSTRIAIS E AUTOMOTIVOS</h1>
                <p>Especialistas em lonas e peças para sistemas de freios, fornecendo qualidade e segurança desde 1995.</p>
                <a href="catalogo.php" class="btn-primary">Ver Catálogo Completo</a>
            </div>
        </section>
        
        <section class="featured-products">
            <div class="section-title">
                <h2>Produtos em Destaque</h2>
                <div class="highlighted-border"></div>
                <p>Conheça algumas das nossas lonas de freio mais procuradas</p>
            </div>
            
            <div class="products-grid">
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

                // Get featured products (limit to 4)
                $sql = "SELECT l.*, COUNT(lc.carro_id) AS car_count 
                        FROM Lonas l 
                        LEFT JOIN LonaCarros lc ON l.id = lc.lona_id 
                        GROUP BY l.id 
                        ORDER BY car_count DESC, l.id DESC 
                        LIMIT 4";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="product-card">';
                        echo '<div class="product-image">';
                        echo '<h3 class="product-code">' . htmlspecialchars($row['codigo']) . '</h3>';
                        echo '<img src="' . htmlspecialchars($row['imagem']) . '" alt="' . htmlspecialchars($row['codigo']) . '">';
                        echo '</div>';
                        echo '<div class="product-info">';
                        echo '<p class="product-meta"><strong>Dimensões:</strong> ' . htmlspecialchars($row['medidas']) . '</p>';
                        
                        // Get car brands for this lona
                        $brandsSql = "SELECT DISTINCT c.marca FROM Carros c 
                                      JOIN LonaCarros lc ON c.id = lc.carro_id 
                                      WHERE lc.lona_id = ? 
                                      LIMIT 3";
                        $brandsStmt = $conn->prepare($brandsSql);
                        $brandsStmt->bind_param("i", $row['id']);
                        $brandsStmt->execute();
                        $brandsResult = $brandsStmt->get_result();
                        
                        $brands = [];
                        while ($brand = $brandsResult->fetch_assoc()) {
                            $brands[] = $brand['marca'];
                        }
                        
                        if (count($brands) > 0) {
                            echo '<p class="product-meta"><strong>Aplicações:</strong> ' . htmlspecialchars(implode(', ', $brands)) . (count($brands) >= 3 ? '...' : '') . '</p>';
                        }
                        
                        echo '<a href="detalhes_lona.php?id=' . $row['id'] . '" class="product-link">Ver detalhes &rarr;</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Nenhum produto encontrado.</p>';
                }
                
                $conn->close();
                ?>
            </div>
        </section>
        
        <section class="about-section" id="sobre-nos">
            <div class="about-content">
                <div class="about-text">
                    <h2>Sobre a JP2 Freios</h2>
                    <p>Desde 1995, a JP2 Freios Industriais e Automotivos tem oferecido soluções de alta qualidade em sistemas de freios. Somos especializados na fabricação e distribuição de lonas de freio para uma ampla variedade de veículos.</p>
                    <p>Nossa empresa tem o compromisso de fornecer produtos confiáveis que garantem a segurança e o desempenho dos veículos de nossos clientes. Todos os nossos produtos passam por rigorosos testes de qualidade para assegurar a durabilidade e eficiência.</p>
                    <a href="#sobre-nos" class="btn-primary">Saiba Mais</a>
                </div>
                <div class="about-image">
                    <img src="uploads/about-image.jpg" alt="JP2 Freios" onerror="this.src='uploads/logo.png'">
                </div>
            </div>
        </section>
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
                    <li><a href="#sobre-nos">Sobre Nós</a></li>
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