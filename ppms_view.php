<?php
include("includes/config.php");
include("includes/auth.php");

// Récupération des procédures partagées (PDF)
$ppmsProcedures = $pdo->query("SELECT titre, chemin_fichier, description, date_publication FROM ppms_procedures ORDER BY date_publication DESC")->fetchAll();

// Récupération des fiches de sécurité (Textes)
$fiches = $pdo->query("SELECT * FROM ppms_fiches")->fetchAll(PDO::FETCH_ASSOC);

function formatConsignes($text)
{
    if (!$text)
        return "";
    $lines = explode("\n", $text);
    $html = "<ul>";
    foreach ($lines as $line) {
        if (trim($line))
            $html .= "<li>" . htmlspecialchars(trim($line)) . "</li>";
    }
    $html .= "</ul>";
    return $html;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css?v=1.3">
    <title>Sécurité & PPMS - Plateforme Providence</title>
    <style>
        .fiche-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .fiche-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-top: 8px solid #cbd5e0;
        }

        .fiche-card.evacuation {
            border-top-color: #e53e3e;
        }

        .fiche-card.confinement {
            border-top-color: #3182ce;
        }

        .fiche-card.attentat {
            border-top-color: #d69e2e;
        }

        .fiche-header {
            padding: 15px;
            background: #f7fafc;
            border-bottom: 1px solid #edf2f7;
        }

        .fiche-header h3 {
            margin: 0;
            font-size: 1.1em;
            color: #2d3748;
        }

        .fiche-header small {
            color: #a0aec0;
        }

        .fiche-body {
            padding: 20px;
        }

        .fiche-section {
            margin-bottom: 15px;
        }

        .fiche-section strong {
            display: block;
            color: #4a5568;
            font-size: 0.85em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .fiche-body p,
        .fiche-body li {
            font-size: 0.95em;
            line-height: 1.4;
            margin: 5px 0;
        }

        .fiche-body ul {
            padding-left: 20px;
            margin: 5px 0;
        }

        .alert-box {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-box.signal {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .alert-box.alerte {
            background: #ebf8ff;
            color: #2b6cb0;
            border: 1px solid #bee3f8;
        }

        .procedure-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #ed8936;
        }
    </style>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <div class="welcome-header" style="background: linear-gradient(135deg, #2d3748, #4a5568);">
            <h2>🚨 Sécurité & PPMS</h2>
            <p>Consignes d'urgence et documents de sécurité pour le personnel.</p>
        </div>

        <!-- FICHES INTERACTIVES -->
        <h3>📄 Fiches de conduite à tenir</h3>
        <div class="fiche-container">
            <?php foreach ($fiches as $f): ?>
                <div class="fiche-card <?php echo $f['type']; ?>">
                    <div class="fiche-header">
                        <h3><?php echo htmlspecialchars($f['titre']); ?></h3>
                        <small><?php echo htmlspecialchars($f['entete']); ?> | MAJ: <?php echo $f['last_update']; ?></small>
                    </div>
                    <div class="fiche-body">
                        <div class="alert-box signal">
                            📢 SIGNAL : <?php echo htmlspecialchars($f['signal_sonore']); ?>
                        </div>
                        <div class="alert-box alerte">
                            📞 ALERTE : <?php echo htmlspecialchars($f['alerte_msg']); ?>
                        </div>

                        <div class="fiche-section">
                            <strong>Consignes :</strong>
                            <?php echo formatConsignes($f['consignes_1']); ?>
                        </div>

                        <?php if ($f['consignes_2']): ?>
                            <div class="fiche-section">
                                <strong>Instructions spécifiques :</strong>
                                <?php echo formatConsignes($f['consignes_2']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="fiche-section" style="background: #f7fafc; padding: 10px; border-radius: 6px;">
                            <strong>À noter :</strong>
                            <p><?php echo nl2br(htmlspecialchars($f['divers'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- DOCUMENTS PDF -->
        <hr style="border: 0; border-top: 1px solid #eee; margin: 40px 0;">
        <h3>📥 Documents à télécharger (Plans, Protocoles...)</h3>
        <div class="section" style="padding: 20px;">
            <?php if (!empty($ppmsProcedures)): ?>
                <?php foreach ($ppmsProcedures as $proc): ?>
                    <div class="procedure-item">
                        <div>
                            <strong style="color: #2d3748;"><?php echo htmlspecialchars($proc['titre']); ?></strong><br>
                            <small style="color: #718096;"><?php echo htmlspecialchars($proc['description']); ?></small>
                        </div>
                        <a href="<?php echo htmlspecialchars($proc['chemin_fichier']); ?>" target="_blank" class="btn"
                            style="padding: 8px 15px; font-size: 13px; background-color: #ed8936;">📥 Télécharger</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #a0aec0; padding: 20px;">Aucun document PDF publié pour le moment.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="dashboard.php" class="btn" style="background-color: #718096;">🏠 Retour au tableau de bord</a>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>