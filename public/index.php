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

            
            $sql = "SELECT users.*, roles.name as role_name
                    FROM users
                    LEFT JOIN roles ON users.role_id = roles.id
                    WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role_name'];
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

            require __DIR__ . '/../config/database.php';
            // Pobieranie kategorii i priorytetów
            $stmt_cats = $pdo->query("SELECT * FROM categories");
            $categories = $stmt_cats->fetchAll();

            $stmt_prio = $pdo->query("SELECT * FROM priorities");
            $priorities = $stmt_prio->fetchAll();

        require __DIR__ . '/../src/View/create_ticket.php';
        break;


    case '/ticket':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $base_path . '/login');
            exit;
        }
        $ticket_id = $_GET['id'] ?? null;
        if (!$ticket_id) {
            header('Location: ' . $base_path . '/dashboard');
            exit;
        }

        require __DIR__ . '/../config/database.php';
        $sql = "SELECT tickets.*,
                       statuses.name as status_name,
                       priorities.name as priority_name,
                       categories.name as category_name,
                       users.name as author_name,
                       users.email as author_email
                FROM tickets
                LEFT JOIN statuses ON tickets.status_id = statuses.id
                LEFT JOIN priorities ON tickets.priority_id = priorities.id
                LEFT JOIN categories ON tickets.category_id = categories.id
                LEFT JOIN users ON tickets.user_id = users.id
                WHERE tickets.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $ticket_id]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            die("Zgłoszenie nie istnieje.");
        }

         if ($_SESSION['role'] === 'USER' && $ticket['user_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            die("<h1>Brak dostępu</h1><p>To nie jest Twoje zgłoszenie.</p>");
        }
        $stmt_statuses = $pdo->query("SELECT * FROM statuses");
        $all_statuses = $stmt_statuses->fetchAll();
        // Pobranie komentarzy

        $sql_comments = "SELECT comments.*, users.name as author_name, roles.name as author_role
                         FROM comments
                         LEFT JOIN users ON comments.user_id = users.id
                         LEFT JOIN roles ON users.role_id = roles.id
                         WHERE ticket_id = :id
                         ORDER BY comments.created_at ASC";

        $stmt_comments = $pdo->prepare($sql_comments);
        $stmt_comments->execute([':id' => $ticket_id]);
        $comments = $stmt_comments->fetchAll();

        require __DIR__ . '/../src/View/ticket.php';
        break;


    case '/update-ticket-status';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $base_path . '/dashboard');
            exit;
        }

        $ticket_id = $_POST['ticket_id'];
        $new_status_id = $_POST['status_id'];
        // Sprawdzamy, czy użytkownik to ADMIN lub OPERATOR.
        // Zwykły USER nie ma prawa tu wejść, nawet jeśli spreparuje żądanie POST.
        if (!isset($_SESSION['role']) || $_SESSION['role'] === 'USER') {
            die("Brak uprawnień do zmiany statusu.");
        }
        require __DIR__ . '/../config/database.php';
        // SQL Update
        $sql = "UPDATE tickets SET status_id = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':status' => $new_status_id,
            ':id' => $ticket_id
        ]);
        header('Location: ' . $base_path . '/ticket?id=' . $ticket_id);
        exit;
        break;

    case '/add-comment':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $base_path . '/dashboard');
            exit;
        }
        if (!isset($_SESSION['user_id'])) {
            die("Brak dostępu.");
        }
        require __DIR__ . '/../config/database.php';

        $ticket_id = $_POST['ticket_id'];
        $content = $_POST['content'];
        $user_id = $_SESSION['user_id'];
        // Zapisywanie komentarza
        $sql = "INSERT INTO comments (content, ticket_id, user_id, created_at)
                VALUES (:content, :ticket_id, :user_id, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':content' => $content,
            ':ticket_id' => $ticket_id,
            ':user_id' => $user_id
        ]);
        header('Location: ' . $base_path . '/ticket?id=' . $ticket_id);
        exit;
        break;

    
    case '/admin/users':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
            die("Brak dostępu. Tylko dla administratora.");
        }

        require __DIR__ . '/../config/database.php';

        $sql = "SELECT users.*, roles.name as role_name, departments.name as dept_name
                FROM users
                LEFT JOIN roles ON users.role_id = roles.id
                LEFT JOIN departments ON users.department_id = departments.id
                ORDER BY users.id ASC";
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll();
        require __DIR__ . '/../src/View/admin_users.php';
        break;


    case '/admin/users/edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
            die("Brak dostępu.");
        }

        $user_id = $_GET['id'] ?? null;
        if (!$user_id) {
            header('Location: ' . $base_path . '/admin/users');
            exit;
        }

        require __DIR__ . '/../config/database.php';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user_to_edit = $stmt->fetch();

        if (!$user_to_edit) {
            die("Użytkownik nie istnieje.");
        }

        $stmt_roles = $pdo->query("SELECT * FROM roles");
        $roles = $stmt_roles->fetchAll();

        $stmt_dept = $pdo->query("SELECT * FROM departments");
        $departments = $stmt_dept->fetchAll();

        require __DIR__ . '/../src/View/admin_users_edit.php';
        break;


    case '/admin/users/create':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
            die("Brak dostępu.");
        }

        require __DIR__ . '/../config/database.php';

        $stmt = $pdo->query("SELECT * FROM roles");
        $roles = $stmt->fetchAll();

        $stmt_dept = $pdo->query("SELECT * FROM departments");
        $departments = $stmt_dept->fetchAll();

        require __DIR__ . '/../src/View/admin_users_create.php';
        break;

    
    case '/admin/users/store':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'ADMIN') {
            header('Location: ' . $base_path . '/dashboard');
            exit;
        }
        require __DIR__ . '/../config/database.php';
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role_id = $_POST['role_id'];
        $department_id = $_POST['department_id'];
        $raw_password = $_POST['password'];

        $password_hash = password_hash($raw_password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password_hash, role_id, department_id) 
                VALUES (:name, :email, :hash, :role, :dept)";
        
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':hash' => $password_hash,
                ':role' => $role_id,
                ':dept' => $department_id
            ]);
        } catch (PDOException $e) {
            die("Błąd: Taki email już istnieje lub inny problem z bazą.");
        }

        header('Location: ' . $base_path . '/admin/users');
        exit;
        break;


    case '/admin/users/update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'ADMIN') {
            header('Location: ' . $base_path . '/dashboard');
            exit;
        }

        require __DIR__ . '/../config/database.php';

        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role_id = $_POST['role_id'];
        $department_id = $_POST['department_id'];
        $new_password = $_POST['new_password'];

        try {
            if (!empty($new_password)) {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $sql = "UPDATE users 
                        SET name = :name, email = :email, role_id = :role, department_id = :dept, password_hash = :hash 
                        WHERE id = :id";
                
                $params = [
                    ':name' => $name,
                    ':email' => $email,
                    ':role' => $role_id,
                    ':dept' => $department_id,
                    ':hash' => $password_hash,
                    ':id' => $id
                ];
            } else {
                $sql = "UPDATE users 
                        SET name = :name, email = :email, role_id = :role, department_id = :dept 
                        WHERE id = :id";
                
                $params = [
                    ':name' => $name,
                    ':email' => $email,
                    ':role' => $role_id,
                    ':dept' => $department_id,
                    ':id' => $id
                ];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            header('Location: ' . $base_path . '/admin/users');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                echo '<div style="max-width:600px; margin:50px auto; padding:20px; border:1px solid #dc3545; background:#f8d7da; color:#721c24; text-align:center; font-family:sans-serif; border-radius:5px;">';
                echo '<h3>⛔ Błąd edycji!</h3>';
                echo '<p>Adres email <strong>' . htmlspecialchars($email) . '</strong> jest już zajęty.</p>';
                echo '<button onclick="history.back()" style="padding:10px 20px; background:#dc3545; color:white; border:none; cursor:pointer; border-radius:5px;">Wróć i popraw</button>';
                echo '</div>';
                exit;
            } else {
                die("Błąd bazy danych: " . $e->getMessage());
            }
        }
        break;

    case '/admin/categories':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
            die("Brak dostępu.");
        }

        require __DIR__ . '/../config/database.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            if (!empty($name)) {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
                $stmt->execute([':name' => $name]);
                header('Location: ' . $base_path . '/admin/categories');
                exit;
            }
        }

        $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
        $categories = $stmt->fetchAll();

        require __DIR__ . '/../src/View/admin_categories.php';
        break;


    case '/admin/categories/delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'ADMIN') {
            header('Location: ' . $base_path . '/dashboard');
            exit;
        }

        require __DIR__ . '/../config/database.php';
        $id = $_POST['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: ' . $base_path . '/admin/categories');
            exit;
        } catch (PDOException $e) {
        // Kod 23000 = Naruszenie więzów integralności (kategoria jest używana)
            if ($e->getCode() == '23000') {
                die("<div class='container mt-5'><div class='alert alert-danger text-center'>
                            <h4>Nie można usunąć tej kategorii!</h4>
                            <p>Jest ona przypisana do istniejących zgłoszeń.</p>
                            <a href='$base_path/admin/categories' class='btn btn-secondary'>Wróć</a>
                        </div></div>");
            } else {
                die("Błąd bazy danych: " . $e->getMessage());
            }
        }
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