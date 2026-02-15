<?php
include("../includes/config.php");
include("../includes/auth.php");
include("../includes/ai_watch.php");

header('Content-Type: application/json');

if ($currentUser['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

try {
    $result = updateLegalWatch();
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>