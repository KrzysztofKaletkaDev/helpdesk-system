<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja komentarza</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">Edytuj komentarz</div>
                    <div class="card-body">
                        <form action="/helpdesk/edit-comment" method="POST">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <div class="mb-3">
                                <textarea name="content" class="form-control" rows="5" required><?php echo htmlspecialchars($comment['content']); ?></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="javascript:history.back()" class="btn btn-secondary">Anuluj</a>
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