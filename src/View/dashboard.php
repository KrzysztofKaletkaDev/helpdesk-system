<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Pulpit - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require __DIR__ . '/header.php'; ?>

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

                        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Lista zgłoszeń</h3>
            <a href="/helpdesk/create-ticket" class="btn btn-success">+ Nowe zgłoszenie</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Temat</th>
                        <th>Status</th>
                        <th>Priorytet</th>
                        <th>Zgłaszający</th>
                        <th>Data</th>
                        <th>Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Brak zgłoszeń w systemie.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo $ticket['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ticket['title']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($ticket['status_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['priority_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['author_name']); ?></td>
                                <td><?php echo $ticket['created_at']; ?></td>
                                <td>
                                    <a href="/helpdesk/ticket?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">Podgląd</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>  
</body>
</html>