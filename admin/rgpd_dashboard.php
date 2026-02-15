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
    <title>Administration - Plateforme Providence</title>
</head>

<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/admin_nav.php"); ?>

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

        <div class="section" style="background: #f0f7ff; border-left: 4px solid #3182ce;">
            <h3>📋 Checklist de Conformité (Prêt pour un Audit CNIL)</h3>
            <p style="margin-bottom: 15px;">Voici les 10 documents clés que vous devez être en mesure de présenter
                immédiatement :</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px;">
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>1. Registre des activités de traitement</span>
                    <span style="color: #38a169; font-weight: bold;">🟢 PRÊT</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>2. Acte de désignation du DPO</span>
                    <span style="color: #e53e3e; font-weight: bold;">🔴 MANQUANT</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>3. AIPD (Analyses d'Impact)</span>
                    <span style="color: #d69e2e; font-weight: bold;">🟡 À VÉRIFIER</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>4. Mentions d'information (Inscriptions)</span>
                    <span style="color: #38a169; font-weight: bold;">🟢 PRÊT</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>5. Modèles de consentement Droit Image</span>
                    <span style="color: #38a169; font-weight: bold;">🟢 PRÊT</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>6. Procédure d'exercice des droits</span>
                    <span style="color: #e53e3e; font-weight: bold;">🔴 MANQUANT</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>7. Contrats sous-traitants (Clauses RGPD)</span>
                    <span style="color: #d69e2e; font-weight: bold;">🟡 À VÉRIFIER</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>8. Liste des destinataires de données</span>
                    <span style="color: #38a169; font-weight: bold;">🟢 PRÊT</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>9. PSSI (Sécurité Informatique)</span>
                    <span style="color: #e53e3e; font-weight: bold;">🔴 MANQUANT</span>
                </div>
                <div
                    style="padding: 10px; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span>10. Registre des violations de données</span>
                    <span style="color: #38a169; font-weight: bold;">🟢 PRÊT</span>
                </div>
            </div>
            <p style="margin-top: 15px; font-size: 0.9em; text-align: right;"><a href="documents.php"
                    style="color: #3182ce; font-weight: bold;">Gérer vos preuves et statuts →</a></p>
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
                <a href="legal_watch.php?context=rgpd" class="btn"
                    style="text-align: center; background: linear-gradient(135deg, #667eea, #764ba2); grid-column: span 2;">✨
                    Veille Juridique Assistée par IA</a>
            </div>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>