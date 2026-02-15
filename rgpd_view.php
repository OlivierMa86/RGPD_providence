<?php
include_once("includes/config.php");
include_once("includes/auth.php");
include_once("includes/functions.php");

$message = "";
$error = "";

// Vérification de la bibliothèque PDF
$lib_exists = file_exists("vendor/tcpdf/tcpdf.php");

// Statistiques utilisateur (Bilan)
$stmt = $pdo->prepare("SELECT score_conformite, observations, date_validation FROM questionnaires WHERE id_utilisateur=? AND statut='complete' ORDER BY date_validation DESC LIMIT 1");
$stmt->execute([$currentUser['id_utilisateur']]);
$bilan = $stmt->fetch();

// Charger les conseils IA
$stmt = $pdo->prepare("SELECT type_fiche, observations FROM fiches WHERE id_utilisateur = ? AND type_fiche IN ('bonnes_pratiques', 'points_vigilance') ORDER BY date_generation DESC LIMIT 2");
$stmt->execute([$currentUser['id_utilisateur']]);
$conseils = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Charger les outils
$userToolLinks = getUsedToolsGdprLinks($pdo, $currentUser['id_utilisateur']);

if (isset($_GET['action']) && $_GET['action'] == "pdf") {
    if (!$lib_exists) {
        $error = "La fonction de génération PDF n'est pas encore activée sur ce serveur (Bibliothèque TCPDF manquante).";
    } else {
        // Construction du contenu de la fiche
        $content = "<h1>Fiche RGPD de " . htmlspecialchars($currentUser['nom']) . "</h1>";
        $content .= "<p><b>Fonction :</b> " . htmlspecialchars($currentUser['fonction']) . "</p>";
        $content .= "<p><b>Date :</b> " . date('d/m/Y') . "</p>";

        if ($bilan) {
            $content .= "<h3>Bilan de conformité</h3>";
            $content .= "<p>Score estimé : " . ($bilan['score_conformite'] ?? '0') . "%</p>";
            $content .= "<h3>Conseils</h3>";
            $content .= "<p>" . nl2br(htmlspecialchars($bilan['observations'] ?? '')) . "</p>";
        } else {
            $content .= "<p><i>Les données de bilan ne sont pas encore disponibles. Complétez le questionnaire pour générer votre bilan.</i></p>";
        }

        $filename = "pdf/export/fiche_" . $currentUser['id_utilisateur'] . "_" . date('Ymd') . ".pdf";
        if (genererPDF($content, $filename)) {
            $message = "Votre fiche PDF a été générée avec succès.";
            $pdf_link = $filename;
        } else {
            $error = "Échec de la génération du PDF. Vérifiez que la bibliothèque TCPDF est correctement installée.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css?v=1.3">
    <title>Espace RGPD - Plateforme Providence</title>
    <style>
        .rgpd-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .tool-link {
            display: inline-block;
            background: #4a5568;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85em;
            margin: 5px 2px;
        }
    </style>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <div class="welcome-header" style="background: linear-gradient(135deg, #3182ce, #2b6cb0);">
            <h2>🛡️ Espace RGPD & Conformité</h2>
            <p>Gérez vos questionnaires, accédez à vos bilans et utilisez les outils de conformité.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert success" style="margin-top:20px;">
                <?php echo $message; ?>
                <?php if (isset($pdf_link)): ?>
                    <br><a href="<?php echo $pdf_link; ?>" target="_blank" style="font-weight:bold;">📥 Télécharger le PDF</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error" style="margin-top:20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="rgpd-grid">
            <!-- QUESTIONNAIRE SECTION -->
            <div class="section">
                <h3>📝 Questionnaire & Bilan</h3>
                <?php if ($bilan): ?>
                    <div
                        style="background: #ebf8ff; padding: 15px; border-radius: 8px; border: 1px solid #bee3f8; margin-bottom: 15px;">
                        <span style="font-size: 0.9em; color: #2b6cb0;">Dernière validation : <strong>
                                <?php echo date('d/m/Y', strtotime($bilan['date_validation'])); ?>
                            </strong></span><br>
                        <span style="font-size: 1.2em; font-weight: bold; color: #2c5282;">Score :
                            <?php echo $bilan['score_conformite']; ?>%
                        </span>
                    </div>
                <?php else: ?>
                    <p style="color: #718096; font-style: italic;">Vous n'avez pas encore terminé de bilan de conformité.
                    </p>
                <?php endif; ?>
                <a href="questionnaire.php" class="btn" style="width: 100%; box-sizing: border-box;">Refaire ou mettre à
                    jour mon bilan</a>
            </div>

            <!-- PDF REPORT SECTION -->
            <div class="section">
                <h3>📥 Rapport PDF Personnel</h3>
                <p>Téléchargez un document récapitulatif complet avec toutes vos réponses, votre score et les conseils
                    de l'IA.
                </p>
                <a href="generate_bilan_pdf.php" class="btn"
                    style="width: 100%; box-sizing: border-box; background-color: #4a5568;">📋 Générer mon bilan complet
                    (PDF)</a>
            </div>

            <!-- TOOLBOX SECTION -->
            <div class="section" style="background: #fff5f5; border-left: 4px solid #f56565;">
                <h3>🧰 Boîte à outils Juridiques</h3>
                <p>Générez des documents pré-remplis pour vos activités pédagogiques.</p>
                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                    <a href="generate_doc.php?type=mention" target="_blank"
                        style="color: #c53030; font-weight: bold; text-decoration: none;">📄 Mention d'info pour les
                        parents</a>
                    <a href="generate_doc.php?type=droit_image" target="_blank"
                        style="color: #c53030; font-weight: bold; text-decoration: none;">📸 Formulaire de Droit à
                        l'image</a>
                    <a href="generate_doc.php?type=guide" target="_blank"
                        style="color: #c53030; font-weight: bold; text-decoration: none;">🛡️ Guide de survie des
                        données</a>
                </div>
            </div>
        </div>

        <div class="rgpd-grid">
            <!-- AI ADVICE SECTION -->
            <div class="section ai-advice" style="flex: 2;">
                <h3>🤖 Conseils</h3>
                <?php if (!empty($conseils)): ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="advice-block">
                            <h4>✅ Bonnes pratiques</h4>
                            <p style="font-size: 0.9em;">
                                <?php echo nl2br(htmlspecialchars($conseils['bonnes_pratiques'] ?? 'Examinez vos pratiques pour recevoir des conseils.')); ?>
                            </p>
                        </div>
                        <div class="advice-block warning">
                            <h4>⚠️ Points de vigilance</h4>
                            <p style="font-size: 0.9em;">
                                <?php echo nl2br(htmlspecialchars($conseils['points_vigilance'] ?? 'Aucune alerte majeure détectée.')); ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: #a0aec0; font-style: italic;">Complétez le questionnaire pour obtenir des conseils
                        personnalisés.</p>
                <?php endif; ?>
            </div>

            <!-- USED TOOLS COMPLIANCE -->
            <div class="section" style="background: #e6fffa; border-left: 4px solid #38b2ac;">
                <h3>🔗 Conformité de vos outils</h3>
                <p>Liens vers les politiques de confidentialité des outils que vous avez déclarés :</p>
                <div style="margin-top: 10px;">
                    <?php if (!empty($userToolLinks)): ?>
                        <?php foreach ($userToolLinks as $name => $url): ?>
                            <a href="<?php echo $url; ?>" target="_blank" class="tool-link">🌐
                                <?php echo $name; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #718096; font-size: 0.9em;">Déclarez vos outils dans le questionnaire pour voir
                            leurs liens.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="dashboard.php" class="btn" style="background-color: #718096;">🏠 Retour au tableau de bord</a>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>