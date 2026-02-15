<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
    <title>Administration - Sélection - Plateforme Providence</title>
    <style>
        .selection-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
        }

        @media (max-width: 1000px) {
            .selection-grid {
                grid-template-columns: 1fr;
            }
        }

        .selection-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 2px solid transparent;
        }

        .selection-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-color: #0059b2;
        }

        .selection-card .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .selection-card h2 {
            color: #0059b2;
            margin-bottom: 15px;
            font-size: 28px;
        }

        .selection-card p {
            color: #718096;
            font-size: 16px;
            line-height: 1.6;
        }

        .selection-card .btn {
            margin-top: auto;
            width: 80%;
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="welcome-header">
            <h2>⚙️ Panel d'administration</h2>
            <p>Veuillez choisir le domaine d'administration que vous souhaitez gérer.</p>
        </div>

        <div class="selection-grid">
            <a href="rgpd_dashboard.php" class="selection-card">
                <div class="icon">🛡️</div>
                <h2>Conformité RGPD</h2>
                <p>Gérez le registre des activités, les preuves de conformité et les bilans enseignants.</p>
                <span class="btn">Accéder au RGPD</span>
            </a>

            <a href="ppms_dashboard.php" class="selection-card">
                <div class="icon">🚨</div>
                <h2>Sécurité & PPMS</h2>
                <p>Gérez les plans de sûreté, les exercices de sécurité et les consignes d'urgence.</p>
                <span class="btn" style="background-color: #ed8936;">Accéder au PPMS</span>
            </a>

            <a href="users.php" class="selection-card">
                <div class="icon">👥</div>
                <h2>Utilisateurs</h2>
                <p>Gérez les comptes, les accès et les fonctions du personnel de l'établissement.</p>
                <span class="btn" style="background-color: #38b2ac;">Gérer les Comptes</span>
            </a>

            <a href="../dashboard.php" class="selection-card">
                <div class="icon">📝</div>
                <h2>Mon Espace Personnel</h2>
                <p>Complétez votre propre questionnaire RGPD, consultez votre bilan et téléchargez votre fiche PDF.</p>
                <span class="btn" style="background-color: #805ad5;">Mon Questionnaire</span>
            </a>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>