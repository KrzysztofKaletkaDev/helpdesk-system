<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zmiana hasła</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require __DIR__ . '/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        Zmiana hasła
                    </div>
                    <div class="card-body">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <br>
                                <a href="/helpdesk/dashboard" class="alert-link">Wróć do pulpitu</a>
                            </div>
                        <?php endif; ?>

                        <?php if (!isset($success)): ?>
                        <form action="/helpdesk/change-password" method="POST">
                            
                            <div class="mb-3">
                                <label class="form-label">Aktualne hasło</label>
                                <input type="password" name="current_password" class="form-control" required>
                                <div class="form-text">Dla bezpieczeństwa musisz podać swoje obecne hasło.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nowe hasło</label>
                                <input type="password" name="new_password" class="form-control" required minlength="5">
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="/helpdesk/dashboard" class="btn btn-secondary">Anuluj</a>
                                <button type="submit" class="btn btn-primary">Zmień hasło</button>
                            </div>
                        </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>