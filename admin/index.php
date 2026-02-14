<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// Statistiques admin
$nbUsers = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$nbQuestionnaires = $pdo->query("SELECT COUNT(*) FROM questionnaires WHERE statut='complete'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
    <title>Administration - Plateforme RGPD</title>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="welcome-header">
            <h2>⚙️ Panel d'administration</h2>
            <p>Gestion globale de la conformité et des utilisateurs</p>
        </div>

        <div class="stats-grid"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="section" style="margin-bottom: 0; text-align: center;">
                <h3 style="border:none;">Utilisateurs</h3>
                <p style="font-size: 32px; font-weight: 800; color: #0059b2; margin: 10px 0;"><?php echo $nbUsers; ?>
                </p>
            </div>
            <div class="section" style="margin-bottom: 0; text-align: center;">
                <h3 style="border:none;">Bilans terminés</h3>
                <p style="font-size: 32px; font-weight: 800; color: #48bb78; margin: 10px 0;">
                    <?php echo $nbQuestionnaires; ?>
                </p>
            </div>
        </div>

        <div class="section">
            <h3>🚀 Actions rapides</h3>
            <div class="dashboard-grid">
                <a href="users.php" class="btn" style="text-align: center;">👥 Gérer les utilisateurs</a>
                <a href="registre.php" class="btn" style="text-align: center;">📁 Consulter le registre</a>
                <a href="documentation.php" class="btn" style="text-align: center; background-color: #4a5568;">📚
                    Documentation RGPD</a>
                <a href="logs.php" class="btn" style="text-align: center; background-color: #718096;">📜 Journaux
                    d'actions</a>
                <a href="export_registre.php" class="btn" style="text-align: center; background-color: #38a169;">📥
                    Export Excel (CSV)</a>
            </div>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>