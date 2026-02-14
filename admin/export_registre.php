<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    exit("Accès refusé");
}

// Nom du fichier
$filename = "registre_traitements_" . date('Ymd_His') . ".csv";

// Headers pour forcer le téléchargement du fichier en CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Création du pointeur de fichier pour PHP
$output = fopen('php://output', 'w');

// UTF-8 BOM pour Excel (essentiel pour les accents)
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// fputcsv($output, [ ... ], ';');
fputcsv($output, [
    'N°',
    'Traitement',
    'Saisi par',
    'Finalité',
    'Responsable',
    'Base Légale',
    'Catégories données',
    'Conservation',
    'Maj'
], ';');

// Récupération des données avec le nom de l'utilisateur
$traitements = $pdo->query("SELECT r.*, u.nom FROM registre_traitements r 
                           LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur 
                           ORDER BY r.dernier_maj DESC")->fetchAll();

foreach ($traitements as $t) {
    fputcsv($output, [
        $t['id_traitement'],
        $t['nom_traitement'],
        $t['nom'] ?? 'Système',
        $t['finalite'],
        $t['responsable_traitement'],
        $t['base_legale'],
        $t['categories_donnes'],
        $t['duree_conservation'],
        $t['dernier_maj']
    ], ';');
}

fclose($output);
exit;
?>