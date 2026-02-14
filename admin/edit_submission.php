<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) exit("Utilisateur non spécifié.");

// Charger l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$user_id]);
$targetUser = $stmt->fetch();
if (!$targetUser) exit("Utilisateur introuvable.");

$message = "";

// Charger le questionnaire (le plus récent)
$stmt = $pdo->prepare("SELECT id_questionnaire, statut FROM questionnaires WHERE id_utilisateur = ? ORDER BY date_creation DESC LIMIT 1");
$stmt->execute([$user_id]);
$q = $stmt->fetch();

if (!$q) {
     exit("Cet utilisateur n'a pas encore commencé de questionnaire.");
}
$id_questionnaire = $q['id_questionnaire'];

// Traitement des modifications par l'admin
if (isset($_POST['save_admin'])) {
    foreach ($_POST['reponse'] as $id_question => $valeur) {
        $stmt = $pdo->prepare("INSERT INTO reponses (id_questionnaire, id_question, reponse_utilisateur) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reponse_utilisateur=?");
        $stmt->execute([$id_questionnaire, $id_question, $valeur, $valeur]);
    }
    
    // Synchronisation avec le registre et l'IA
    updateRegistreFromBilan($id_questionnaire, $pdo);
    generateConseilsIA($id_questionnaire, $pdo);
    
    $message = "Questionnaire mis à jour par l'administrateur. Le registre et les conseils IA ont été synchronisés.";
}

// Charger les questions pour la fonction de l'utilisateur
$questions = $pdo->prepare("SELECT * FROM questions WHERE fonction_cible = ? OR fonction_cible IS NULL OR fonction_cible = ''");
$questions->execute([$targetUser['fonction']]);
$liste_questions = $questions->fetchAll();

// Charger les réponses
$stmt = $pdo->prepare("SELECT id_question, reponse_utilisateur FROM reponses WHERE id_questionnaire = ?");
$stmt->execute([$id_questionnaire]);
$reponses_existantes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Si aucune réponse trouvée, on tente de récupérer le dernier bilan validé pour l'affichage
if (empty($reponses_existantes)) {
    $stmt_prev = $pdo->prepare("SELECT id_questionnaire FROM questionnaires WHERE id_utilisateur = ? AND statut = 'complete' ORDER BY date_validation DESC LIMIT 1");
    $stmt_prev->execute([$user_id]);
    $prev_q = $stmt_prev->fetch();
    if ($prev_q) {
        $stmt_rep = $pdo->prepare("SELECT id_question, reponse_utilisateur FROM reponses WHERE id_questionnaire = ?");
        $stmt_rep->execute([$prev_q['id_questionnaire']]);
        $reponses_existantes = $stmt_rep->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Édition Questionnaire - Admin</title>
</head>
<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <h2>🛠️ Édition du questionnaire : <?php echo htmlspecialchars($targetUser['nom']); ?></h2>
        <p>Fonction : <strong><?php echo htmlspecialchars($targetUser['fonction']); ?></strong></p>
        <p>Statut actuel : <span class="badge"><?php echo $q['statut']; ?></span></p>

        <?php if ($message): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <?php foreach ($liste_questions as $q_data): ?>
                <div class="question-block" style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">
                    <p><strong><?php echo htmlspecialchars($q_data['question_txt']); ?></strong></p>
                    <?php if ($q_data['type_reponse'] == 'choix'): 
                        $options = json_decode($q_data['options_reponse']);
                    ?>
                        <?php foreach($options as $opt): ?>
                            <label style="display:inline-block; margin-right:15px;">
                                <input type="radio" name="reponse[<?php echo $q_data['id_question']; ?>]" value="<?php echo htmlspecialchars($opt); ?>" 
                                <?php echo (isset($reponses_existantes[$q_data['id_question']]) && $reponses_existantes[$q_data['id_question']] == $opt) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($opt); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php elseif ($q_data['type_reponse'] == 'texte'): ?>
                        <textarea name="reponse[<?php echo $q_data['id_question']; ?>]" style="width:100%; min-height:60px;"><?php echo htmlspecialchars($reponses_existantes[$q_data['id_question']] ?? ''); ?></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="actions">
                <button type="submit" name="save_admin" class="btn">Enregistrer les modifications</button>
                <a href="users.php" class="btn-danger" style="text-decoration:none; padding:10px 15px; border-radius:5px;">Annuler</a>
            </div>
        </form>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>
</html>
