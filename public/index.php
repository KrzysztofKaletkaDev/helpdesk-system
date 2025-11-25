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
        require __DIR__ . '/../config/database.php';

        $sql = "SELECT tickets.*,
                        statuses.name as status_name,
                        priorities.name as priority_name,
                        users.name as author_name
                FROM tickets
                LEFT JOIN statuses ON tickets.status_id = statuses.id
                LEFT JOIN priorities ON tickets.priority_id = priorities.id
                LEFT JOIN users ON tickets.user_id = users.id";
        if ($_SESSION['role'] === 'USER') {
            $sql .= " WHERE tickets.user_id = :user_id";
        }
        $sql .= " ORDER BY tickets.created_at DESC";
        $stmt = $pdo->prepare($sql);
        if ($_SESSION['role'] === 'USER') {
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
        } else {
            $stmt->execute();
        }

        $tickets = $stmt->fetchAll();
        require __DIR__ . '/../src/View/dashboard.php';
        break;
    case '/create-ticket':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $base_path . '/login');
            exit;
        }
        // Obsługa wysyłania formularza (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../config/database.php';

            $title = $_POST['title'];
            $description = $_POST['description'];
            $category_id = $_POST['category_id'];
            $priority_id = $_POST['priority_id'];
            $user_id = $_SESSION['user_id'];

            $status_id = 1;
            // INSERT
            $sql = "INSERT INTO tickets (title, description, user_id, category_id, priority_id, status_id, created_at)
            VALUES (:title, :desc, :user, :cat, :prio, :status, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':desc' => $description,
                ':user' => $user_id,
                ':cat' => $category_id,
                ':prio' => $priority_id,
                ':status' => $status_id
            ]);
            header('Location: ' . $base_path . '/dashboard');
            exit;
        }
        require __DIR__ . '/../src/View/create_ticket.php';
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