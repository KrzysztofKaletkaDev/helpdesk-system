<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja: <?php echo htmlspecialchars($dictionary_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        Edycja elementu: <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                        <br><small class="text-muted">Słownik: <?php echo htmlspecialchars($dictionary_name); ?></small>
                    </div>
                    <div class="card-body">
                        
                        <form action="<?php echo $update_action; ?>" method="POST">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Nazwa</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($item['name']); ?>" required>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo $back_link; ?>" class="btn btn-secondary">Anuluj</a>
                                <button type="submit" class="btn btn-success">Zapisz zmiany</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>