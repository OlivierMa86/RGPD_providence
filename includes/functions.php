<?php
function hashPassword($plain)
{
    return password_hash($plain, PASSWORD_BCRYPT);
}
function verifyPassword($plain, $hash)
{
    return password_verify($plain, $hash);
}
function envoyerMail($to, $sujet, $message)
{ /* via PHPMailer */
}

function genererPDF($html, $filename)
{
    // Recherche du fichier tcpdf.php de manière robuste
    $baseDir = dirname(__DIR__);
    $tcpdfPath = $baseDir . "/vendor/tcpdf/tcpdf.php";

    if (file_exists($tcpdfPath)) {
        require_once($tcpdfPath);
        if (class_exists('TCPDF')) {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Plateforme RGPD');
            $pdf->SetTitle('Fiche de conformité');

            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');

            // Chemin absolu pour l'enregistrement
            $outputPath = $baseDir . "/" . $filename;

            // S'assurer que le dossier existe
            if (!is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0777, true);
            }

            $pdf->Output($outputPath, 'F');
            return true;
        }
    }
    return false;
}

function login($pseudo, $password, $pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo=? LIMIT 1");
    $stmt->execute([$pseudo]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

function hashPwd($pwd)
{
    return password_hash($pwd, PASSWORD_BCRYPT);
}

function updateRegistreFromBilan($id_questionnaire, $pdo)
{
    // Récupérer l'utilisateur propriétaire du questionnaire
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM questionnaires WHERE id_questionnaire = ?");
    $stmt->execute([$id_questionnaire]);
    $q_info = $stmt->fetch();
    if (!$q_info)
        return;
    $id_utilisateur = $q_info['id_utilisateur'];

    // 1. Nettoyage : on ne garde que les dernières modifications de cet utilisateur
    $pdo->prepare("DELETE FROM registre_traitements WHERE id_utilisateur = ?")->execute([$id_utilisateur]);

    // 2. Analyse les réponses pour mettre à jour le registre (CDC Automatisme)
    $stmt = $pdo->prepare("SELECT q.question_txt, r.reponse_utilisateur FROM reponses r JOIN questions q ON r.id_question = q.id_question WHERE r.id_questionnaire = ?");
    $stmt->execute([$id_questionnaire]);
    $reponses = $stmt->fetchAll();

    $categories = [
        'pronote' => false,
        'aplim' => false,
        'cloud' => false,
        'saas' => false,
        'papier' => false
    ];

    foreach ($reponses as $row) {
        $resp = $row['reponse_utilisateur'];
        if (empty($resp))
            continue;

        if (stripos($resp, 'Pronote') !== false)
            $categories['pronote'] = true;
        if (stripos($resp, 'Aplim') !== false || stripos($resp, 'Charlemagne') !== false)
            $categories['aplim'] = true;
        if (preg_match('/(Cloud|Google|ENT|OneDrive|Dropbox|Microsoft)/i', $resp))
            $categories['cloud'] = true;
        if (preg_match('/(Padlet|Kahoot|Quizizz|Canva|Web|Internet)/i', $resp))
            $categories['saas'] = true;
        if (preg_match('/(Papier|Armoire|Classeur|Physique)/i', $resp))
            $categories['papier'] = true;
    }

    $added_any = false;
    if ($categories['pronote']) {
        $pdo->prepare("INSERT INTO registre_traitements (id_utilisateur, nom_traitement, finalite, responsable_traitement, base_legale, categories_donnes, source_questionnaire) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$id_utilisateur, 'Pronote (Vie Scolaire)', 'Gestion de la scolarité, notes et absences', 'Chef d’établissement', 'Mission d’intérêt public', 'Identité, résultats, vie scolaire', $id_questionnaire]);
        $added_any = true;
    }

    if ($categories['aplim']) {
        $pdo->prepare("INSERT INTO registre_traitements (id_utilisateur, nom_traitement, finalite, responsable_traitement, base_legale, categories_donnes, source_questionnaire) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$id_utilisateur, 'Charlemagne / Aplim', 'Gestion administrative et base élèves', 'Chef d’établissement', 'Mission d’intérêt public', 'Identité des élèves et familles (SIECLE)', $id_questionnaire]);
        $added_any = true;
    }

    if ($categories['cloud']) {
        $pdo->prepare("INSERT INTO registre_traitements (id_utilisateur, nom_traitement, finalite, responsable_traitement, base_legale, categories_donnes, source_questionnaire) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$id_utilisateur, 'Solution de stockage numérique', 'Stockage et partage de fichiers pédagogiques/administratifs', 'Chef d’établissement', 'Mission d’intérêt public', 'Identifiants, documents de travail', $id_questionnaire]);
        $added_any = true;
    }

    if ($categories['saas']) {
        $pdo->prepare("INSERT INTO registre_traitements (id_utilisateur, nom_traitement, finalite, responsable_traitement, base_legale, categories_donnes, source_questionnaire) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$id_utilisateur, 'Outils numériques tiers (SaaS)', 'Animation pédagogique et évaluation', 'Chef d’établissement', 'Mission d’intérêt public', 'Identité (pseudos), résultats', $id_questionnaire]);
        $added_any = true;
    }

    if ($categories['papier']) {
        $pdo->prepare("INSERT INTO registre_traitements (id_utilisateur, nom_traitement, finalite, responsable_traitement, base_legale, categories_donnes, source_questionnaire) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$id_utilisateur, 'Dossiers et archives papier', 'Gestion administrative et vie scolaire', 'Chef d’établissement', 'Obligation légale', 'Données administratives, dossiers élèves', $id_questionnaire]);
        $added_any = true;
    }

    // Entrée par défaut si aucune détection mais questionnaire rempli
    if (!$added_any && count($reponses) > 0) {
        $pdo->prepare("INSERT INTO registre_traitements (id_utilisateur, nom_traitement, finalite, responsable_traitement, base_legale, categories_donnes, source_questionnaire) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$id_utilisateur, 'Activités liées à la fonction', 'Gestion courante des données du service', 'Chef d’établissement', 'Mission d’intérêt public', 'Données professionnelles', $id_questionnaire]);
    }
}

function generateConseilsIA($id_questionnaire, $pdo)
{
    // Analyse des réponses pour générer des conseils "IA" (CDC)
    $stmt = $pdo->prepare("SELECT q.question_txt, r.reponse_utilisateur, u.id_utilisateur 
                           FROM reponses r 
                           JOIN questions q ON r.id_question = q.id_question 
                           JOIN questionnaires qn ON r.id_questionnaire = qn.id_questionnaire
                           JOIN utilisateurs u ON qn.id_utilisateur = u.id_utilisateur
                           WHERE r.id_questionnaire = ?");
    $stmt->execute([$id_questionnaire]);
    $reponses = $stmt->fetchAll();

    if (empty($reponses))
        return;

    if (empty($reponses))
        return;

    $id_utilisateur = $reponses[0]['id_utilisateur'];
    $bonnes_pratiques = [];
    $vigilance = [];
    $score = 100;

    // --- CONSEILS GÉNÉRAUX SYSTÉMATIQUES ---
    $bonnes_pratiques[] = "IDENTITÉ : Sur les outils tiers (Padlet, Canva, etc.), n'utilisez jamais le nom de famille complet des élèves. Privilégiez un prénom + initiale (ex: Lucas B.) ou un pseudonyme pour minimiser les risques.";
    $bonnes_pratiques[] = "DROIT À L'IMAGE : Avant de publier une photo sur l'ENT ou le site, vérifiez systématiquement l'autorisation signée. Préférez les photos de groupe montrant les élèves de dos ou floutez les visages.";

    // --- BASE DE CONNAISSANCE EXPERTE ---
    $expert_knowledge = [
        'Pronote' => "Pronote : C'est votre outil de référence sécurisé. Attention toutefois à la zone 'Observations' : elle doit rester factuelle et ne jamais contenir de données de santé ou de jugements dégradants.",
        'Aplim' => "Gestion Aplim/Charlemagne : Assurez-vous que votre session est toujours fermée après usage, car ce logiciel accède à l'intégralité de la base SIECLE des familles.",
        'Padlet' => "Outils Collaboratifs (Padlet, Canva, Genially) : Ces plateformes stockent les données hors de l'Union Européenne. Ne les utilisez que pour des contenus pédagogiques sans données personnelles identifiables.",
        'WhatsApp' => "ALERTE SÉCURITÉ : WhatsApp traite les métadonnées de manière commerciale. Son usage pour échanger des documents élèves ou communiquer avec les familles est vivement déconseillé par le Ministère.",
        'PAI' => "GESTION PAI/MDPH : Les données de santé sont 'sensibles' au sens du RGPD. Elles ne doivent jamais transiter par email personnel ni être stockées sur une clé USB non chiffrée.",
        'Papier' => "DOCUMENTS PHYSIQUES : Une liste d'élèves jetée entière à la poubelle est une violation de données. Utilisez systématiquement le destructeur de documents (broyeur)."
    ];

    foreach ($reponses as $row) {
        $q = $row['question_txt'];
        $r = $row['reponse_utilisateur'];
        if (empty($r))
            continue;

        // 1. Analyse des réponses fermées (Choix)
        if (stripos($q, 'Pronote') !== false && $r == 'Oui')
            $bonnes_pratiques[] = $expert_knowledge['Pronote'];
        if (stripos($q, 'Aplim') !== false && $r == 'Oui')
            $bonnes_pratiques[] = $expert_knowledge['Aplim'];

        if (stripos($q, 'outils tiers') !== false || stripos($q, 'Padlet') !== false) {
            if ($r == 'Oui') {
                $vigilance[] = $expert_knowledge['Padlet'];
                $score -= 10;
            }
        }

        if (stripos($q, 'WhatsApp') !== false || stripos($q, 'messageries') !== false) {
            if ($r == 'Oui') {
                $vigilance[] = $expert_knowledge['WhatsApp'];
                $score -= 20;
            }
        }

        if (stripos($q, 'santé') !== false || stripos($q, 'PAI') !== false || stripos($q, 'MDPH') !== false) {
            if ($r == 'Oui') {
                $vigilance[] = $expert_knowledge['PAI'];
                $score -= 5;
            }
        }

        if (stripos($q, 'papier') !== false && $r == 'Poubelle classique') {
            $vigilance[] = $expert_knowledge['Papier'];
            $score -= 15;
        }

        // 2. Analyse de la question ouverte sur les autres logiciels
        if (stripos($q, 'autres logiciels') !== false) {
            $tools = [
                'Kahoot' => "Kahoot/Quizizz : Outils ludiques mais gourmands en données. Ne demandez pas aux élèves de créer de compte personnel.",
                'Google' => "Google Drive/Form : L'usage de comptes personnels 'Gmail' pour stocker des travaux d'élèves est proscrit. Utilisez exclusivement l'espace établissement.",
                'Discord' => "Discord : Plateforme non adaptée au cadre scolaire souverain. Risque élevé de contact non sollicité.",
                'Facebook' => "Réseaux Sociaux : Ne créez jamais de groupe 'classe' sur Facebook ou Instagram."
            ];
            foreach ($tools as $key => $advice) {
                if (stripos($r, $key) !== false) {
                    $vigilance[] = $advice;
                    $score -= 10;
                }
            }
        }

        // 3. Sécurité de base
        if (stripos($q, 'session nominative') !== false && $r == 'Non') {
            $vigilance[] = "Poste de travail : L'absence de session nominative empêche toute traçabilité en cas d'incident. Contactez l'informatique.";
            $score -= 20;
        }
        if (stripos($q, 'Verrouillez-vous') !== false && $r == 'Jamais') {
            $vigilance[] = "Verrouillage : Laisser un poste ouvert est la première cause de 'fuite' de données en interne. Prenez le réflexe Win+L.";
            $score -= 10;
        }
        if (stripos($q, 'stockez') !== false && stripos($r, 'Clé USB') !== false) {
            $vigilance[] = "Supports amovibles : Les clés USB se perdent facilement. Si vous en utilisez une, elle doit être chiffrée ou ne contenir aucune donnée nominative.";
            $score -= 10;
        }
    }

    // Déshabillage des doublons et nettoyage
    $bonnes_pratiques = array_unique($bonnes_pratiques);
    $vigilance = array_unique($vigilance);

    if (empty($bonnes_pratiques))
        $bonnes_pratiques[] = "Continuez à appliquer les consignes de sécurité de votre établissement.";
    if (empty($vigilance))
        $vigilance[] = "Aucun risque majeur détecté sur votre périmètre actuel. Restez vigilant.";

    // Utilisation de \n pour les sauts de ligne (plus propre en DB et flexible display)
    $bp_txt = "• " . implode("\n• ", $bonnes_pratiques);
    $vig_txt = "• " . implode("\n• ", $vigilance);

    // Mise à jour du questionnaire
    $pdo->prepare("UPDATE questionnaires SET score_conformite = ?, observations = ? WHERE id_questionnaire = ?")
        ->execute([max(0, $score), $bp_txt, $id_questionnaire]);

    // Archivage dans la table fiches
    $pdo->prepare("DELETE FROM fiches WHERE id_utilisateur = ? AND type_fiche IN ('bonnes_pratiques', 'points_vigilance')")->execute([$id_utilisateur]);

    $pdo->prepare("INSERT INTO fiches (id_utilisateur, type_fiche, observations, par_admin) VALUES (?, 'bonnes_pratiques', ?, 0)")
        ->execute([$id_utilisateur, $bp_txt]);
    $pdo->prepare("INSERT INTO fiches (id_utilisateur, type_fiche, observations, par_admin) VALUES (?, 'points_vigilance', ?, 0)")
        ->execute([$id_utilisateur, $vig_txt]);
}

/**
 * Récupère les liens de référence RGPD pour les outils détectés dans les réponses.
 * @param PDO $pdo
 * @param int|null $id_utilisateur Si null, récupère pour tout l'établissement (Admin)
 */
function getUsedToolsGdprLinks($pdo, $id_utilisateur = null)
{
    $gdpr_refs = [
        'Pronote' => [
            'url' => 'https://www.index-education.com/fr/rgpd-pronote.php',
            'keywords' => ['pronote']
        ],
        'Aplim / Charlemagne' => [
            'url' => 'https://www.aplim.fr/mentions-legales/',
            'keywords' => ['aplim', 'charlemagne']
        ],
        'ENT (EcoleDirecte, etc.)' => [
            'url' => 'https://www.ecoledirecte.com/politique-confidentialite',
            'keywords' => ['ecoledirecte', 'ent']
        ],
        'Padlet' => [
            'url' => 'https://legal.padlet.com/privacy',
            'keywords' => ['padlet']
        ],
        'Canva' => [
            'url' => 'https://www.canva.com/policies/privacy-policy/',
            'keywords' => ['canva']
        ],
        'Genially' => [
            'url' => 'https://genially.com/privacy/',
            'keywords' => ['genially']
        ],
        'Kahoot' => [
            'url' => 'https://kahoot.com/privacy-policy/',
            'keywords' => ['kahoot']
        ],
        'Quizizz' => [
            'url' => 'https://quizizz.com/privacy',
            'keywords' => ['quizizz']
        ],
        'iCloud / Apple (Éducation)' => [
            'url' => 'https://support.apple.com/fr-fr/102315',
            'keywords' => ['icloud', 'apple']
        ],
        'Google Workspace for Education' => [
            'url' => 'https://workspace.google.com/intl/fr/terms/education_privacy/',
            'keywords' => ['google', 'drive', 'form', 'docs', 'classroom']
        ],
        'Microsoft (Éducation, Teams, OneDrive)' => [
            'url' => 'https://www.microsoft.com/fr-fr/trust-center/privacy/gdpr',
            'keywords' => ['microsoft', 'teams', 'onedrive', 'office', '365', 'sharepoint']
        ],
        'Dropbox' => [
            'url' => 'https://www.dropbox.com/fr/privacy',
            'keywords' => ['dropbox']
        ],
        'Zoom' => [
            'url' => 'https://explore.zoom.us/fr/privacy/',
            'keywords' => ['zoom']
        ],
        'WhatsApp' => [
            'url' => 'https://www.whatsapp.com/legal/privacy-policy-eea',
            'keywords' => ['whatsapp']
        ],
        'Discord' => [
            'url' => 'https://discord.com/privacy',
            'keywords' => ['discord']
        ],
        'Skype' => [
            'url' => 'https://privacy.microsoft.com/fr-fr/privacystatement',
            'keywords' => ['skype']
        ],
        'Telegram' => [
            'url' => 'https://telegram.org/privacy',
            'keywords' => ['telegram']
        ],
        'Signal' => [
            'url' => 'https://signal.org/legal/',
            'keywords' => ['signal']
        ],
        'Slack' => [
            'url' => 'https://slack.com/intl/fr-fr/trust/privacy/privacy-policy',
            'keywords' => ['slack']
        ],
        'Trello' => [
            'url' => 'https://www.atlassian.com/legal/privacy-policy',
            'keywords' => ['trello']
        ],
        'Notion' => [
            'url' => 'https://www.notion.so/fr-fr/about/privacy-policy',
            'keywords' => ['notion']
        ],
        'Evernote' => [
            'url' => 'https://evernote.com/intl/fr/privacy/policy',
            'keywords' => ['evernote']
        ],
        'Prezi' => [
            'url' => 'https://prezi.com/privacy-policy/',
            'keywords' => ['prezi']
        ],
        'Moodle' => [
            'url' => 'https://moodle.com/privacy-notice/',
            'keywords' => ['moodle']
        ],
        'Pix' => [
            'url' => 'https://pix.fr/politique-de-confidentialite',
            'keywords' => ['pix']
        ],
        'Klaxoon' => [
            'url' => 'https://klaxoon.com/fr/legal/privacy-policy',
            'keywords' => ['klaxoon']
        ],
        'Wooclap' => [
            'url' => 'https://www.wooclap.com/fr/privacy-policy/',
            'keywords' => ['wooclap']
        ],
        'WeTransfer' => [
            'url' => 'https://wetransfer.com/legal/privacy',
            'keywords' => ['wetransfer']
        ],
        'Typeform' => [
            'url' => 'https://admin.typeform.com/to/dwk6gt/',
            'keywords' => ['typeform']
        ],
        'SurveyMonkey' => [
            'url' => 'https://www.surveymonkey.com/mp/legal/privacy/',
            'keywords' => ['surveymonkey']
        ],
        'Mentimeter' => [
            'url' => 'https://www.mentimeter.com/privacy',
            'keywords' => ['mentimeter']
        ],
        'Doodle' => [
            'url' => 'https://doodle.com/fr/privacy-policy/',
            'keywords' => ['doodle']
        ],
        'Miro' => [
            'url' => 'https://miro.com/legal/privacy-policy/',
            'keywords' => ['miro']
        ],
        'Mural' => [
            'url' => 'https://www.mural.co/legal/privacy-policy',
            'keywords' => ['mural']
        ],
        'Lucidchart' => [
            'url' => 'https://lucid.co/fr/legal-privacy',
            'keywords' => ['lucidchart', 'lucidpark']
        ]
    ];

    // On récupère les réponses et les infos de la question associée
    $query = "SELECT r.reponse_utilisateur, q.question_txt, q.type_reponse
              FROM reponses r
              JOIN questions q ON r.id_question = q.id_question
              JOIN (
                  SELECT MAX(id_questionnaire) as last_q
                  FROM questionnaires
                  WHERE 1=1";
    $params = [];

    if ($id_utilisateur) {
        $query .= " AND id_utilisateur = ?";
        $params[] = $id_utilisateur;
    }

    $query .= " GROUP BY id_utilisateur) qn ON r.id_questionnaire = qn.last_q";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reponses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $detected_tools = [];

    foreach ($reponses as $row) {
        $resp = $row['reponse_utilisateur'];
        if (empty($resp))
            continue;

        // 1. Détection des outils connus (partout dans le questionnaire)
        $resp_known_tools = [];
        foreach ($gdpr_refs as $tool => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (stripos($resp, $keyword) !== false) {
                    $detected_tools[$tool] = $data['url'];
                    $resp_known_tools[] = $keyword;
                }
            }
        }

        // 2. Extraction dynamique (seulement pour la question "autres logiciels")
        $is_software_question = (stripos($row['question_txt'], 'autres logiciels') !== false);

        if ($is_software_question) {
            // On sépare la réponse en mots/segments (virgules, points-virgules, espaces, retours ligne)
            $chunks = preg_split('/[\s,;.\n]+/', $resp, -1, PREG_SPLIT_NO_EMPTY);

            $stop_words = [
                'oui',
                'non',
                'parfois',
                'toujours',
                'jamais',
                'aucun',
                'aucune',
                'rien',
                'pas',
                'le',
                'la',
                'les',
                'un',
                'une',
                'des',
                'du',
                'au',
                'aux',
                'et',
                'ou',
                'avec',
                'pour',
                'dans',
                'sur',
                'par',
                'ce',
                'cette',
                'ces',
                'est',
                'sont',
                'ont',
                'fait',
                'faire',
                'utiliser',
                'utilise',
                'utilisons',
                'utilisés',
                'logiciel',
                'logiciels',
                'site',
                'sites',
                'application',
                'applications',
                'outil',
                'outils',
                'plus',
                'moins',
                'tout'
            ];

            foreach ($chunks as $chunk) {
                $clean_chunk = trim($chunk, " \t\n\r\0\x0B.,;!?()[]'\"");
                $lower_chunk = mb_strtolower($clean_chunk);

                // On ignore si trop court, si c'est un stop-word, ou si c'est déjà un outil connu détecté
                if (strlen($clean_chunk) < 3 || in_array($lower_chunk, $stop_words))
                    continue;

                // On vérifie si ce mot n'est pas déjà couvert par un mot-clé reconnu
                $already_covered = false;
                foreach ($resp_known_tools as $k) {
                    if (stripos($clean_chunk, $k) !== false) {
                        $already_covered = true;
                        break;
                    }
                }

                if (!$already_covered && !isset($detected_tools[$clean_chunk])) {
                    // C'est un nouvel outil potentiel !
                    $detected_tools[$clean_chunk] = "https://www.google.com/search?q=RGPD+politique+confidentialite+" . urlencode($clean_chunk);
                }
            }
        }
    }

    return $detected_tools;
}
?>