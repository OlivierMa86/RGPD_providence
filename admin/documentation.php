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
    <title>Documentation RGPD - Plateforme RGPD</title>
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
            background: #ebf8ff;
            border-left: 4px solid #3182ce;
            padding: 15px;
            margin-top: 20px;
        }

        .note-important {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="welcome-header">
            <h2>📚 Documentation Administrative de Base</h2>
            <p>Le socle de votre conformité et la preuve d'une organisation structurée.</p>
        </div>

        <div class="doc-section">
            <h2>1. La Documentation Administrative de Base</h2>
            <ul>
                <li><strong>Le Registre des activités de traitement :</strong> C’est la pièce maîtresse. Il doit
                    recenser tous vos fichiers (gestion des inscriptions, notes, cantine, vidéosurveillance, paie du
                    personnel OGEC, etc.).</li>
                <li><strong>L'acte de désignation du DPO :</strong> La preuve que vous avez nommé un Délégué à la
                    Protection des Données (qu'il soit interne, mutualisé ou externe).</li>
                <li><strong>Les AIPD (Analyses d'Impact) :</strong> Si vous utilisez des outils "à risque" (ex:
                    biométrie pour la cantine, surveillance à grande échelle), vous devez fournir l'analyse d'impact
                    correspondante.</li>
            </ul>
        </div>

        <div class="doc-section">
            <h2>2. Information et Droits des Personnes</h2>
            <p>Les enquêteurs vérifieront si les personnes concernées savent ce que vous faites de leurs données.</p>
            <ul>
                <li><strong>Les mentions d'information :</strong> Celles présentes sur les formulaires d'inscription, le
                    site web (politique de confidentialité) et le règlement intérieur.</li>
                <li><strong>Les modèles de recueil de consentement :</strong> Notamment pour l'utilisation des
                    photos/vidéos des élèves (droit à l'image) ou pour les activités périscolaires facultatives.</li>
                <li><strong>La procédure d'exercice des droits :</strong> Un document interne expliquant comment vous
                    répondez à un parent qui demande l'accès ou la suppression des données de son enfant.</li>
            </ul>
        </div>

        <div class="doc-section">
            <h2>3. La Maîtrise des Sous-traitants</h2>
            <p>L'école est responsable des outils qu'elle choisit (logiciels de vie scolaire comme Pronote/EcoleDirecte,
                maintenance informatique, etc.).</p>
            <ul>
                <li><strong>Les contrats de sous-traitance :</strong> Tous vos contrats avec des prestataires IT doivent
                    inclure une clause spécifique RGPD (article 28) garantissant la sécurité des données.</li>
                <li><strong>La liste des destinataires :</strong> À qui transmettez-vous des données ? (Rectorat,
                    assurance scolaire, prestataires de transport).</li>
            </ul>
        </div>

        <div class="doc-section">
            <h2>4. Sécurité et Violation de Données</h2>
            <p>Vous devez prouver que les données sont physiquement et numériquement protégées.</p>
            <ul>
                <li><strong>La Politique de Sécurité des Systèmes d'Information (PSSI) :</strong> Ou au moins un
                    document décrivant les règles de mots de passe, de sauvegardes et de gestion des accès.</li>
                <li><strong>Le registre des violations de données :</strong> Même si vous n'avez jamais fait de
                    notification à la CNIL, vous devez tenir un registre interne listant les incidents (ex: perte d'une
                    clé USB, mail envoyé au mauvais destinataire) et les mesures prises.</li>
            </ul>
        </div>

        <div class="doc-section">
            <h2>📢 Le conseil "terrain"</h2>
            <p>Les enquêteurs s'attardent souvent sur deux points sensibles en milieu scolaire :</p>
            <div class="advice-box">
                <p><strong>La durée de conservation :</strong> Pourquoi gardez-vous encore le dossier d'un élève parti
                    il y a 10 ans ? Assurez-vous d'avoir des règles d'archivage claires.</p>
                <p><strong>La vidéosurveillance :</strong> Si l'établissement est équipé, le panneau d'information doit
                    être conforme et les caméras ne doivent pas filmer les lieux de vie (cours de récréation, salles de
                    classe) en permanence sans justification de sécurité forte.</p>
            </div>

            <div class="note-important">
                <p><strong>Note importante :</strong> Dans un établissement sous contrat, la distinction entre les
                    données traitées pour le compte de l'État (enseignants de l'Éducation Nationale) et celles de l'OGEC
                    (personnel de droit privé et gestion de la vie scolaire) doit être claire dans votre registre.</p>
            </div>
        </div>

        <?php 
        include_once("../includes/functions.php");
        $toolLinks = getUsedToolsGdprLinks($pdo);
        if (!empty($toolLinks)): 
        ?>
            <div class="doc-section">
                <h2>🔗 Références RGPD des outils utilisés</h2>
                <p>Ces liens pointent vers les politiques de confidentialité officielles des outils détectés dans les
                    questionnaires de votre établissement. Ils permettent de vérifier les engagements de conformité des
                    éditeurs.</p>
                <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                    <?php foreach ($toolLinks as $name => $url): ?>
                        <a href="<?php echo $url; ?>" target="_blank" class="btn"
                            style="background-color: #718096; text-align: left; padding: 12px; display: block; text-decoration: none;">
                            🌐
                            <?php echo $name; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>