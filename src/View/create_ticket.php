<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Nowe zgłoszenie - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Zgłoś nowy problem</h4>
                </div>
                <div class="card-body">
                    
                    <form action="/helpdesk/create-ticket" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label">Temat zgłoszenia</label>
                            <input type="text" name="title" class="form-control" placeholder="Np. Nie działa drukarka" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategoria</label>
                            <select name="category_id" class="form-select">
                                <option value="1">Sprzęt</option>
                                <option value="2">Oprogramowanie</option>
                                <option value="3">Dostęp / Konta</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Priorytet</label>
                            <select name="priority_id" class="form-select">
                                <option value="1">Niski</option>
                                <option value="2">Średni</option>
                                <option value="3">Wysoki</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Opis problemu</label>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/helpdesk/dashboard" class="btn btn-secondary">Anuluj</a>
                            <button type="submit" class="btn btn-success">Wyślij zgłoszenie</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>