<?php
include("includes/config.php");
include("includes/auth.php");

$message = "";

// Traitement de la mise à jour de la fonction
if (isset($_POST['update_role']) && !empty($_POST['new_role'])) {
    $new_role = trim($_POST['new_role']); // update_role
    // 1. Mise à jour en base
    $stmt_upd = $pdo->prepare("UPDATE utilisateurs SET fonction = ? WHERE id_utilisateur = ?");
    $stmt_upd->execute([$new_role, $currentUser['id_utilisateur']]);
    
    // 2. Mise à jour de la session
    $_SESSION['user']['fonction'] = $new_role;
    $currentUser['fonction'] = $new_role; // Mise à jour immédiate de la variable locale
    
    // 3. Mise à jour du questionnaire EN COURS s'il existe
    $pdo->prepare("UPDATE questionnaires SET fonction = ? WHERE id_utilisateur = ? AND statut = 'en_cours'")
        ->execute([$new_role, $currentUser['id_utilisateur']]);

    $message = "✅ Votre fonction a été mise à jour. Le questionnaire s'est adapté.";
}

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
                if (!is_numeric($id_question)) continue; // Sécurité : ignorer les clés non-numériques
                $stmt = $pdo->prepare("INSERT INTO reponses (id_questionnaire, id_question, reponse_utilisateur) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reponse_utilisateur=?");
                $stmt->execute([$id_questionnaire, (int)$id_question, $valeur, $valeur]);
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

