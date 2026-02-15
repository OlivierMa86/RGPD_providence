<?php
include("includes/config.php");
include("includes/auth.php");
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css?v=1.4">
    <title>Guide d'utilisation - Plateforme Providence</title>
    <style>
        .guide-section {
            margin-bottom: 40px;
        }

        .guide-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #edf2f7;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .guide-card h3 {
            color: #0059b2;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step-list {
            list-style: none;
            padding: 0;
        }

        .step-list li {
            padding: 12px 0;
            border-bottom: 1px solid #f7fafc;
            display: flex;
            gap: 15px;
        }

        .step-number {
            background: #0059b2;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .badge-user {
            background: #ebf8ff;
            color: #2b6cb0;
        }

        .badge-admin {
            background: #faf5ff;
            color: #6b46c1;
        }
    </style>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <div class="welcome-header" style="background: linear-gradient(135deg, #4a5568, #2d3748);">
            <h2>📖 Guide & Tutoriel</h2>
            <p>Apprenez à maîtriser les outils de la Plateforme Providence pour votre établissement.</p>
        </div>

        <!-- SECTION UTILISATEUR -->
        <div class="guide-section">
            <span class="role-badge badge-user">Espace Enseignant / Personnel</span>
            <div class="guide-card">
                <h3>🛡️ Le module RGPD</h3>
                <p>Ce module vous aide à assurer la protection des données de vos élèves et à rester en conformité avec
                    la loi.</p>
                <ul class="step-list">
                    <li>
                        <div class="step-number">1</div>
                        <div><strong>Bilan de conformité :</strong> Répondez au questionnaire annuel pour évaluer vos
                            pratiques numériques.</div>
                    </li>
                    <li>
                        <div class="step-number">2</div>
                        <div><strong>Conseils de l'IA :</strong> Après votre bilan, consultez les recommandations
                            personnalisées pour sécuriser vos données.</div>
                    </li>
                    <li>
                        <div class="step-number">3</div>
                        <div><strong>Boîte à outils :</strong> Générez en un clic vos mentions d'information ou vos
                            formulaires de droit à l'image.</div>
                    </li>
                </ul>
            </div>

            <div class="guide-card">
                <h3>🚨 Le module Sécurité & PPMS</h3>
                <p>Accédez instantanément aux consignes de sécurité en cas d'urgence dans l'établissement.</p>
                <ul class="step-list">
                    <li>
                        <div class="step-number">1</div>
                        <div><strong>Fiches Réflexes :</strong> Consultez les conduites à tenir (Évacuation,
                            Confinement,
                            Attentat) en cas d'alerte.</div>
                    </li>
                    <li>
                        <div class="step-number">2</div>
                        <div><strong>Procédures partagées :</strong> Téléchargez les PDF officiels de l'établissement
                            concernant la sécurité.</div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- SECTION ADMIN -->
        <?php if ($currentUser['role'] == 'admin'): ?>
            <div class="guide-section">
                <span class="role-badge badge-admin">Espace Administration</span>
                <div class="guide-card" style="border-left: 5px solid #6b46c1;">
                    <h3>⚙️ Gestion du Personnel</h3>
                    <p>En tant qu'administrateur, vous pilotez les accès de l'établissement.</p>
                    <ul class="step-list">
                        <li>
                            <div class="step-number">1</div>
                            <div><strong>Comptes :</strong> Créez, modifiez ou réinitialisez les mots de passe (🔑) des
                                utilisateurs.</div>
                        </li>
                        <li>
                            <div class="step-number">2</div>
                            <div><strong>Suivi :</strong> Vérifiez qui a complété son bilan RGPD pour assurer une couverture
                                totale de l'établissement.</div>
                        </li>
                    </ul>
                </div>

                <div class="guide-card" style="border-left: 5px solid #38a169;">
                    <h3>📁 Pilotage RGPD & Audit</h3>
                    <ul class="step-list">
                        <li>
                            <div class="step-number">1</div>
                            <div><strong>Registre :</strong> Centralisez tous les traitements de données de
                                l'établissement.</div>
                        </li>
                        <li>
                            <div class="step-number">2</div>
                            <div><strong>Preuves :</strong> Gérez la checklist des 10 documents obligatoires en cas d'audit
                                CNIL.</div>
                        </li>
                        <li>
                            <div class="step-number">3</div>
                            <div><strong>Export :</strong> Téléchargez le registre complet au format Excel/CSV.</div>
                        </li>
                        <li>
                            <div class="step-number">4</div>
                            <div><strong>Veille Juridique :</strong> Consultez les dernières évolutions de la loi (Pix IA,
                                PPMS Unifié) avec des actions concrètes recommandées.</div>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; text-align: center;">
            <a href="dashboard.php" class="btn" style="background-color: #718096;">🏠 Retour au tableau de bord</a>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>