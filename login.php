<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: create_lona.php");
    exit;
}

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "JP2Catalog";

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create connection
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT password_hash FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($password_hash);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $password_hash)) {
            $_SESSION['loggedin'] = true;
            header("Location: create_lona.php");
            exit;
        } else {
            $error = 'Nome de usuário ou senha inválidos.';
        }
    } else {
        $error = 'Nome de usuário ou senha inválidos.';
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JP2</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Login - JP2</h1>
    </header>
    <main>
        <form method="POST" action="login.php">
            <label for="username">Nome de Usuário:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required><br>

            <button type="submit">Entrar</button>
        </form>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </main>
</body>
</html> 