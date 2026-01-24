<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="/helpdesk/dashboard">Helpdesk IT</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/helpdesk/dashboard">Pulpit</a>
                </li>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        Administracja
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/helpdesk/admin/users">Użytkownicy</a></li>
                        <li><hr class="dropdown-divider"></li>
                       <li><h6 class="dropdown-header">Ustawienia</h6></li>
                        <li><a class="dropdown-item" href="/helpdesk/admin/categories">Kategorie</a></li>
                        <li><a class="dropdown-item" href="/helpdesk/admin/statuses">Statusy</a></li>
                        <li><a class="dropdown-item" href="/helpdesk/admin/priorities">Priorytety</a></li>
                        <li><a class="dropdown-item" href="/helpdesk/admin/departments">Departamenty</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex text-white align-items-center">
                <span class="me-3">
                    <?php echo $_SESSION['name'] ?? 'Gość'; ?> 
                    (<?php echo $_SESSION['role'] ?? '-'; ?>)
                </span>

                <a href="/helpdesk/change-password" class="btn btn-outline-light btn-sm me-2">Hasło</a>

                <a href="/helpdesk/logout" class="btn btn-outline-light btn-sm">Wyloguj</a>
            </div>
        </div>
    </div>
</nav>