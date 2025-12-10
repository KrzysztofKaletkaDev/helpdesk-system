<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Nowy Pracownik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    Dodaj nowego pracownika
                </div>
                <div class="card-body">
                    
                    <form action="/helpdesk/admin/users/store" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label">Imię i Nazwisko</label>
                            <input type="text" name="name" class="form-control" required placeholder="np. Anna Nowak">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="anna@firma.pl">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hasło startowe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rola</label>
                            <select name="role_id" class="form-select">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>">
                                        <?php echo htmlspecialchars($role['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Departament / Dział</label>
                            <select name="department_id" class="form-select">
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>">
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="/helpdesk/admin/users" class="btn btn-secondary">Anuluj</a>
                            <button type="submit" class="btn btn-success">Utwórz konto</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>