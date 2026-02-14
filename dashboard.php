<?php
include("includes/config.php");
include("includes/auth.php");

// Statistiques utilisateur
$stmt = $pdo->prepare("SELECT COUNT(*) as nb FROM questionnaires WHERE id_utilisateur=? AND statut='complete'");
$stmt->execute([$currentUser['id_utilisateur']]);
$stats = $stmt->fetch();

// Charger les conseils IA (les plus récents de la table fiches)
$stmt = $pdo->prepare("SELECT type_fiche, observations FROM fiches WHERE id_utilisateur = ? AND type_fiche IN ('bonnes_pratiques', 'points_vigilance') ORDER BY date_generation DESC LIMIT 2");
$stmt->execute([$currentUser['id_utilisateur']]);
$conseils = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Charger les procédures PPMS partagées
$ppmsProcedures = $pdo->query("SELECT titre, chemin_fichier, description FROM ppms_procedures ORDER BY date_publication DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css?v=1.2">
    <title>Tableau de bord</title>
    <style>
        .dashboard-col {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <div class="welcome-header">
            <h2>👋 Bienvenue, <?php echo htmlspecialchars($currentUser['nom']); ?></h2>
            <p><strong>Fonction :</strong> <?php echo htmlspecialchars($currentUser['fonction']); ?></p>
        </div>

        <div class="dashboard-grid">
            <!-- Colonne GAUCHE : RGPD & Documents -->
            <div class="dashboard-col">
                <div class="section" style="margin-bottom: 0;">
                    <h3>📊 Mon bilan RGPD</h3>
                    <p>Questionnaires complétés : <strong><?php echo $stats['nb']; ?></strong></p>
                    <div class="actions" style="margin-top: 15px;">
                        <a href="questionnaire.php" class="btn" style="width: 100%; box-sizing: border-box;">📝 Remplir
                            mon questionnaire</a>
                    </div>
                </div>

                <div class="section" style="margin-bottom: 0;">
                    <h3>📄 Mon Espace RGPD</h3>
                    <ul style="padding-left: 20px;">
                        <li><a href="rgpd_view.php">Accéder à mes outils & bilans</a></li>
                        <li style="margin-top:10px;"><a href="rgpd_view.php?action=pdf" class="btn"
                                 style="padding: 5px 15px; font-size: 13px;">⚡ Générer mon Rapport PDF</a></li>
                    </ul>
                </div>

                <div class="section" style="background: #fdf2f2; border-left: 4px solid #f87171; margin-bottom: 0;">
                    <h3>🧰 Boîte à outils</h3>
                    <ul style="margin-top: 10px; padding-left: 20px;">
                        <li><a href="generate_doc.php?type=mention" target="_blank"
                                style="color: #c53030; font-weight: bold;">📝 Mention d'info</a></li>
                        <li style="margin-top:5px;"><a href="generate_doc.php?type=droit_image" target="_blank"
                                style="color: #c53030; font-weight: bold;">📸 Droit à l'image</a></li>
                        <li style="margin-top:5px;"><a href="generate_doc.php?type=guide" target="_blank"
                                style="color: #c53030; font-weight: bold;">🛡️ Guide de survie</a></li>
                    </ul>
                </div>

                <?php
                include_once("includes/functions.php");
                $userToolLinks = getUsedToolsGdprLinks($pdo, $currentUser['id_utilisateur']);
                if (!empty($userToolLinks)):
                    ?>
                    <div class="section" style="background: #e6fffa; border-left: 4px solid #38b2ac; margin-bottom: 0;">
                        <h3>🔗 Mes outils et conformité</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 10px;">
                            <?php foreach ($userToolLinks as $name => $url): ?>
                                <a href="<?php echo $url; ?>" target="_blank" class="btn"
                                    style="padding: 5px 8px; font-size: 11px; background-color: #4a5568;">
                                    🌐 <?php echo $name; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Colonne DROITE : Conseils IA & PPMS -->
            <div class="dashboard-col">
                <?php if (!empty($conseils)): ?>
                    <div class="section ai-advice" style="margin-bottom: 0;">
                        <h3>🤖 Conseils</h3>
                        <div class="advice-block" style="padding: 10px;">
                            <h4 style="font-size: 0.9em;">✅ Bonnes pratiques</h4>
                            <p style="font-size: 0.85em;">
                                <?php echo nl2br(htmlspecialchars($conseils['bonnes_pratiques'] ?? 'En attente...')); ?></p>
                        </div>
                        <div class="advice-block warning" style="padding: 10px; margin-top: 10px;">
                            <h4 style="font-size: 0.9em;">⚠️ Points de vigilance</h4>
                            <p style="font-size: 0.85em;">
                                <?php echo nl2br(htmlspecialchars($conseils['points_vigilance'] ?? 'En attente...')); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="section" style="background: #fffaf0; border-left: 4px solid #ed8936; margin-bottom: 0;">
                    <h3>🚨 Sécurité & PPMS</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                        <?php if (!empty($ppmsProcedures)): ?>
                            <?php foreach ($ppmsProcedures as $proc): ?>
                                <div
                                    style="background: white; padding: 10px; border-radius: 6px; border: 1px solid #feebc8; display: flex; justify-content: space-between; align-items: center;">
                                    <span
                                        style="font-size: 0.9em; font-weight: bold;"><?php echo htmlspecialchars($proc['titre']); ?></span>
                                    <a href="<?php echo htmlspecialchars($proc['chemin_fichier']); ?>" target="_blank"
                                        class="btn" style="padding: 4px 8px; font-size: 11px; background-color: #ed8936;">📥</a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #a0aec0; font-style: italic; font-size: 0.85em;">Aucune procédure publiée.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>