<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// Liste des documents requis (simulée pour l'instant)
$required_docs = [
    1 => "Registre des activités de traitement",
    2 => "Acte de désignation du DPO",
    3 => "AIPD (Analyses d'Impact)",
    4 => "Mentions d'information (Inscriptions)",
    5 => "Modèles de consentement Droit Image",
    6 => "Procédure d'exercice des droits",
    7 => "Contrats sous-traitants (Clauses RGPD)",
    8 => "Liste des destinataires de données",
    9 => "PSSI (Sécurité Informatique)",
    10 => "Registre des violations de données"
];

// Statuts simulés
$status_colors = [
    'pret' => '#38a169',
    'manquant' => '#e53e3e',
    'verif' => '#d69e2e'
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
    <title>Gestion des Documents - Plateforme Providence</title>
</head>

<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/admin_nav.php"); ?>

    <div class="container">
        <div class="welcome-header">
            <h2>📁 Gestion des Documents de Conformité</h2>
            <p>Centralisez vos preuves pour être prêt en cas de contrôle.</p>
        </div>

        <div class="section">
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #edf2f7; text-align: left;">
                        <th style="padding: 15px; border-bottom: 2px solid #cbd5e0;">Document</th>
                        <th style="padding: 15px; border-bottom: 2px solid #cbd5e0;">État</th>
                        <th style="padding: 15px; border-bottom: 2px solid #cbd5e0;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($required_docs as $id => $name):
                        // Simuler des statuts variés
                        $status = ($id % 3 == 0) ? 'verif' : (($id % 2 == 0) ? 'manquant' : 'pret');
                        $status_label = ($status == 'pret') ? '🟢 PRÊT' : (($status == 'manquant') ? '🔴 MANQUANT' : '🟡 À VÉRIFIER');
                        ?>
                        <tr>
                            <td style="padding: 15px; border-bottom: 1px solid #edf2f7;">
                                <?php echo $name; ?>
                            </td>
                            <td
                                style="padding: 15px; border-bottom: 1px solid #edf2f7; color: <?php echo $status_colors[$status]; ?>; font-weight: bold;">
                                <?php echo $status_label; ?>
                            </td>
                            <td style="padding: 15px; border-bottom: 1px solid #edf2f7;">
                                <button class="btn"
                                    style="padding: 5px 10px; font-size: 12px; background-color: #444;">Mettre à
                                    jour</button>
                                <button class="btn"
                                    style="padding: 5px 10px; font-size: 12px; background-color: #718096;">Lier un
                                    fichier</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <a href="index.php" class="btn">← Retour au panel</a>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>