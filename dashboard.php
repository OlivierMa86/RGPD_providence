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
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Tableau de bord</title>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <div class="welcome-header">
            <h2>👋 Bienvenue, <?php echo htmlspecialchars($currentUser['nom']); ?></h2>
            <p><strong>Fonction :</strong> <?php echo htmlspecialchars($currentUser['fonction']); ?></p>
        </div>

        <div class="dashboard-grid">
            <div class="section">
                <h3>📊 Mon bilan RGPD</h3>
                <p>Questionnaires complétés : <strong><?php echo $stats['nb']; ?></strong></p>
                <div class="actions">
                    <a href="questionnaire.php" class="btn">📝 Compléter / Mettre à jour mon questionnaire</a>
                </div>
            </div>

            <?php if (!empty($conseils)): ?>
                <div class="section ai-advice">
                    <h3>🤖 Conseils de l'IA (Adaptés à votre poste)</h3>

                    <div class="advice-block">
                        <h4>✅ Bonnes pratiques</h4>
                        <p><?php echo nl2br(htmlspecialchars($conseils['bonnes_pratiques'] ?? 'En attente de génération...')); ?>
                        </p>
                    </div>

                    <div class="advice-block warning">
                        <h4>⚠️ Points de vigilance</h4>
                        <p><?php echo nl2br(htmlspecialchars($conseils['points_vigilance'] ?? 'En attente de génération...')); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            include_once("includes/functions.php");
            $userToolLinks = getUsedToolsGdprLinks($pdo, $currentUser['id_utilisateur']);
            if (!empty($userToolLinks)):
                ?>
                <div class="section" style="background: #e6fffa; border-left: 4px solid #38b2ac;">
                    <h3>🔗 Vos outils et leur conformité</h3>
                    <p>Voici les références RGPD des outils que vous avez déclarés utiliser :</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px;">
                        <?php foreach ($userToolLinks as $name => $url): ?>
                            <a href="<?php echo $url; ?>" target="_blank" class="btn"
                                style="padding: 10px 15px; font-size: 13px; background-color: #4a5568; text-decoration: none; border-radius: 5px; color: white;">
                                🌐 <?php echo $name; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="section">
                <h3>📄 Mes documents</h3>
                <ul>
                    <li><a href="fiches.php">Accéder à mes fiches PDF</a></li>
                    <li style="margin-top:10px;"><a href="fiches.php?action=pdf" class="btn"
                            style="padding: 5px 15px; font-size: 13px;">⚡ Générer mon PDF maintenant</a></li>
                </ul>
            </div>

            <div class="section" style="background: #fdf2f2; border-left: 4px solid #f87171;">
                <h3>🧰 Boîte à outils & Ressources</h3>
                <p>Documents indispensables pré-remplis avec vos réponses :</p>
                <ul style="margin-top: 10px;">
                    <li><a href="generate_doc.php?type=mention" target="_blank"
                            style="color: #c53030; font-weight: bold;">📝 Modèle de mention d'information
                            (Élèves/Parents)</a></li>
                    <li><a href="generate_doc.php?type=droit_image" target="_blank"
                            style="color: #c53030; font-weight: bold;">📸 Autorisation Droit à l'image CPGE/Collège</a>
                    </li>
                    <li><a href="generate_doc.php?type=guide" target="_blank"
                            style="color: #c53030; font-weight: bold;">🛡️ Mon Guide de survie RGPD personnalisé</a>
                    </li>
                </ul>
                <p style="font-size: 0.85em; color: #666; margin-top: 10px;"><em>Note : Ces documents sont générés
                        dynamiquement selon votre dernier bilan.</em></p>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>