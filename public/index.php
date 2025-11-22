<?php
session_start();

$base_path = '/helpdesk';
$request = $_SERVER['REQUEST_URI'];

$path = str_replace($base_path, '', $request);
$path = strtok($path, '?');

switch ($path) {
    case '/':
    case '':
        header('Location: ' . $base_path . '/login');
        exit;
    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../config/database.php';
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                header('Location: ' . $base_path . '/dashboard');
                exit;
            } else {
                $error = "Błędny email lub haslo!";
            }
        }
        require __DIR__ . '/../src/View/login.php';
        break;
    case '/dashboard':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $base_path . '/login');
            exit;
        }
        require __DIR__ . '/../src/View/dashboard.php';
        break;
    case '/logout':
        session_destroy();
        header('Location: ' . $base_path . '/login');
        exit;
    default:
        http_response_code(404);
        echo "<div style='text-align:center; margin-top: 50px;'>";
        echo "<h1>404 - Nie znaleziono</h1>";
        echo "<p>Prubujesz wejsc na adres: <strong>$path</strong></p>";
        echo "<a href='$base_path/login'>Wroć do logowania</a>";
        echo "</div>";
        break;
}