<?php
include_once("includes/config.php");
include_once("includes/auth.php");
include_once("includes/functions.php");

// --- Déterminer l'utilisateur cible ---
// Si admin et user_id fourni → on génère le bilan d'un autre utilisateur
$isAdmin = ($currentUser['role'] == 'admin');
$targetUserId = $currentUser['id_utilisateur'];

if ($isAdmin && isset($_GET['user_id'])) {
    $targetUserId = (int) $_GET['user_id'];
}

// Charger l'utilisateur cible
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$targetUserId]);
$targetUser = $stmt->fetch();
if (!$targetUser)
    exit("Utilisateur introuvable.");

// Sécurité : un utilisateur non-admin ne peut voir que son propre bilan
if (!$isAdmin && $targetUserId != $currentUser['id_utilisateur']) {
    header("Location: dashboard.php");
    exit;
}

// --- Charger le questionnaire le plus récent ---
$stmt = $pdo->prepare("SELECT * FROM questionnaires WHERE id_utilisateur = ? ORDER BY date_creation DESC LIMIT 1");
$stmt->execute([$targetUserId]);
$questionnaire = $stmt->fetch();

if (!$questionnaire)
    exit("Aucun questionnaire trouvé pour cet utilisateur.");

$id_questionnaire = $questionnaire['id_questionnaire'];

// --- Charger TOUTES les questions (communes + statiques pour la fonction + IA) ---
$ia_tag = '[IA] ' . $targetUser['fonction'];
$stmt = $pdo->prepare("SELECT * FROM questions WHERE fonction_cible IS NULL OR fonction_cible = '' OR fonction_cible = ? OR fonction_cible = ? ORDER BY id_question");
$stmt->execute([$targetUser['fonction'], $ia_tag]);
$questions = $stmt->fetchAll();

// --- Charger les réponses ---
$stmt = $pdo->prepare("SELECT id_question, reponse_utilisateur FROM reponses WHERE id_questionnaire = ?");
$stmt->execute([$id_questionnaire]);
$reponses = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// --- Charger les conseils IA ---
$stmt = $pdo->prepare("SELECT type_fiche, observations FROM fiches WHERE id_utilisateur = ? AND type_fiche IN ('bonnes_pratiques', 'points_vigilance') ORDER BY date_generation DESC LIMIT 2");
$stmt->execute([$targetUserId]);
$conseils = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// --- Statistiques ---
$score = $questionnaire['score_conformite'] ?? 0;
$dateValidation = $questionnaire['date_validation'] ? date('d/m/Y', strtotime($questionnaire['date_validation'])) : 'Non validé';
$statut = $questionnaire['statut'];

