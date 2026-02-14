<?php
include("includes/config.php");
include("includes/auth.php");

$message = "";

// 1. Chercher le questionnaire en cours le plus récent
$stmt = $pdo->prepare("SELECT id_questionnaire FROM questionnaires WHERE id_utilisateur = ? AND statut = 'en_cours' ORDER BY date_creation DESC LIMIT 1");
$stmt->execute([$currentUser['id_utilisateur']]);
$q = $stmt->fetch();

if (!$q) {
    // Si pas de questionnaire en cours, on regarde si on doit en créer un nouveau ou si on veut juste voir le dernier complet
    $stmt_last = $pdo->prepare("SELECT id_questionnaire, statut FROM questionnaires WHERE id_utilisateur = ? ORDER BY date_creation DESC LIMIT 1");
    $stmt_last->execute([$currentUser['id_utilisateur']]);
    $last_q = $stmt_last->fetch();

    if ($last_q && $last_q['statut'] == 'complete' && !isset($_GET['new'])) {
        // L'utilisateur a déjà complété un bilan, on lui propose d'en créer un nouveau ou d'éditer le dernier
        // Pour simplifier selon la demande "modifier mon questionnaire", on va lui permettre d'éditer le dernier même s'il est complet
        // OU on crée un nouveau "en_cours" pré-rempli. Choisissons la création auto pour garder l'historique.
        $stmt = $pdo->prepare("INSERT INTO questionnaires (id_utilisateur, fonction) VALUES (?, ?)");
        $stmt->execute([$currentUser['id_utilisateur'], $currentUser['fonction']]);
        $id_questionnaire = $pdo->lastInsertId();
        
        // Copie immédiate des réponses
        $pdo->prepare("INSERT IGNORE INTO reponses (id_questionnaire, id_question, reponse_utilisateur) 
                       SELECT ?, id_question, reponse_utilisateur FROM reponses WHERE id_questionnaire = ?")
            ->execute([$id_questionnaire, $last_q['id_questionnaire']]);
    } else if (!$last_q) {
        // Premier questionnaire
        $stmt = $pdo->prepare("INSERT INTO questionnaires (id_utilisateur, fonction) VALUES (?, ?)");
        $stmt->execute([$currentUser['id_utilisateur'], $currentUser['fonction']]);
        $id_questionnaire = $pdo->lastInsertId();
    } else {
        $id_questionnaire = $last_q['id_questionnaire'];
    }
} else {
    $id_questionnaire = $q['id_questionnaire'];
}

// Sécurité : si le questionnaire en cours est vide, on tente un pré-remplissage de sauvetage
$stmt_check = $pdo->prepare("SELECT COUNT(*) FROM reponses WHERE id_questionnaire = ?");
$stmt_check->execute([$id_questionnaire]);
if ($stmt_check->fetchColumn() == 0) {
    $stmt_prev = $pdo->prepare("SELECT id_questionnaire FROM questionnaires WHERE id_utilisateur = ? AND statut = 'complete' ORDER BY date_validation DESC LIMIT 1");
    $stmt_prev->execute([$currentUser['id_utilisateur']]);
    $prev = $stmt_prev->fetch();
    if ($prev && $prev['id_questionnaire'] != $id_questionnaire) {
        $pdo->prepare("INSERT IGNORE INTO reponses (id_questionnaire, id_question, reponse_utilisateur) 
                       SELECT ?, id_question, reponse_utilisateur FROM reponses WHERE id_questionnaire = ?")
            ->execute([$id_questionnaire, $prev['id_questionnaire']]);
    }
}

// Traitement des réponses (Sauvegarde et/ou Validation)
if (isset($_POST['save']) || isset($_POST['validate'])) {
    try {
        if (isset($_POST['reponse']) && is_array($_POST['reponse'])) {
            foreach ($_POST['reponse'] as $id_question => $valeur) {
                $stmt = $pdo->prepare("INSERT INTO reponses (id_questionnaire, id_question, reponse_utilisateur) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reponse_utilisateur=?");
                $stmt->execute([$id_questionnaire, $id_question, $valeur, $valeur]);
            }
        }
        
        if (isset($_POST['save'])) {
            updateRegistreFromBilan($id_questionnaire, $pdo);
            generateConseilsIA($id_questionnaire, $pdo);
            $message = "✅ Vos réponses ont été enregistrées. Les conseils et liens de conformité ont été mis à jour sur votre tableau de bord.";
        }
        
        if (isset($_POST['validate'])) {
            $pdo->prepare("UPDATE questionnaires SET statut='complete', date_validation=NOW() WHERE id_questionnaire=?")->execute([$id_questionnaire]);
            updateRegistreFromBilan($id_questionnaire, $pdo);
            generateConseilsIA($id_questionnaire, $pdo);
            header("Location: dashboard.php?success=bilan_valide");
            exit;
        }
    } catch (PDOException $e) {
        $message = "❌ Erreur technique lors de l'enregistrement : " . $e->getMessage();
    }
}

// Charger les questions
$fonction = $currentUser['fonction'];
$stmt_q = $pdo->prepare("SELECT * FROM questions WHERE fonction_cible = ? 
                          OR (fonction_cible = 'Direction' AND ? = 'Chef d’établissement')
                          OR fonction_cible IS NULL OR fonction_cible = ''");
$stmt_q->execute([$fonction, $fonction]);
$liste_questions = $stmt_q->fetchAll();

// Charger les réponses existantes
$stmt = $pdo->prepare("SELECT id_question, reponse_utilisateur FROM reponses WHERE id_questionnaire = ?");
$stmt->execute([$id_questionnaire]);
$reponses_existantes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Questionnaire RGPD</title>
</head>
<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <h2>📝 Questionnaire RGPD</h2>
        <p>Merci de répondre aux questions relatives à votre fonction : <strong><?php echo htmlspecialchars($currentUser['fonction']); ?></strong></p>

        <?php if ($message): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <?php foreach ($liste_questions as $q): ?>
                <div class="question-block">
                    <p><strong><?php echo htmlspecialchars($q['question_txt']); ?></strong></p>
                    <?php if ($q['type_reponse'] == 'choix'): 
                        $options = json_decode($q['options_reponse']);
                    ?>
                        <?php foreach($options as $opt): ?>
                            <label>
                                <input type="radio" name="reponse[<?php echo $q['id_question']; ?>]" value="<?php echo htmlspecialchars($opt); ?>" 
                                <?php echo (isset($reponses_existantes[$q['id_question']]) && $reponses_existantes[$q['id_question']] == $opt) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($opt); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php elseif ($q['type_reponse'] == 'texte'): ?>
                        <textarea name="reponse[<?php echo $q['id_question']; ?>]"><?php echo htmlspecialchars($reponses_existantes[$q['id_question']] ?? ''); ?></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="actions">
                <button type="submit" name="save">Enregistrer sans valider</button>
                <button type="submit" name="validate" class="btn-primary" onclick="return confirm('Valider définitivement ?')">Valider mon bilan</button>
            </div>
        </form>
    </div>

    <?php include("includes/footer.php"); ?>
</body>
</html>