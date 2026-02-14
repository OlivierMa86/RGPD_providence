<?php
include("includes/config.php");
include("includes/auth.php");

echo "<h2>Diagnostic Avancé : " . htmlspecialchars($currentUser['nom']) . "</h2>";
echo "<p>Votre fonction : <b>" . htmlspecialchars($currentUser['fonction']) . "</b></p>";

// 1. Vérification des questions disponibles
$stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE fonction_cible = ? OR fonction_cible IS NULL OR fonction_cible = ''");
$stmt->execute([$currentUser['fonction']]);
$nb_visibles = $stmt->fetchColumn();

echo "<h3>1. Accessibilité des questions</h3>";
echo "<p>Nombre de questions visibles pour votre fonction : <b>$nb_visibles</b></p>";

if ($nb_visibles == 0) {
    echo "<p style='color:red;'>⚠️ ATTENTION : Aucune question n'est configurée pour votre fonction actuelle. Le formulaire sera vide, ce qui explique pourquoi rien n'est enregistré.</p>";
}

// 2. Aperçu des fonctions cibles dans la base
echo "<h3>2. Fonctions cibles configurées dans la table 'questions'</h3>";
$res = $pdo->query("SELECT DISTINCT fonction_cible FROM questions")->fetchAll(PDO::FETCH_COLUMN);
echo "<ul>";
foreach ($res as $f) {
    echo "<li>" . (empty($f) ? "[Vide/Toutes]" : htmlspecialchars($f)) . "</li>";
}
echo "</ul>";

// 3. Test de sauvegarde manuel
echo "<h3>3. Test d'écriture immédiat</h3>";
if (isset($_GET['test_save'])) {
    $q_id = $pdo->query("SELECT id_questionnaire FROM questionnaires LIMIT 1")->fetchColumn();
    $quest_id = $pdo->query("SELECT id_question FROM questions LIMIT 1")->fetchColumn();
    if ($q_id && $quest_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reponses (id_questionnaire, id_question, reponse_utilisateur) VALUES (?, ?, 'Test Diagnostic') ON DUPLICATE KEY UPDATE reponse_utilisateur='Test Diagnostic Update'");
            $stmt->execute([$q_id, $quest_id]);
            echo "<p style='color:green;'>✅ Écriture réussie dans la table 'reponses' !</p>";
        } catch (Exception $e) {
            echo "<p style='color:red;'>❌ Échec d'écriture : " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p><a href='?test_save=1'>Cliquez ici pour tester si la base accepte l'écriture</a></p>";
}
?>