// Compteurs
$totalQuestions = count($questions);
$answeredQuestions = 0;
foreach ($questions as $q) {
    if (!empty($reponses[$q['id_question']] ?? ''))
        $answeredQuestions++;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Bilan RGPD -
        <?php echo htmlspecialchars($targetUser['nom']); ?>
    </title>
    <style>
        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #2d3748;
            background: white;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
        }

        /* En-tête */
        .header {
            background: linear-gradient(135deg, #3182ce, #2b6cb0);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.85;
            font-size: 14px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .meta-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 10px;
            border-radius: 6px;
        }

        .meta-item strong {
            display: block;
            font-size: 18px;
        }

        /* Sections */
        .section {
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            background: #f7fafc;
            padding: 12px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: bold;
            font-size: 15px;
        }

        .section-header.ia {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .section-body {
            padding: 15px 20px;
        }

        /* Questions */
        .question {
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .question:last-child {
            border-bottom: none;
        }

        .question-text {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .question-answer {
            color: #4a5568;
            padding-left: 15px;
        }

        .question-answer.empty {
            color: #a0aec0;
            font-style: italic;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75em;
            font-weight: bold;
        }

        .badge-ia {
            background: #e9d8fd;
            color: #553c9a;
        }

        .badge-common {
            background: #bee3f8;
            color: #2b6cb0;
        }

        /* Conseils */
        .advice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .advice-block {
            padding: 15px;
            border-radius: 8px;
        }

        .advice-good {
            background: #f0fff4;
            border: 1px solid #c6f6d5;
        }

        .advice-warn {
            background: #fffaf0;
            border: 1px solid #feebc8;
        }

        .advice-block h4 {
            margin-bottom: 8px;
        }

        .advice-block p {
            font-size: 0.9em;
            line-height: 1.6;
            white-space: pre-line;
        }

        /* Score */
        .score-bar {
            height: 20px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .score-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s;
        }

        /* Boutons */
        .btn-print {
            background: #3182ce;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-back {
            background: #718096;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            color: #a0aec0;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- BOUTONS (non imprimés) -->
        <div class="no-print" style="margin-bottom: 20px; display: flex; gap: 10px;">
            <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Sauvegarder en PDF</button>
            <a href="<?php echo $isAdmin ? 'admin/users.php' : 'rgpd_view.php'; ?>" class="btn-back">← Retour</a>
        </div>

        <!-- EN-TÊTE -->
        <div class="header">
            <h1>📋 Bilan RGPD Complet</h1>
            <p>
                <?php echo htmlspecialchars($targetUser['nom']); ?> —
                <?php echo htmlspecialchars($targetUser['fonction']); ?>
            </p>
            <div class="meta-grid">
                <div class="meta-item">
                    <small>Score de conformité</small>
                    <strong>
                        <?php echo $score; ?>%
                    </strong>
                </div>
                <div class="meta-item">
                    <small>Questions répondues</small>
                    <strong>
                        <?php echo $answeredQuestions; ?>/
                        <?php echo $totalQuestions; ?>
                    </strong>
                </div>
                <div class="meta-item">
                    <small>Statut / Date</small>
                    <strong>
                        <?php echo $statut == 'complete' ? '✅ Validé' : '🔄 En cours'; ?>
                    </strong>
                    <small>
                        <?php echo $dateValidation; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- SCORE VISUEL -->
        <div class="section">
            <div class="section-header">📊 Niveau de conformité estimé</div>
            <div class="section-body">
                <div class="score-bar">
                    <?php
                    $scoreColor = $score >= 80 ? '#48bb78' : ($score >= 50 ? '#ecc94b' : '#f56565');
                    ?>
                    <div class="score-fill"
                        style="width: <?php echo $score; ?>%; background: <?php echo $scoreColor; ?>;"></div>
                </div>
                <p style="text-align: center; font-weight: bold; color: <?php echo $scoreColor; ?>;">
                    <?php echo $score; ?>% —
                    <?php echo $score >= 80 ? 'Bonne conformité' : ($score >= 50 ? 'Conformité moyenne — des améliorations sont nécessaires' : 'Non conforme — actions correctives urgentes'); ?>
                </p>
            </div>
        </div>

        <!-- QUESTIONS COMMUNES -->
        <div class="section">
            <div class="section-header">📝 Questions communes (tous les postes)</div>
            <div class="section-body">
                <?php foreach ($questions as $q): ?>
                    <?php if (empty($q['fonction_cible']) || $q['fonction_cible'] === ''): ?>
                        <div class="question">
                            <div class="question-text">
                                <?php echo htmlspecialchars($q['question_txt']); ?>
                            </div>
                            <?php $rep = $reponses[$q['id_question']] ?? ''; ?>
                            <div class="question-answer <?php echo empty($rep) ? 'empty' : ''; ?>">
                                →
                                <?php echo empty($rep) ? 'Non répondu' : htmlspecialchars($rep); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- QUESTIONS SPÉCIFIQUES (IA) -->
        <?php
        $aiQuestions = array_filter($questions, fn($q) => strpos($q['fonction_cible'] ?? '', '[IA]') === 0);
        if (!empty($aiQuestions)):
            ?>
            <div class="section page-break">
                <div class="section-header ia">🤖 Questions personnalisées pour :
                    <?php echo htmlspecialchars($targetUser['fonction']); ?>
                </div>
                <div class="section-body">
                    <?php foreach ($aiQuestions as $q): ?>
                        <div class="question">
                            <div class="question-text">
                                <?php echo htmlspecialchars($q['question_txt']); ?>
                            </div>
                            <?php $rep = $reponses[$q['id_question']] ?? ''; ?>
                            <div class="question-answer <?php echo empty($rep) ? 'empty' : ''; ?>">
                                →
                                <?php echo empty($rep) ? 'Non répondu' : htmlspecialchars($rep); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- QUESTIONS STATIQUES SPÉCIFIQUES -->
        <?php
        $staticRoleQuestions = array_filter($questions, fn($q) => !empty($q['fonction_cible']) && $q['fonction_cible'] !== '' && strpos($q['fonction_cible'], '[IA]') !== 0);
        if (!empty($staticRoleQuestions)):
            ?>
            <div class="section">
                <div class="section-header">🏷️ Questions spécifiques au poste</div>
                <div class="section-body">
                    <?php foreach ($staticRoleQuestions as $q): ?>
                        <div class="question">
                            <div class="question-text">
                                <?php echo htmlspecialchars($q['question_txt']); ?>
                            </div>
                            <?php $rep = $reponses[$q['id_question']] ?? ''; ?>
                            <div class="question-answer <?php echo empty($rep) ? 'empty' : ''; ?>">
                                →
                                <?php echo empty($rep) ? 'Non répondu' : htmlspecialchars($rep); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- CONSEILS IA -->
        <div class="section page-break">
            <div class="section-header">🤖 Conseils & Recommandations</div>
            <div class="section-body">
                <div class="advice-grid">
                    <div class="advice-block advice-good">
                        <h4>✅ Bonnes pratiques</h4>
                        <p>
                            <?php echo nl2br(htmlspecialchars($conseils['bonnes_pratiques'] ?? 'Complétez le questionnaire pour recevoir des conseils.')); ?>
                        </p>
                    </div>
                    <div class="advice-block advice-warn">
                        <h4>⚠️ Points de vigilance</h4>
                        <p>
                            <?php echo nl2br(htmlspecialchars($conseils['points_vigilance'] ?? 'Aucun point de vigilance majeur détecté.')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PIED DE PAGE -->
        <div class="footer">
            Plateforme Providence — La Providence - La Salle<br>
            Document généré le
            <?php echo date('d/m/Y à H:i'); ?> — Usage interne — Confidentiel
        </div>
    </div>
</body>

</html>