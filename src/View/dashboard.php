<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Pulpit - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Helpdesk IT</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Zalogowany: <strong><?php echo $_SESSION['name']; ?></strong> (<?php echo $_SESSION['role']; ?>)
                </span>
                <a href="/helpdesk/logout" class="btn btn-outline-light btn-sm">Wyloguj</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center p-5">
                        <h1 class="display-4">Witaj w systemie!</h1>
                        <p class="lead">To jest Twój pulpit główny.</p>
                        <hr class="my-4">
                        
                        <?php if ($_SESSION['role'] === 'ADMIN'): ?>
                            <div class="alert alert-info">
                                Masz uprawnienia Administratora. Możesz zarządzać użytkownikami.
                            </div>
                        <?php endif; ?>

                        <button class="btn btn-primary btn-lg mt-3">Zgłoś nowy problem</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>