<?php
include_once("includes/config.php");
include_once("includes/auth.php");

$message = "";
$error = "";

// Vérification de la bibliothèque PDF
$lib_exists = file_exists("vendor/tcpdf/tcpdf.php");

if (isset($_GET['action']) && $_GET['action'] == "pdf") {
    if (!$lib_exists) {
        $error = "La fonction de génération PDF n'est pas encore activée sur ce serveur (Bibliothèque TCPDF manquante).";
    } else {
        // Construction du contenu de la fiche
        $content = "<h1>Fiche RGPD de " . htmlspecialchars($currentUser['nom']) . "</h1>";
        $content .= "<p><b>Fonction :</b> " . htmlspecialchars($currentUser['fonction']) . "</p>";
        $content .= "<p><b>Date :</b> " . date('d/m/Y') . "</p>";

        // On récupère le dernier bilan
        try {
            $stmt = $pdo->prepare("SELECT score_conformite, observations FROM questionnaires WHERE id_utilisateur = ? AND statut = 'complete' ORDER BY date_validation DESC LIMIT 1");
            $stmt->execute([$currentUser['id_utilisateur']]);
            $bilan = $stmt->fetch();

            if ($bilan) {
                $content .= "<h3>Bilan de conformité</h3>";
                $content .= "<p>Score estimé : " . ($bilan['score_conformite'] ?? '0') . "%</p>";
                $content .= "<h3>Conseils de l'IA</h3>";
                $content .= "<p>" . nl2br(htmlspecialchars($bilan['observations'] ?? '')) . "</p>";
            }
        } catch (PDOException $e) {
            $content .= "<p><i>Les données de bilan ne sont pas encore disponibles.</i></p>";
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
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Mes fiches - Plateforme RGPD</title>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <h2>📄 Mes fiches et documents</h2>

        <?php if ($message): ?>
            <div class="alert success">
                <?php echo $message; ?>
                <?php if (isset($pdf_link)): ?>
                    <br><a href="<?php echo $pdf_link; ?>" target="_blank" style="font-weight:bold;">📥 Télécharger le PDF</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="section">
            <h3>🧾 Génération de documents</h3>
            <p>Vous pouvez générer une fiche récapitulative de votre situation RGPD basée sur votre dernier
                questionnaire validé.</p>
            <a href="fiches.php?action=pdf" class="btn">Générer ma fiche PDF</a>
        </div>

        <div class="section">
            <h3>📂 Historique</h3>
            <p>Aucun document archivé pour le moment.</p>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>