<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// Récupération des documents PPMS
$docs = $pdo->query("SELECT * FROM ppms_documents")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

// Récupération des exercices
$exercices = $pdo->query("SELECT * FROM ppms_exercices ORDER BY date_prevue DESC")->fetchAll();

// Récupération des procédures partagées
$procedures = $pdo->query("SELECT * FROM ppms_procedures ORDER BY date_publication DESC")->fetchAll();

// Récupération des fiches de sécurité
$fiches = $pdo->query("SELECT * FROM ppms_fiches")->fetchAll(PDO::FETCH_ASSOC);

// Calcul des rappels (Logique simplifiée)
$currentMonth = date('m');
$currentYear = date('Y');
$trimester = ceil($currentMonth / 3);

$fireThisTrimester = false;
$intrusionThisYear = false;

foreach ($exercices as $ex) {
    if ($ex['statut'] === 'Réalisé') {
        $exDate = strtotime($ex['date_realisee']);
        $exMonth = date('m', $exDate);
        $exYear = date('Y', $exDate);
        
        if ($ex['type_exercice'] === 'Incendie' && ceil($exMonth / 3) == $trimester && $exYear == $currentYear) {
            $fireThisTrimester = true;
        }
        
        // Année scolaire (ex: Sept 2023 - Aout 2024)
        $schoolYearStart = ($currentMonth >= 9) ? $currentYear : $currentYear - 1;
        if ($ex['type_exercice'] === 'Intrusion_Attentat' && $exYear >= $schoolYearStart) {
            $intrusionThisYear = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
    <title>Dashboard PPMS - Plateforme Providence</title>
    <style>
        .ppms-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        .doc-item {
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #edf2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .status-A_Jour { background: #c6f6d5; color: #22543d; }
        .status-A_Reviser { background: #feebc8; color: #744210; }
        .status-Manquant { background: #fed7d7; color: #822727; }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            width: 500px;
        }
        .procedure-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .fiche-editor-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/admin_nav.php"); ?>

    <div class="container">
        <div class="welcome-header" style="background: linear-gradient(135deg, #4a5568, #2d3748);">
            <h2>🌪️ Gestion PPMS (Plan Particulier de Mise en Sûreté)</h2>
            <p>Outils de pilotage pour la sécurité et la prévention des risques majeurs.</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert success" style="background: #f0fff4; color: #276745; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #9ae6b4;">
                ✅ Opération réussie !
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert error" style="background: #fff5f5; color: #c53030; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #feb2b2;">
                ❌ Une erreur est survenue lors de l'opération.
            </div>
        <?php endif; ?>

        <div class="ppms-grid">
            <div class="left-col">
                <div class="section">
                    <h3>📋 Documents Obligatoires</h3>
                    <?php
                    $mandatoryDocs = [
                        'PPMS_Unique' => '1. PPMS Unique (Validé CA)',
                        'Registre_Securite' => '2. Registre de Sécurité (ERP)',
                        'DUER' => '3. DUER (Risques Salariés)',
                        'Carnet_Maintenance' => '4. Carnet de Maintenance'
                    ];

                    foreach ($mandatoryDocs as $key => $label):
                        $doc = $docs[$key] ?? null;
                    ?>
                        <div class="doc-item">
                            <div>
                                <strong><?php echo $label; ?></strong><br>
                                <small style="color: #718096;">
                                    <?php echo $doc ? "Mis à jour le " . date('d/m/Y', strtotime($doc['date_upload'])) : "Non déposé"; ?>
                                </small>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <?php if ($doc): ?>
                                    <span class="status-badge status-<?php echo $doc['statut']; ?>">
                                        <?php echo str_replace('_', ' ', $doc['statut']); ?>
                                    </span>
                                    <a href="<?php echo $doc['chemin_fichier']; ?>" target="_blank" class="btn" style="padding: 5px 10px; font-size: 0.8em; background: #718096;">👁️ Voir</a>
                                <?php else: ?>
                                    <span class="status-badge status-Manquant">Manquant</span>
                                <?php endif; ?>
                                <button onclick="openUploadModal('<?php echo $key; ?>')" class="btn" style="padding: 5px 10px; font-size: 0.8em;">⬆️ Déposer</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="section">
                    <h3>📢 Procédures partagées (Personnel)</h3>
                    <p style="font-size: 0.9em; color: #718096; margin-bottom: 15px;">Ces documents sont accessibles par tous les professeurs sur leur tableau de bord.</p>
                    
                    <?php foreach ($procedures as $proc): ?>
                        <div class="procedure-card">
                            <div>
                                <strong style="color: #2d3748;"><?php echo $proc['titre']; ?></strong><br>
                                <small style="color: #718096;">Publié le <?php echo date('d/m/Y', strtotime($proc['date_publication'])); ?></small>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="<?php echo $proc['chemin_fichier']; ?>" target="_blank" class="btn" style="padding: 5px 10px; font-size: 0.8em; background: #3182ce;">👁️</a>
                                <form action="ppms_actions.php" method="POST" onsubmit="return confirm('Supprimer cette procédure ?');">
                                    <input type="hidden" name="action" value="delete_procedure">
                                    <input type="hidden" name="id_procedure" value="<?php echo $proc['id_procedure']; ?>">
                                    <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8em; background: #e53e3e;">🗑️</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($procedures)): ?>
                        <p style="text-align: center; color: #a0aec0; padding: 10px;">Aucune procédure partagée pour le moment.</p>
                    <?php endif; ?>
                    
                    <button onclick="document.getElementById('modalProcedure').style.display='block'" class="btn" style="width: 100%; margin-top: 10px; background: #edf2f7; color: #2d3748; border: 1px dashed #cbd5e0;">+ Ajouter une procédure pour les profs</button>
                </div>

                <div class="section">
                    <h3>📄 Fiches de sécurité interactives</h3>
                    <p style="font-size: 0.9em; color: #718096; margin-bottom: 15px;">Éditez ici les consignes officielles affichées aux professeurs.</p>
                    <?php foreach ($fiches as $f): ?>
                        <div class="fiche-editor-card">
                            <div>
                                <strong style="color: #2d3748;"><?php echo $f['titre']; ?></strong><br>
                                <small style="color: #718096;">Dernière mise à jour : <?php echo $f['last_update']; ?></small>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="../ppms_view.php" target="_blank" class="btn" style="padding: 5px 10px; font-size: 0.8em; background: #3182ce;">👁️ Voir</a>
                                <button onclick='openFicheModal(<?php echo htmlspecialchars(json_encode($f), ENT_QUOTES, "UTF-8"); ?>)' class="btn" style="padding: 5px 15px; font-size: 0.8em; background: #ed8936;">✏️ Modifier</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="section">
                    <h3>🚨 Historique & Programme des Exercices</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f7fafc; text-align: left;">
                                <th style="padding: 12px; border-bottom: 2px solid #edf2f7;">Type</th>
                                <th style="padding: 12px; border-bottom: 2px solid #edf2f7;">Date prévue</th>
                                <th style="padding: 12px; border-bottom: 2px solid #edf2f7;">Réalisation</th>
                                <th style="padding: 12px; border-bottom: 2px solid #edf2f7;">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exercices as $ex): ?>
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid #edf2f7;"><?php echo str_replace('_', ' ', $ex['type_exercice']); ?></td>
                                    <td style="padding: 12px; border-bottom: 1px solid #edf2f7;"><?php echo date('d/m/Y', strtotime($ex['date_prevue'])); ?></td>
                                    <td style="padding: 12px; border-bottom: 1px solid #edf2f7;"><?php echo $ex['date_realisee'] ? date('d/m/Y', strtotime($ex['date_realisee'])) : '-'; ?></td>
                                    <td style="padding: 12px; border-bottom: 1px solid #edf2f7;">
                                        <span class="status-badge <?php echo $ex['statut'] == 'Réalisé' ? 'status-A_Jour' : 'status-A_Reviser'; ?>">
                                            <?php echo $ex['statut']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($exercices)): ?>
                                <tr><td colspan="4" style="text-align:center; padding: 20px; color: #718096;">Aucun exercice enregistré.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="right-col">
                <div class="section" style="background: <?php echo (!$fireThisTrimester || !$intrusionThisYear) ? '#fff5f5' : '#f0fff4'; ?>; border-left: 4px solid <?php echo (!$fireThisTrimester || !$intrusionThisYear) ? '#e53e3e' : '#38a169'; ?>;">
                    <h3>💡 Rappels Automatiques</h3>
                    <ul style="padding-left: 20px; list-style-type: none;">
                        <li style="margin-bottom: 15px;">
                            <strong>🔥 Alerte Incendie :</strong><br>
                            <small>Fréquence : 1 par trimestre.</small><br>
                            <?php if ($fireThisTrimester): ?>
                                <strong style="color: #2f855a;">✅ Effectué ce trimestre</strong>
                            <?php else: ?>
                                <strong style="color: #c53030;">❌ À prévoir pour ce trimestre</strong>
                            <?php endif; ?>
                        </li>
                        <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
                        <li style="margin-bottom: 15px;">
                            <strong>🛡️ Alerte Intrusion :</strong><br>
                            <small>Fréquence : 1 par année scolaire.</small><br>
                            <?php if ($intrusionThisYear): ?>
                                <strong style="color: #2f855a;">✅ Effectué cette année</strong>
                            <?php else: ?>
                                <strong style="color: #c53030;">❌ À programmer / réaliser</strong>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>

                <div class="section">
                    <h3>🚀 Actions rapides</h3>
                    <button onclick="document.getElementById('modalExercice').style.display='block'" class="btn" style="width: 100%; margin-bottom: 10px; background: #4a5568;">🚨 Programmer un exercice</button>
                    <a href="ppms_documentation.php" class="btn btn-secondary" style="width: 100%; margin-bottom: 10px;">📚 Documentation PPMS</a>
                    <a href="legal_watch.php?context=ppms" class="btn" style="width: 100%; margin-bottom: 10px; background: linear-gradient(135deg, #667eea, #764ba2);">✨ Veille Juridique Assistée par IA</a>
                    <a href="index.php" class="btn" style="width: 100%; background: #a0aec0;">🔙 Retour Administration</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload -->
    <div id="modalUpload" class="modal">
        <div class="modal-content">
            <h3 id="uploadTitle">Déposer un document</h3>
            <form action="ppms_actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_doc">
                <input type="hidden" name="type_doc" id="uploadType">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Fichier (PDF recommandé) :</label>
                    <input type="file" name="file" required style="width: 100%;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Observations :</label>
                    <textarea name="observations" rows="3" style="width: 100%; border-radius: 8px; border: 1px solid #ddd; padding: 10px;"></textarea>
                </div>
                
                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="submit" class="btn">Enregistrer</button>
                    <button type="button" onclick="this.closest('.modal').style.display='none'" class="btn" style="background:#718096;">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Procedure -->
    <div id="modalProcedure" class="modal">
        <div class="modal-content">
            <h3>Ajouter une procédure (PDF)</h3>
            <form action="ppms_actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_procedure">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Titre de la procédure :</label>
                    <input type="text" name="titre" placeholder="ex: Plan d'évacuation 2024" required style="width: 100%;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Fichier PDF :</label>
                    <input type="file" name="file" accept=".pdf" required style="width: 100%;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description courte :</label>
                    <textarea name="description" rows="2" style="width: 100%; border-radius: 8px; border: 1px solid #ddd; padding: 10px;"></textarea>
                </div>
                
                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="submit" class="btn">Publier pour les profs</button>
                    <button type="button" onclick="this.closest('.modal').style.display='none'" class="btn" style="background:#718096;">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Exercice -->
    <div id="modalExercice" class="modal">
        <div class="modal-content">
            <h3>Programmer / Enregistrer un exercice</h3>
            <form action="ppms_actions.php" method="POST">
                <input type="hidden" name="action" value="add_exercice">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Type d'exercice :</label>
                    <select name="type_exercice" required style="width: 100%;">
                        <option value="Incendie">🔥 Alerte Incendie</option>
                        <option value="Intrusion_Attentat">🛡️ Alerte Intrusion / Attentat</option>
                        <option value="Risques_Majeurs">🌪️ Risques Majeurs (Confinement)</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date prévue :</label>
                    <input type="date" name="date_prevue" required style="width: 100%;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date réalisée (si déjà fait) :</label>
                    <input type="date" name="date_realisee" style="width: 100%;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Statut :</label>
                    <select name="statut" style="width: 100%;">
                        <option value="Programmé">Programmé</option>
                        <option value="Réalisé">Réalisé (Déjà clôturé)</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Observations :</label>
                    <textarea name="observations" rows="3" style="width: 100%; border-radius: 8px; border: 1px solid #ddd; padding: 10px;"></textarea>
                </div>
                
                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="submit" class="btn">Confirmer</button>
                    <button type="button" onclick="this.closest('.modal').style.display='none'" class="btn" style="background:#718096;">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal(type) {
            document.getElementById('uploadType').value = type;
            document.getElementById('uploadTitle').innerText = "Dépôt : " + type.replace(/_/g, ' ');
            document.getElementById('modalUpload').style.display = 'block';
        }

        function openFicheModal(fiche) {
            document.getElementById('ficheId').value = fiche.id;
            document.getElementById('ficheTitre').value = fiche.titre;
            document.getElementById('ficheEntete').value = fiche.entete;
            document.getElementById('ficheSignal').value = fiche.signal_sonore;
            document.getElementById('ficheAlerte').value = fiche.alerte_msg;
            document.getElementById('ficheConsignes1').value = fiche.consignes_1;
            document.getElementById('ficheConsignes2').value = fiche.consignes_2;
            document.getElementById('ficheDivers').value = fiche.divers;
            document.getElementById('modalFiche').style.display = 'block';
        }
        
        window.onclick = function(event) {
            if (event.target.className == 'modal') {
                event.target.style.display = "none";
            }
        }
    </script>

    <!-- Modal Modifier Fiche -->
    <div id="modalFiche" class="modal">
        <div class="modal-content" style="width: 700px; margin-top: 50px;">
            <h3>Modifier la fiche de sécurité</h3>
            <form action="ppms_actions.php" method="POST">
                <input type="hidden" name="action" value="update_fiche">
                <input type="hidden" name="id" id="ficheId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Titre :</label>
                        <input type="text" name="titre" id="ficheTitre" required style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">En-tête (Établissement/Date) :</label>
                        <input type="text" name="entete" id="ficheEntete" style="width: 100%;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Signal sonore :</label>
                        <textarea name="signal_sonore" id="ficheSignal" rows="2" style="width: 100%;"></textarea>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Instruction Alerte :</label>
                        <textarea name="alerte_msg" id="ficheAlerte" rows="2" style="width: 100%;"></textarea>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Consignes principales (1 par ligne) :</label>
                    <textarea name="consignes_1" id="ficheConsignes1" rows="4" style="width: 100%;"></textarea>
                </div>

                <div style="margin-top: 10px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Consignes secondaires (Élèves/Postures) :</label>
                    <textarea name="consignes_2" id="ficheConsignes2" rows="4" style="width: 100%;"></textarea>
                </div>

                <div style="margin-top: 10px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Divers (Rassemblement/Matériel/Fin) :</label>
                    <textarea name="divers" id="ficheDivers" rows="3" style="width: 100%;"></textarea>
                </div>
                
                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="submit" class="btn">Mettre à jour la fiche</button>
                    <button type="button" onclick="this.closest('.modal').style.display='none'" class="btn" style="background:#718096;">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <?php include("../includes/header.php"); ?>
    <?php include("../includes/footer.php"); ?>
</body>

</html>