// Charger les questions COMMUNES (base de données) — applicables à tous les postes
$stmt_q = $pdo->prepare("SELECT * FROM questions WHERE fonction_cible IS NULL OR fonction_cible = ''");
$stmt_q->execute();
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
        <div style="background: #edf2f7; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
            <form method="post" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <label style="font-weight: bold; color: #4a5568;">Votre fonction actuelle :</label>
                <input type="text" name="new_role" value="<?php echo htmlspecialchars($currentUser['fonction']); ?>" 
                       style="padding: 8px; border: 1px solid #cbd5e0; border-radius: 4px; flex: 1; min-width: 250px;" 
                       placeholder="Ex: Enseignant, CPE, Direction...">
                <button type="submit" name="update_role" class="btn" style="padding: 8px 15px; font-size: 0.9em; background: #4a5568;">🔄 Mettre à jour</button>
            </form>
            <small style="color: #718096; display: block; margin-top: 5px;">
                💡 <em>Astuce : Séparez les rôles par une virgule si vous en avez plusieurs (ex: "Enseignant, Référent Numérique"). Le questionnaire s'adaptera automatiquement.</em>
            </small>
        </div>

        <?php if ($message): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <?php foreach ($liste_questions as $q): ?>
                <div class="question-block" id="q-block-<?php echo $q['id_question']; ?>">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                        <p style="margin-top:0;"><strong><?php echo htmlspecialchars($q['question_txt']); ?></strong></p>
                        <button type="button" class="btn-ai-help" onclick="askAiHelp(<?php echo $q['id_question']; ?>, `<?php echo addslashes($q['question_txt']); ?>`)" style="background:none; border:none; cursor:pointer; font-size:1.2em;" title="Demander de l'aide à l'IA">✨</button>
                    </div>
                    
                    <div id="ai-advice-<?php echo $q['id_question']; ?>" style="display:none; background:#f0f4ff; border-left:4px solid #667eea; padding:10px; margin-bottom:10px; border-radius:4px; font-size:0.9em; color:#2d3748;">
                        <em>L'IA réfléchit...</em>
                    </div>

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
            
            <script>
            function askAiHelp(id, text) {
                const zone = document.getElementById('ai-advice-' + id);
                if(zone.style.display === 'block') {
                    zone.style.display = 'none'; // Toggle off
                    return;
                }
                
                zone.style.display = 'block';
                zone.innerHTML = '🔄 <em>L\'IA analyse votre profil et la question...</em>';
                
                const formData = new FormData();
                formData.append('question_id', id);
                formData.append('question_text', text);
                
                fetch('ajax_ai_help.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        zone.innerHTML = '<strong>💡 Conseil IA :</strong><br>' + d.advice;
                    } else {
                        zone.innerHTML = '⚠️ ' + (d.message || 'Erreur inconnue');
                    }
                })
                .catch(e => {
                    console.error(e);
                    zone.innerHTML = '❌ Erreur technique (voir console)';
                });
            }
            </script>

            <!-- BLOC AI : Questions spécifiques générées par l'IA -->
            <div id="ai-questions-section" style="margin-top: 30px;">
                <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4em;">🤖</span>
                    <div>
                        <strong style="font-size: 1.1em;">Questions personnalisées pour : <?php echo htmlspecialchars($currentUser['fonction']); ?></strong><br>
                        <small style="opacity: 0.85;">Générées par l'IA en fonction de votre poste</small>
                    </div>
                </div>
                <div id="ai-questions-container" style="border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px; padding: 20px; background: #faf5ff;">
                    <p id="ai-loading" style="text-align: center; color: #667eea; font-weight: bold;">
                        🔄 <em>L'IA génère des questions adaptées à votre profil...</em>
                    </p>
                </div>
            </div>

            <script>
            // Chargement des questions IA au chargement de la page
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('ai-questions-container');
                const loading = document.getElementById('ai-loading');
                const fonction = '<?php echo addslashes($currentUser["fonction"]); ?>';
                
                const formData = new FormData();
                formData.append('fonction', fonction);
                
                fetch('ajax_generate_questions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.questions && data.questions.length > 0) {
                        // Réponses existantes (chargées côté PHP)
                        const savedResponses = <?php echo json_encode($reponses_existantes); ?>;
                        let html = '';
                        const sourceTag = data.source === 'db' ? 
                            '<small style="color:#a0aec0; display:block; margin-bottom:15px;">📦 Questions enregistrées en base de données</small>' : 
                            '<small style="color:#38a169; display:block; margin-bottom:15px;">✨ Questions fraîchement générées par l\'IA</small>';
                        html += sourceTag;

                        data.questions.forEach((q) => {
                            const qId = q.id_question;
                            const savedVal = savedResponses[qId] || '';
                            const cat = q.categorie ? `<span style="background:#e9d8fd; color:#553c9a; padding:2px 8px; border-radius:10px; font-size:0.75em; font-weight:bold;">${q.categorie}</span>` : '';
                            
                            html += `<div class="question-block" style="background:white; padding:15px; border-radius:8px; margin-bottom:12px; border:1px solid #e2e8f0;">`;
                            html += `<div style="display:flex; justify-content:space-between; align-items:start;">`;
                            html += `<p style="margin-top:0;"><strong>${q.question}</strong> ${cat}</p>`;
                            html += `<button type="button" onclick="askAiHelp('${qId}', \`${q.question.replace(/`/g, "'")}\`)" style="background:none; border:none; cursor:pointer; font-size:1.2em;" title="Demander de l'aide à l'IA">✨</button>`;
                            html += `</div>`;
                            html += `<div id="ai-advice-${qId}" style="display:none; background:#f0f4ff; border-left:4px solid #667eea; padding:10px; margin-bottom:10px; border-radius:4px; font-size:0.9em; color:#2d3748;"><em>L'IA réfléchit...</em></div>`;
                            
                            if (q.type === 'choix' && q.options) {
                                q.options.forEach(opt => {
                                    const checked = (savedVal === opt) ? 'checked' : '';
                                    html += `<label style="display:block; margin-bottom:5px;">`;
                                    html += `<input type="radio" name="reponse[${qId}]" value="${opt}" ${checked}> ${opt}`;
                                    html += `</label>`;
                                });
                            } else {
                                html += `<textarea name="reponse[${qId}]" rows="2" style="width:100%; padding:8px; border:1px solid #cbd5e0; border-radius:4px;">${savedVal}</textarea>`;
                            }
                            html += `</div>`;
                        });
                        
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p style="text-align:center; color:#a0aec0;">Aucune question supplémentaire générée pour votre profil.</p>';
                    }
                })
                .catch(e => {
                    console.error(e);
                    container.innerHTML = '<p style="color:#e53e3e; text-align:center;">❌ Erreur lors du chargement des questions IA.</p>';
                });
            });
            </script>

            <div class="actions" style="margin-top: 20px;">
                <button type="submit" name="save">Enregistrer sans valider</button>
                <button type="submit" name="validate" class="btn-primary" onclick="return confirm('Valider définitivement ?')">Valider mon bilan</button>
            </div>
        </form>
    </div>

    <?php include("includes/footer.php"); ?>
</body>
</html>