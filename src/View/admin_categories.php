<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Słowniki - Kategorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require __DIR__ . '/header.php'; ?>

    <div class="container">
        <h2 class="mb-4">Zarządzanie Kategoriami</h2>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">Istniejące kategorie</div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nazwa kategorii</th>
                                    </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?php echo $cat['id']; ?></td>
                                    <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow bg-white">
                    <div class="card-header bg-success text-white">Dodaj nową</div>
                    <div class="card-body">
                        <form action="/helpdesk/admin/categories" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nazwa</label>
                                <input type="text" name="name" class="form-control" placeholder="np. Awaria Sieci" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Zapisz</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>