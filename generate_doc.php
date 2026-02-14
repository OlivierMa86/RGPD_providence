<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once(__DIR__ . "/includes/config.php");
include_once(__DIR__ . "/includes/auth.php");
// functions.php est déjà inclus par config.php

$type = $_GET['type'] ?? '';
$id_utilisateur = $currentUser['id_utilisateur'] ?? null;

// Récupérer les outils détectés pour cet utilisateur
$tools = getUsedToolsGdprLinks($pdo, $id_utilisateur);
$toolNames = array_keys($tools);

// Récupérer les réponses spécifiques pour le pré-remplissage
$stmt = $pdo->prepare("SELECT q.id_question, r.reponse_utilisateur, q.question_txt 
                       FROM reponses r 
                       JOIN questions q ON r.id_question = q.id_question 
                       WHERE r.id_questionnaire = (
                           SELECT MAX(id_questionnaire) FROM questionnaires WHERE id_utilisateur = ?
                       )");
$stmt->execute([$id_utilisateur]);
$reponses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mapper les réponses pour un accès facile
$data = [];
foreach ($reponses as $row) {
    if (stripos($row['question_txt'], 'téléphone personnel') !== false)
        $data['phone_usage'] = $row['reponse_utilisateur'];
    if (stripos($row['question_txt'], 'stockez-vous vos documents') !== false)
        $data['storage'] = $row['reponse_utilisateur'];
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css?v=1.1">
    <title>Génération de Document RGPD</title>
    <style>
        .page-doc {
            background: white;
            padding: 50px;
            max-width: 800px;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            line-height: 1.6;
            color: #333;
        }

        .placeholder {
            background: #fff5f5;
            color: #c53030;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
        }

        @media print {

            .print-btn,
            header,
            footer {
                display: none;
            }

            .page-doc {
                box-shadow: none;
                margin: 0;
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>

<body>
    <button onclick="window.print()" class="btn print-btn">🖨️ Imprimer / Sauvegarder en PDF</button>

    <div class="container">
        <div class="page-doc">
            <?php if ($type == 'mention'): ?>
                <h1>Note d'Information RGPD (Modèle Élèves/Parents)</h1>
                <p><em>Établissement La Providence - La Salle</em></p>
                <hr>
                <p>Dans le cadre de votre scolarité, nous vous informons que l'établissement utilise plusieurs outils
                    numériques pour faciliter les apprentissages et la communication.</p>

                <h3>1. Finalités du traitement</h3>
                <p>Les données sont collectées pour : le suivi pédagogique, la gestion des notes, et l'utilisation d'outils
                    collaboratifs.</p>

                <h3>2. Outils utilisés</h3>
                <p>Outre Pronote et l'ENT, les outils suivants sont mis en œuvre sous la responsabilité de <strong>
                        <?php echo htmlspecialchars($currentUser['nom']); ?>
                    </strong> (
                    <?php echo htmlspecialchars($currentUser['fonction']); ?>) :
                </p>
                <ul>
                    <?php if (!empty($toolNames)): ?>
                        <?php foreach ($toolNames as $t): ?>
                            <li><strong>
                                    <?php echo htmlspecialchars($t); ?>
                                </strong></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><span class="placeholder">[Aucun outil spécifique déclaré dans votre questionnaire]</span></li>
                    <?php endif; ?>
                </ul>

                <h3>3. Conservation et Sécurité</h3>
                <p>Les données sont stockées principalement sur : <strong>
                        <?php echo htmlspecialchars($data['storage'] ?? 'ENT / Serveur sécurisé'); ?>
                    </strong>.</p>

                <h3>4. Vos Droits</h3>
                <p>Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et d'effacement de vos données
                    auprès du DPO de l'établissement.</p>

            <?php elseif ($type == 'droit_image'): ?>
                <h1>Autorisation de Droit à l'Image</h1>
                <p>Je soussigné(e) M./Mme <span class="placeholder">[Nom du Parent]</span>, responsable légal de l'enfant
                    <span class="placeholder">[Nom de l'enfant]</span>,
                </p>
                <p>Autorise <strong>
                        <?php echo htmlspecialchars($currentUser['nom']); ?>
                    </strong>, en sa qualité de <strong>
                        <?php echo htmlspecialchars($currentUser['fonction']); ?>
                    </strong> à l'établissement La Providence,</p>
                <p>À photographier ou filmer mon enfant dans le cadre des activités pédagogiques suivantes :</p>
                <ul>
                    <li>Illustration des cours et projets pédagogiques.</li>
                    <li>Publication sur l'ENT ou Pronote de l'établissement.</li>
                </ul>
                <p>Cette autorisation est valable pour l'année scolaire en cours et peut être révoquée à tout moment.</p>
                <br><br>
                <p>Fait à Poitiers, le
                    <?php echo date('d/m/Y'); ?>
                </p>
                <p>Signature :</p>

            <?php elseif ($type == 'guide'): ?>
                <h1>Guide de Survie RGPD Personnalisé</h1>
                <p>Pour : <strong>
                        <?php echo htmlspecialchars($currentUser['nom']); ?>
                    </strong></p>
                <hr>

                <div class="advice-box" style="background: #f0f7ff; padding: 15px; border-left: 5px solid #2b6cb0;">
                    <h3>🎯 Vos points clés cette semaine</h3>
                    <ul>
                        <?php if (($data['phone_usage'] ?? '') == 'Oui'): ?>
                            <li style="color: #c53030;"><strong>⚠️ Téléphone Personnel :</strong> Vous avez déclaré utiliser
                                votre mobile pro. Pensez à ne jamais stocker de photos d'élèves dans votre galerie personnelle
                                (utilisez le Drive).</li>
                        <?php endif; ?>

                        <?php if (stripos(($data['storage'] ?? ''), 'personnel') !== false || stripos(($data['storage'] ?? ''), 'Clé USB') !== false): ?>
                            <li style="color: #c53030;"><strong>⚠️ Stockage :</strong> Évitez le stockage local. Privilégiez
                                l'ENT pour garantir la sécurité et la sauvegarde des données.</li>
                        <?php endif; ?>

                        <?php if (!empty($toolNames)): ?>
                            <li><strong>✅ Outils déclarés :</strong> Vos outils (
                                <?php echo implode(', ', $toolNames); ?>) sont bien identifiés dans votre bilan.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <h3>💡 Rappel des 3 règles d'or :</h3>
                <ol>
                    <li>Pas de données nominatives sur clé USB non chiffrée.</li>
                    <li>Verrouiller sa session (Win+L) à chaque déplacement.</li>
                    <li>Utiliser uniquement les outils validés par l'établissement.</li>
                </ol>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-bottom: 50px;">
            <a href="dashboard.php" class="btn" style="background-color: #718096;">← Retour au tableau de bord</a>
        </div>
    </div>
</body>

</html>