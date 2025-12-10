<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zarządzanie Użytkownikami</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pracownicy</h2>
        <div>
            <a href="/helpdesk/dashboard" class="btn btn-outline-secondary me-2">Wróć do Pulpitu</a>
            <a href="/helpdesk/admin/users/create" class="btn btn-success">+ Dodaj pracownika</a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Imię i Nazwisko</th>
                        <th>Email</th>
                        <th>Rola</th>
                        <th>Dział</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                                $badgeColor = match($user['role_name']) {
                                    'ADMIN' => 'bg-danger',
                                    'OPERATOR' => 'bg-warning text-dark',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <span class="badge <?php echo $badgeColor; ?>">
                                <?php echo htmlspecialchars($user['role_name']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['dept_name']); ?></td>
                        <td>
                            <a href="/helpdesk/admin/users/edit?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edytuj</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>