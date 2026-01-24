<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Słownik: <?php echo htmlspecialchars($dictionary_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require __DIR__ . '/header.php'; ?>

    <div class="container">
        <h2 class="mb-4">Zarządzanie: <?php echo htmlspecialchars($dictionary_name); ?></h2>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">Lista elementów</div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nazwa</th>
                                    <th>Akcja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                        <a href="<?php echo $base_url; ?>/edit?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary me-1">Edytuj</a>
                                        
                                        <form action="<?php echo $base_url; ?>/delete" method="POST" 
                                            onsubmit="return confirm('Czy na pewno chcesz usunąć?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Usuń</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow bg-white">
                    <div class="card-header bg-success text-white">Dodaj nowy</div>
                    <div class="card-body">
                        <form action="<?php echo $base_url; ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nazwa</label>
                                <input type="text" name="name" class="form-control" required placeholder="np. Nowa wartość">
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