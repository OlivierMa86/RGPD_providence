<?php
include("includes/config.php");
include("includes/auth.php");

// Vérification de sécurité basique
if (!isset($currentUser)) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$question_id = $_POST['question_id'] ?? null;
$question_text = $_POST['question_text'] ?? '';
$user_roles = $currentUser['fonction']; // ex: "Enseignant, CPE"

if (!$question_id || !$question_text) {
    echo json_encode(['error' => 'Données manquantes']);
    exit;
}

// Rotation de clés simple
$keys = SECRET_GEMINI_KEYS;
$apiKey = $keys[array_rand($keys)];

$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
error_log("AI Help API URL: " . preg_replace('/key=([^&]+)/', 'key=***', $apiUrl));

// Prompt contextuel
$prompt = "Tu es un assistant RGPD expert pour un établissement scolaire. \n";
$prompt .= "L'utilisateur a pour rôle(s) : '$user_roles'.\n";
$prompt .= "Il doit répondre à la question suivante : '$question_text'.\n";
$prompt .= "Donne-lui 3 points d'attention ou exemples concrets LIÉS À SON RÔLE pour l'aider à répondre honnêtement et
complètement.\n";
$prompt .= "Sois bref, pédagogique et bienveillant. Fais une liste à puces.";

$postData = [
    "contents" => [
        ["parts" => [["text" => $prompt]]]
    ],
    "generationConfig" => [
        "temperature" => 0.4,
        "maxOutputTokens" => 500
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Bypass SSL local si besoin
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$json = json_decode($response, true);

if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
    $advice = $json['candidates'][0]['content']['parts'][0]['text'];
    // Nettoyage Markdown -> HTML simple
    $advice = str_replace(["\n- ", "\n* "], "<br>• ", $advice);
    $advice = str_replace("\n", "<br>", $advice);
    echo json_encode(['success' => true, 'advice' => $advice]);
} else {
    // Debug info
    $debug = "HTTP Code: $httpCode | cURL Error: " . ($curlError ?: 'None') . " | Response: " . substr($response, 0, 200) . "...";
    error_log("AI Help Error: $debug");
    echo json_encode(['success' => false, 'message' => 'L\'IA n\'a pas pu générer de conseil. Détails technique : ' . $debug]);
}
?>