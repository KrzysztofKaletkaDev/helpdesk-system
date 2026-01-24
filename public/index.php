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
                        authors.name as author_name,
                        operators.name as operator_name 
                FROM tickets
                LEFT JOIN statuses ON tickets.status_id = statuses.id
                LEFT JOIN priorities ON tickets.priority_id = priorities.id
                LEFT JOIN users as authors ON tickets.user_id = authors.id
                LEFT JOIN users as operators ON tickets.operator_id = operators.id";

        if ($_SESSION['role'] === 'USER') {
            $sql .= " WHERE tickets.user_id = :id";
        } 
        elseif ($_SESSION['role'] === 'OPERATOR') {
            $sql .= " WHERE tickets.operator_id = :id OR tickets.operator_id IS NULL";
        }

        $sql .= " ORDER BY tickets.created_at DESC";

        $stmt = $pdo->prepare($sql);
        
        if ($_SESSION['role'] === 'USER' || $_SESSION['role'] === 'OPERATOR') {
            $stmt->execute([':id' => $_SESSION['user_id']]);
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
    

    case '/edit-ticket':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $base_path . '/login');
            exit;
        }

        require __DIR__ . '/../config/database.php';

        $ticket_id = $_GET['id'] ?? $_POST['id'] ?? null;
        if (!$ticket_id) { die("Brak ID zgłoszenia."); }

        $stmt = $pdo->prepare("SELECT * FROM tickets Where id = :id");
        $stmt->execute([':id' => $ticket_id]);
        $ticket = $stmt->fetch();

        if (!$ticket) { die("Zgłoszenie nie istnieje."); }

        $is_admin_or_op = in_array($_SESSION['role'], ['ADMIN', 'OPERATOR']);
        if (!$is_admin_or_op && $ticket['user_id'] != $_SESSION['user_id']) {
            die("Brak uprawnień.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $category_id = $_POST['category_id'];
            $priority_id = $_POST['priority_id'];
            $operator_id = $ticket['operator_id'];

            if ($_SESSION['role'] === 'ADMIN' && isset($_POST['operator_id'])) {
                $operator_id = $_POST['operator_id'] ?: null;
            }

            $sql = "UPDATE tickets SET
                    title = :title, description = :desc,
                    category_id = :cat, priority_id = :prio,
                    operator_id = :op
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title, ':desc' => $description,
                ':cat' => $category_id, ':prio' => $priority_id,
                ':op' => $operator_id, ':id' => $ticket_id
            ]);

            header('Location: ' . $base_path . '/ticket?id=' . $ticket_id);
            exit;
        }

        $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
        $priorities = $pdo->query("SELECT * FROM priorities")->fetchAll();
        $operators = $pdo->query("SELECT users.* FROM users LEFT JOIN roles ON users.role_id = roles.id WHERE roles.name IN ('ADMIN', 'OPERATOR')")->fetchAll();

        require __DIR__ . '/../src/View/edit_ticket.php';
        break;


    case '/delete-ticket':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
            die("Brak uprawnień lub nieprawidłowa metoda.");
        }

        require __DIR__ . '/../config/database.php';
        $ticket_id = $_POST['ticket_id'];

        try {
            $pdo->beginTransaction();

            $stmt1 = $pdo->prepare("DELETE FROM comments WHERE ticket_id = :id");
            $stmt1->execute([':id' => $ticket_id]);

            $stmt2 = $pdo->prepare("DELETE FROM tickets WHERE id = :id");
            $stmt2->execute([':id' => $ticket_id]);

            $pdo->commit();
            

            header('Location: ' . $base_path . '/dashboard');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Błąd podczas usuwania zgłoszenia: " . $e->getMessage());
        }
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

        
    case '/delete-comment':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: ' . $base_path . '/dashboard');
            exit;
        }

        require __DIR__ . '/../config/database.php';
        $comment_id = $_POST['comment_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
        $stmt->execute([':id' => $comment_id]);
        $comment = $stmt->fetch();

        if (!$comment) { die("Komentarz nie istnieje."); }

        if ($_SESSION['role'] === 'ADMIN' || $_SESSION['user_id'] == $comment['user_id']) {
            $del = $pdo->prepare("DELETE FROM comments WHERE id = :id");
            $del->execute([':id' => $comment_id]);
        } else {
            die("Brak uprawnień do usunięcia tego komentarza.");
        }

        header('Location: ' . $base_path . '/ticket?id=' . $comment['ticket_id']);
        exit;
        break;

    case '/edit-comment':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $base_path . '/login'); exit; }
        
        require __DIR__ . '/../config/database.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $comment_id = $_POST['comment_id'];
            $content = $_POST['content'];

            $stmt = $pdo->prepare("SELECT user_id, ticket_id FROM comments WHERE id = :id");
            $stmt->execute([':id' => $comment_id]);
            $comment = $stmt->fetch();

            if ($_SESSION['role'] === 'ADMIN' || $_SESSION['user_id'] == $comment['user_id']) {
                $upd = $pdo->prepare("UPDATE comments SET content = :content WHERE id = :id");
                $upd->execute([':content' => $content, ':id' => $comment_id]);
                header('Location: ' . $base_path . '/ticket?id=' . $comment['ticket_id']);
                exit;
            } else {
                die("Brak uprawnień.");
            }
        }

        $comment_id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
        $stmt->execute([':id' => $comment_id]);
        $comment = $stmt->fetch();

        if (!$comment) die("Brak komentarza");
        
        if ($_SESSION['role'] !== 'ADMIN' && $_SESSION['user_id'] != $comment['user_id']) {
            die("Nie możesz edytować tego komentarza.");
        }

        require __DIR__ . '/../src/View/edit_comment.php';
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

    // =========================================================
    // PEŁNY MODUŁ SŁOWNIKÓW (CRUD: Statusy, Departamenty, Priorytety, Kategorie)
    // =========================================================

    // --- STATUSY ---
    case '/admin/statuses':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { die("Brak dostępu."); }
        require __DIR__ . '/../config/database.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO statuses (name) VALUES (:name)");
            $stmt->execute([':name' => $_POST['name']]);
            header('Location: ' . $base_path . '/admin/statuses'); exit;
        }
        $items = $pdo->query("SELECT * FROM statuses ORDER BY id ASC")->fetchAll();
        $dictionary_name = "Statusy";
        $base_url = "/helpdesk/admin/statuses";
        require __DIR__ . '/../src/View/admin_dictionary_template.php';
        break;

    case '/admin/statuses/edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { die("Brak dostępu."); }
        require __DIR__ . '/../config/database.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("UPDATE statuses SET name = :name WHERE id = :id");
            $stmt->execute([':name' => $_POST['name'], ':id' => $_POST['id']]);
            header('Location: ' . $base_path . '/admin/statuses'); exit;
        }
        $item = $pdo->prepare("SELECT * FROM statuses WHERE id = :id");
        $item->execute([':id' => $_GET['id']]);
        $item = $item->fetch();
        $dictionary_name = "Statusy";
        $update_action = "/helpdesk/admin/statuses/edit";
        $back_link = "/helpdesk/admin/statuses";
        require __DIR__ . '/../src/View/admin_dictionary_edit.php';
        break;

    case '/admin/statuses/delete':
        require __DIR__ . '/../config/database.php';
        if ($_SESSION['role'] !== 'ADMIN') die('Brak uprawnień');
        // Zabezpieczenie podstawowych statusów
        if (in_array($_POST['id'], [1, 2, 3])) die("Nie można usunąć statusów systemowych.");
        try {
            $pdo->prepare("DELETE FROM statuses WHERE id = :id")->execute([':id' => $_POST['id']]);
            header('Location: ' . $base_path . '/admin/statuses');
        } catch (PDOException $e) { die("Element używany."); }
        break;


    // --- DEPARTAMENTY ---
    case '/admin/departments':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { die("Brak dostępu."); }
        require __DIR__ . '/../config/database.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO departments (name) VALUES (:name)");
            $stmt->execute([':name' => $_POST['name']]);
            header('Location: ' . $base_path . '/admin/departments'); exit;
        }
        $items = $pdo->query("SELECT * FROM departments ORDER BY id ASC")->fetchAll();
        $dictionary_name = "Departamenty";
        $base_url = "/helpdesk/admin/departments";
        require __DIR__ . '/../src/View/admin_dictionary_template.php';
        break;

    case '/admin/departments/edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { die("Brak dostępu."); }
        require __DIR__ . '/../config/database.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("UPDATE departments SET name = :name WHERE id = :id");
            $stmt->execute([':name' => $_POST['name'], ':id' => $_POST['id']]);
            header('Location: ' . $base_path . '/admin/departments'); exit;
        }
        $item = $pdo->prepare("SELECT * FROM departments WHERE id = :id");
        $item->execute([':id' => $_GET['id']]);
        $item = $item->fetch();
        $dictionary_name = "Departamenty";
        $update_action = "/helpdesk/admin/departments/edit";
        $back_link = "/helpdesk/admin/departments";
        require __DIR__ . '/../src/View/admin_dictionary_edit.php';
        break;
        
    case '/admin/departments/delete':
        require __DIR__ . '/../config/database.php';
        if ($_SESSION['role'] !== 'ADMIN') die('Brak uprawnień');
        try {
            $pdo->prepare("DELETE FROM departments WHERE id = :id")->execute([':id' => $_POST['id']]);
            header('Location: ' . $base_path . '/admin/departments');
        } catch (PDOException $e) { die("Element używany."); }
        break;


    // --- PRIORYTETY ---
    case '/admin/priorities':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { die("Brak dostępu."); }
        require __DIR__ . '/../config/database.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO priorities (name) VALUES (:name)");
            $stmt->execute([':name' => $_POST['name']]);
            header('Location: ' . $base_path . '/admin/priorities'); exit;
        }
        $items = $pdo->query("SELECT * FROM priorities ORDER BY id ASC")->fetchAll();
        $dictionary_name = "Priorytety";
        $base_url = "/helpdesk/admin/priorities";
        require __DIR__ . '/../src/View/admin_dictionary_template.php';
        break;

    case '/admin/priorities/edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { die("Brak dostępu."); }
        require __DIR__ . '/../config/database.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("UPDATE priorities SET name = :name WHERE id = :id");
            $stmt->execute([':name' => $_POST['name'], ':id' => $_POST['id']]);
            header('Location: ' . $base_path . '/admin/priorities'); exit;
        }
        $item = $pdo->prepare("SELECT * FROM priorities WHERE id = :id");
        $item->execute([':id' => $_GET['id']]);
        $item = $item->fetch();
        $dictionary_name = "Priorytety";
        $update_action = "/helpdesk/admin/priorities/edit";
        $back_link = "/helpdesk/admin/priorities";
        require __DIR__ . '/../src/View/admin_dictionary_edit.php';
        break;

    case '/admin/priorities/delete':
        require __DIR__ . '/../config/database.php';
        if ($_SESSION['role'] !== 'ADMIN') die('Brak uprawnień');
        try {
            $pdo->prepare("DELETE FROM priorities WHERE id = :id")->execute([':id' => $_POST['id']]);
            header('Location: ' . $base_path . '/admin/priorities');
        } catch (PDOException $e) { die("Element używany."); }
        break;


    // --- KATEGORIE ---
    case '/admin/categories':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { die("Brak dostępu."); }
        require __DIR__ . '/../config/database.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute([':name' => $_POST['name']]);
            header('Location: ' . $base_path . '/admin/categories'); exit;
        }
        $items = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
        $dictionary_name = "Kategorie";
        $base_url = "/helpdesk/admin/categories";
        require __DIR__ . '/../src/View/admin_dictionary_template.php';
        break;

    case '/admin/categories/edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { die("Brak dostępu."); }
        require __DIR__ . '/../config/database.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
            $stmt->execute([':name' => $_POST['name'], ':id' => $_POST['id']]);
            header('Location: ' . $base_path . '/admin/categories'); exit;
        }
        $item = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $item->execute([':id' => $_GET['id']]);
        $item = $item->fetch();
        $dictionary_name = "Kategorie";
        $update_action = "/helpdesk/admin/categories/edit";
        $back_link = "/helpdesk/admin/categories";
        require __DIR__ . '/../src/View/admin_dictionary_edit.php';
        break;

    case '/admin/categories/delete':
        require __DIR__ . '/../config/database.php';
        if ($_SESSION['role'] !== 'ADMIN') die('Brak uprawnień');
        try {
            $pdo->prepare("DELETE FROM categories WHERE id = :id")->execute([':id' => $_POST['id']]);
            header('Location: ' . $base_path . '/admin/categories');
        } catch (PDOException $e) { die("Element używany."); }
        break;


    case '/change-password':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $base_path . '/login');
            exit;
        }

        require __DIR__ . '/../config/database.php';
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $user_id = $_SESSION['user_id'];

            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($current_password, $user['password_hash'])) {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

                $update = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
                $update->execute([
                    ':hash' => $new_hash,
                    ':id' => $user_id
                ]);
                $success = "Hasło zostało zmienione pomyślnie!";

            } else {
                $error = "Podane aktualne hasło jest nieprawidołowe.";
            }
        }

        require __DIR__ . '/../src/View/change_password.php';
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