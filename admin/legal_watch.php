<?php
include("../includes/config.php");
include("../includes/auth.php");
include("legal_watch_data.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$context = $_GET['context'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.5">
    <title>Veille Juridique Assistée - Plateforme Providence</title>
    <style>
        .watch-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .watch-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #edf2f7;
            border-left: 6px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: relative;
        }

        .watch-card.badge-critique {
            border-left-color: #e53e3e;
        }

        .watch-card.badge-action-requise {
            border-left-color: #ed8936;
        }

        .watch-card.badge-majeur {
            border-left-color: #3182ce;
        }

        .watch-card.badge-conformite {
            border-left-color: #38a169;
        }

        .watch-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .watch-date {
            font-size: 0.85em;
            color: #718096;
            font-weight: bold;
        }

        .watch-badge {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
            background: #edf2f7;
            color: #4a5568;
            font-weight: 800;
            text-transform: uppercase;
        }

        .watch-card h3 {
            margin: 0;
            font-size: 18px;
            color: #2d3748;
        }

        .action-box {
            background: #fffaf0;
            border: 1px dashed #ed8936;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .action-box strong {
            color: #c05621;
            display: block;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .ai-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/admin_nav.php"); ?>

    <div class="container">
        <div class="welcome-header" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <div class="ai-badge">✨ Veille Juridique Assistée par IA</div>
            <h2>Actualités Réglementaires & Évolutions</h2>
            <p>Analyse automatique des changements de lois (RGPD & PPMS) pour votre établissement.</p>
        </div>

        <div style="margin-bottom: 20px; display: flex; gap: 10px;">
            <a href="?context=all" class="btn"
                style="background: <?php echo $context == 'all' ? '#4a5568' : '#e2e8f0'; ?>; color: <?php echo $context == 'all' ? 'white' : '#4a5568'; ?>;">Tous</a>
            <a href="?context=rgpd" class="btn"
                style="background: <?php echo $context == 'rgpd' ? '#2b6cb0' : '#e2e8f0'; ?>; color: <?php echo $context == 'rgpd' ? 'white' : '#4a5568'; ?>;">🛡️
                RGPD</a>
            <a href="?context=ppms" class="btn"
                style="background: <?php echo $context == 'ppms' ? '#c05621' : '#e2e8f0'; ?>; color: <?php echo $context == 'ppms' ? 'white' : '#4a5568'; ?>;">🚨
                PPMS</a>
        </div>

        <div class="watch-grid">
            <?php
            $display_items = [];
            if ($context == 'all' || $context == 'rgpd') {
                foreach ($veille_rgpd as $item)
                    $display_items[] = array_merge($item, ['type' => 'RGPD']);
            }
            if ($context == 'all' || $context == 'ppms') {
                foreach ($veille_ppms as $item)
                    $display_items[] = array_merge($item, ['type' => 'PPMS']);
            }

            // Tri par date décroissante
            usort($display_items, function ($a, $b) {
                return strcmp($b['date'], $a['date']);
            });

            foreach ($display_items as $item):
                $badge_class = 'badge-' . strtolower(str_replace(' ', '-', $item['badge']));
                ?>
                <div class="watch-card <?php echo $badge_class; ?>">
                    <div class="watch-header">
                        <span class="watch-badge">
                            <?php echo $item['type']; ?> •
                            <?php echo $item['badge']; ?>
                        </span>
                        <span class="watch-date">
                            <?php echo date('d/m/Y', strtotime($item['date'])); ?>
                        </span>
                    </div>
                    <h3>
                        <?php echo htmlspecialchars($item['titre']); ?>
                    </h3>
                    <p style="font-size: 0.95em; color: #4a5568; line-height: 1.5;">
                        <?php echo htmlspecialchars($item['description']); ?>
                    </p>

                    <div class="action-box">
                        <strong>💡 Action à mener pour l'établissement :</strong>
                        <p style="margin:0; font-size: 0.9em;">
                            <?php echo htmlspecialchars($item['action']); ?>
                        </p>
                    </div>

                    <?php if (isset($item['lien'])): ?>
                        <a href="<?php echo $item['lien']; ?>" target="_blank"
                            style="font-size: 0.85em; color: #3182ce; font-weight: bold; margin-top: 5px;">Source officielle
                            →</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" class="btn" style="background-color: #718096;">🏠 Retour à l'administration</a>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>