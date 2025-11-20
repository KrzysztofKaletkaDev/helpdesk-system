<?php
session_start();

$base_path = '/helpdesk';
$request = $_SERVER['REQUEST_URI'];

$path = str_replace($base_path, '', $request);
$path = strtok($path, '?');

switch ($path) {
    case '/':
    case '':
        header('Location: ' . $base_path . '/login');
        exit;
    case '/login':
        require __DIR__ . '/../src/View/login.php';
        break;
    default:
        http_response_code(404);
        echo "<div style='text-align:center; margin-top: 50px;'>";
        echo "<h1>404 - Nie znaleziono</h1>";
        echo "<p>Prubujesz wejsc na adres: <strong>$path</strong></p>";
        echo "<a href='$base_path/login'>Wroć do logowania</a>";
        echo "</div>";
        break;
}