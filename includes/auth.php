<?php
if (!isset($_SESSION['user'])) {
    // Redirection intelligente : on cherche dashboard.php pour savoir si on est à la racine
    $prefix = file_exists("dashboard.php") ? "" : "../";
    header("Location: " . $prefix . "index.php");
    exit;
}

$currentUser = $_SESSION['user'];
?>