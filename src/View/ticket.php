<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zgłoszenie #<?php echo $ticket['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <a href="/helpdesk/dashboard" class="btn btn-outline-secondary mb-3">&larr; Wróć do listy</a>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        #<?php echo $ticket['id']; ?>: <?php echo htmlspecialchars($ticket['title']); ?>
                    </h4>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($ticket['status_name']); ?></span>
                </div>
                <div class="card-body">
                    <h6 class="text-muted">Opis problemu:</h6>
                    <p class="card-text p-3 bg-light border rounded">
                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                    </p>
                </div>
                <h5 class="mt-4 mb-3">Dyskusja</h5>

            <?php foreach ($comments as $comment): ?>
                <div class="card mb-3 <?php echo ($comment['author_role'] === 'ADMIN') ? 'border-primary' : ''; ?>">
                    <div class="card-header d-flex justify-content-between text-muted" style="font-size: 0.85em;">
                        <div>
                            <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                            (<?php echo $comment['author_role']; ?>)
                            <span class="ms-2"><?php echo $comment['created_at']; ?></span>
                        </div>
                        
                        <?php if ($_SESSION['role'] === 'ADMIN' || $_SESSION['user_id'] == $comment['user_id']): ?>
                        <div>
                            <a href="/helpdesk/edit-comment?id=<?php echo $comment['id']; ?>" class="text-decoration-none text-primary me-2">Edytuj</a>
                            
                            <form action="/helpdesk/delete-comment" method="POST" style="display:inline;" onsubmit="return confirm('Usunąć komentarz?');">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" class="btn btn-link p-0 text-danger text-decoration-none" style="font-size: 1em;">Usuń</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body py-2">
                        <p class="card-text mb-0">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($comments)): ?>
                <p class="text-muted fst-italic">Brak komentarzy. Rozpocznij dyskusję.</p>
            <?php endif; ?>

            <div class="card mt-4 bg-light">
                <div class="card-body">
                    <form action="/helpdesk/add-comment" method="POST">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Dodaj odpowiedź</label>
                            <textarea name="content" class="form-control" rows="3" required placeholder="Wpisz treść..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary float-end">Wyślij odpowiedź</button>
                    </form>
                </div>
            </div>
                <div class="card-footer text-muted font-monospace" style="font-size: 0.9em;">
                    Zgłoszono: <?php echo $ticket['created_at']; ?> | 
                    Autor: <?php echo htmlspecialchars($ticket['author_name']); ?> (<?php echo htmlspecialchars($ticket['author_email']); ?>)
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">Informacje</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Priorytet:</span>
                        <strong><?php echo htmlspecialchars($ticket['priority_name']); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Kategoria:</span>
                        <strong><?php echo htmlspecialchars($ticket['category_name']); ?></strong>
                    </li>
                </ul>
                
                <?php if ($_SESSION['role'] !== 'USER'): ?>
                <div class="card-body border-top">
                    <h6>Zarządzaj zgłoszeniem</h6>
                    <form action="/helpdesk/update-ticket-status" method="POST">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                        <select name="status_id" class="form-select mb-2">
                            <?php foreach ($all_statuses as $status): ?>
                                <option 
                                    value="<?php echo $status['id']; ?>"
                                    <?php echo ($status['id'] == $ticket['status_id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($status['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-warning w-100">Zmień status</button>
                    </form>
                </div>
                <div class="card-body border-bottom">
                    <a href="/helpdesk/edit-ticket?id=<?php echo $ticket['id']; ?>" class="btn btn-outline-primary w-100">
                        ✏️ Edytuj / Przypisz
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>