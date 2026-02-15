<?php
session_start();
include("includes/config.php");

// Sécurité
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$fonction = trim($_POST['fonction'] ?? '');
if (empty($fonction)) {
    echo json_encode(['success' => false, 'message' => 'Fonction non spécifiée']);
    exit;
}

// --- VÉRIFIER SI DES QUESTIONS IA EXISTENT DÉJÀ EN BASE POUR CETTE FONCTION ---
// On utilise un préfixe "[IA]" dans fonction_cible pour distinguer les questions générées par l'IA
$ia_tag = '[IA] ' . $fonction;
$stmt = $pdo->prepare("SELECT id_question, question_txt, type_reponse, options_reponse FROM questions WHERE fonction_cible = ?");
$stmt->execute([$ia_tag]);
$existing = $stmt->fetchAll();

if (!empty($existing)) {
    // Les questions IA pour cette fonction existent déjà en base → on les retourne
    $result = [];
    foreach ($existing as $row) {
        $result[] = [
            'id_question' => (int) $row['id_question'],
            'question' => $row['question_txt'],
            'type' => $row['type_reponse'],
            'options' => json_decode($row['options_reponse']),
            'categorie' => null
        ];
    }
    echo json_encode(['success' => true, 'questions' => $result, 'source' => 'db']);
    exit;
}

// --- APPEL IA : Générer de nouvelles questions ---
$keys = SECRET_GEMINI_KEYS;
$apiKey = $keys[array_rand($keys)];
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

$prompt = "Tu es un expert RGPD spécialisé dans les établissements scolaires en France (écoles, collèges, lycées).

L'utilisateur a la fonction suivante : \"$fonction\"

Génère entre 5 et 10 questions RGPD spécifiques et pertinentes pour cette fonction.
Chaque question doit être concrète, pratique et liée au quotidien de cette personne dans son établissement scolaire.

Pour chaque question, fournis :
- 'question' : Le texte de la question (clair, précis, en français).
- 'type' : 'choix' ou 'texte'.
- 'options' : Si type='choix', un tableau de 2 à 4 options possibles. Si type='texte', null.
- 'categorie' : Une catégorie parmi : 'Données personnelles', 'Outils numériques', 'Sécurité', 'Droits des personnes', 'Sous-traitance', 'Communication'.

Pense aux spécificités du poste :
- Quels types de données cette personne manipule-t-elle ?
- Quels outils numériques utilise-t-elle ?
- Quels sont les risques RGPD spécifiques à ce poste ?
- Quelles sont les obligations légales récentes (2024-2026) qui la concernent ?

Réponds UNIQUEMENT avec un tableau JSON valide (pas de Markdown, pas de ```json).";

$postData = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.4,
        "maxOutputTokens" => 3000,
        "responseMimeType" => "application/json"
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode != 200) {
    error_log("AI Questions Error: HTTP $httpCode - $curlError - Response: $response");
    echo json_encode(['success' => false, 'message' => "Erreur API (HTTP $httpCode)"]);
    exit;
}

$json = json_decode($response, true);

if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
    $rawText = $json['candidates'][0]['content']['parts'][0]['text'];
    $rawText = str_replace(['```json', '```'], '', $rawText);
    $questions = json_decode($rawText, true);

    if (is_array($questions) && !empty($questions)) {
        // INSÉRER les questions dans la table `questions` avec le tag [IA]
        $result = [];
        $stmtInsert = $pdo->prepare("INSERT INTO questions (fonction_cible, question_txt, type_reponse, options_reponse, obligatoire) VALUES (?, ?, ?, ?, 0)");

        foreach ($questions as $q) {
            $type = ($q['type'] ?? 'texte') === 'choix' ? 'choix' : 'texte';
            $options = ($type === 'choix' && !empty($q['options'])) ? json_encode($q['options'], JSON_UNESCAPED_UNICODE) : null;

            $stmtInsert->execute([$ia_tag, $q['question'], $type, $options]);
            $newId = (int) $pdo->lastInsertId();

            $result[] = [
                'id_question' => $newId,
                'question' => $q['question'],
                'type' => $type,
                'options' => $q['options'] ?? null,
                'categorie' => $q['categorie'] ?? null
            ];
        }

        echo json_encode(['success' => true, 'questions' => $result, 'source' => 'ai']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'L\'IA n\'a pas pu générer de questions.']);
?>