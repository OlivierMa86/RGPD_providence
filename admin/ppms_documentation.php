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
    <title>Documentation PPMS - Plateforme Providence</title>
    <style>
        .doc-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .doc-section h2 {
            color: #2d3748;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 10px;
            margin-top: 0;
        }

        .doc-section h3 {
            color: #4a5568;
            margin-top: 20px;
        }

        .doc-section ul {
            padding-left: 20px;
        }

        .doc-section li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .advice-box {
            background: #f7fafc;
            border-left: 4px solid #4a5568;
            padding: 15px;
            margin-top: 20px;
        }

        .note-important {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 15px;
            margin-top: 20px;
        }

        .alert-type {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #cbd5e0;
        }

        .alert-fire {
            background: #fff5f5;
            border-left-color: #f56565;
        }

        .alert-intrusion {
            background: #fefcbf;
            border-left-color: #ecc94b;
        }

        .alert-major {
            background: #ebf8ff;
            border-left-color: #4299e1;
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/admin_nav.php"); ?>

    <div class="container">
        <div class="welcome-header" style="background: linear-gradient(135deg, #4a5568, #2d3748);">
            <h2>📚 Documentation PPMS & Sécurité</h2>
            <p>Cadre réglementaire et procédures obligatoires en établissement scolaire.</p>
        </div>

        <div class="doc-section">
            <h2>1. Les Documents Obligatoires</h2>
            <p>Le cadre réglementaire a évolué : on ne sépare plus strictement le "PPMS Attentat-Intrusion" et le "PPMS
                Risques Majeurs". Ils sont désormais regroupés.</p>

            <ul>
                <li><strong>Le PPMS Unique (Plan Particulier de Mise en Sûreté) :</strong> C'est le document de
                    référence. Il doit être validé par le Conseil d'Administration (ou l'instance de gestion du privé)
                    et transmis à l'académie. Il contient : les plans de l'établissement, les zones de confinement, les
                    lieux de rassemblement, et la composition de la cellule de crise.</li>
                <li><strong>Le Registre de Sécurité :</strong> Obligatoire pour tout Établissement Recevant du Public
                    (ERP). Il trace les vérifications techniques (extincteurs, alarmes) et l'historique des exercices.
                </li>
                <li><strong>Le DUER (Document Unique d'Évaluation des Risques) :</strong> Indispensable pour la sécurité
                    des salariés (enseignants et personnels). Il doit intégrer les risques liés aux menaces extérieures.
                </li>
                <li><strong>Le Carnet de Maintenance :</strong> Pour le suivi des installations de désenfumage et de
                    détection incendie.</li>
            </ul>
        </div>

        <div class="doc-section">
            <h2>2. Consignes par Type d'Alerte</h2>

            <div class="alert-type alert-fire">
                <h3>🔥 Alerte Incendie</h3>
                <p><strong>Signal :</strong> Alarme sonore spécifique (NF S 32-001).</p>
                <p><strong>Action :</strong> Évacuation immédiate vers le point de rassemblement extérieur.</p>
                <p><strong>Fréquence :</strong> 1 exercice par trimestre. Le premier doit avoir lieu dans le mois
                    suivant la rentrée.</p>
                <p><strong>Consigne clé :</strong> Le professeur est le dernier à sortir de la salle, ferme la porte
                    (sans la verrouiller) et prend le registre d'appel.</p>
            </div>

            <div class="alert-type alert-intrusion">
                <h3>🛡️ Alerte Intrusion / Attentat</h3>
                <p><strong>Signal :</strong> Distinct de l'alarme incendie (souvent une corne de brume, un message
                    pré-enregistré ou une sonnerie intermittente).</p>
                <p><strong>Action :</strong> Deux options selon la situation :
                <ul>
                    <li><strong>S'échapper :</strong> Si le danger est localisé et qu'une sortie sûre est possible.</li>
                    <li><strong>Se confiner (Barricader) :</strong> Éteindre les lumières, s'éloigner des parois
                        vitrées, s'allonger au sol, et mettre les téléphones en silencieux.</li>
                </ul>
                </p>
                <p><strong>Fréquence :</strong> 1 exercice par an.</p>
            </div>

            <div class="alert-type alert-major">
                <h3>🌪️ Risques Majeurs (Confinement PPMS)</h3>
                <p><strong>Signal :</strong> Souvent une alerte radio ou un signal sonore interne spécifique.</p>
                <p><strong>Action :</strong> Mise à l'abri pour se protéger d'un risque toxique, chimique ou météo.</p>
                <p><strong>Consigne clé :</strong> Calfeutrer les ouvertures (portes, aérations) et ne pas sortir avant
                    l'ordre des autorités.</p>
            </div>
        </div>

        <div class="doc-section">
            <h2>3. Les Points de Vigilance pour le Privé</h2>
            <ul>
                <li><strong>Responsabilité :</strong> Dans le privé sous contrat, c'est le Chef d'Établissement qui est
                    responsable de la rédaction et de l'accessibilité du PPMS, en lien avec l'organisme gestionnaire.
                </li>
                <li><strong>Affichage :</strong> Les consignes de sécurité (plans d'évacuation, numéros d'urgence)
                    doivent être affichées de manière visible dans chaque salle de classe.</li>
                <li><strong>Accessibilité :</strong> Depuis la loi de 2021 (Loi Matras), les établissements doivent
                    veiller à ce que les procédures soient inclusives (alarmes visuelles pour les élèves malentendants
                    par exemple).</li>
            </ul>

            <div class="note-important">
                <p><strong>Note importante :</strong> N'oubliez pas que les exercices de sécurité doivent faire l'objet
                    d'un procès-verbal (PV) consigné dans le registre de sécurité, précisant le temps d'évacuation et
                    les éventuels dysfonctionnements.</p>
            </div>
        </div>

        <div class="section">
            <a href="ppms_dashboard.php" class="btn" style="background-color: #4a5568;">🔙 Retour au tableau de bord
                PPMS</a>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>