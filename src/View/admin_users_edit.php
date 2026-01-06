<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja Użytkownika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    Edycja pracownika: <strong><?php echo htmlspecialchars($user_to_edit['name']); ?></strong>
                </div>
                <div class="card-body">
                    
                    <form action="/helpdesk/admin/users/update" method="POST">
                        <input type="hidden" name="id" value="<?php echo $user_to_edit['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Imię i Nazwisko</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user_to_edit['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adres Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user_to_edit['email']); ?>" required>
                        </div>

                        <div class="mb-3 border p-3 rounded bg-white">
                            <label class="form-label text-danger fw-bold">Reset hasła</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Wpisz nowe hasło (zostaw puste, aby nie zmieniać)">
                            <div class="form-text">Wypełnij to pole TYLKO jeśli chcesz zmienić hasło pracownikowi.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stanowisko / Rola</label>
                            <select name="role_id" class="form-select">
                                <?php foreach ($roles as $role): ?>
                                    <option 
                                        value="<?php echo $role['id']; ?>"
                                        <?php echo ($role['id'] == $user_to_edit['role_id']) ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($role['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Departament / Dział</label>
                            <select name="department_id" class="form-select">
                                <?php foreach ($departments as $dept): ?>
                                    <option 
                                        value="<?php echo $dept['id']; ?>"
                                        <?php 
                                            // Sprawdzamy, czy to jest obecny dział użytkownika
                                            echo ($dept['id'] == $user_to_edit['department_id']) ? 'selected' : ''; 
                                        ?>
                                    >
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="/helpdesk/admin/users" class="btn btn-secondary">Anuluj</a>
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