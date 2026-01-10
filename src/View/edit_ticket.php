<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja zgłoszenia #<?php echo $ticket['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    Edycja zgłoszenia #<?php echo $ticket['id']; ?>
                </div>
                <div class="card-body">
                    
                    <form action="/helpdesk/edit-ticket" method="POST">
                        <input type="hidden" name="id" value="<?php echo $ticket['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Temat</label>
                            <input type="text" name="title" class="form-control" 
                                   value="<?php echo htmlspecialchars($ticket['title']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategoria</label>
                                <select name="category_id" class="form-select">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo ($cat['id'] == $ticket['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priorytet</label>
                                <select name="priority_id" class="form-select">
                                    <?php foreach ($priorities as $prio): ?>
                                        <option value="<?php echo $prio['id']; ?>"
                                            <?php echo ($prio['id'] == $ticket['priority_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prio['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if ($_SESSION['role'] === 'ADMIN'): ?>
                        <div class="mb-3 p-3 bg-light border rounded border-warning">
                            <label class="form-label fw-bold text-danger">Przypisz operatora</label>
                            <select name="operator_id" class="form-select">
                                <option value="">-- Brak (Do wzięcia) --</option>
                                <?php foreach ($operators as $op): ?>
                                    <option value="<?php echo $op['id']; ?>"
                                        <?php echo ($op['id'] == $ticket['operator_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($op['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Opis problemu</label>
                            <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/helpdesk/ticket?id=<?php echo $ticket['id']; ?>" class="btn btn-secondary">Anuluj</a>
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