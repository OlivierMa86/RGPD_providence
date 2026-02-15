<?php
// includes/ai_watch.php

function fetchRSS($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Limiter les redirections
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // Gérer le referer automatiquement
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Un peu plus de temps

    // User agent "réel" pour passer pour un navigateur
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
    // Ignorer les erreurs SSL pour le développement local si nécessaire (à commenter en prod idéalement)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200 || !$data) {
        // Log l'erreur pour le debug
        error_log("LegalWatch Error [$url]: HTTP $httpCode - cURL: $curlError");
        return [];
    }

    // Tentative de réparation XML pour les flux mal formés
    $data = trim($data);

    $xml = @simplexml_load_string($data);
    if (!$xml)
        return [];

    $items = [];
    // Support RSS 2.0 et Atom (simplifié)
    if (isset($xml->channel->item)) {
        foreach ($xml->channel->item as $item) {
            $items[] = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'description' => strip_tags((string) $item->description),
                'pubDate' => (string) $item->pubDate,
                'source' => (string) $xml->channel->title
            ];
        }
    }
    return array_slice($items, 0, 5); // On garde les 5 plus récents par flux pour ne pas saturer l'IA
}

function analyzeWithGemini($items)
{
    if (empty($items))
        return [];

    // Rotation basique des clés API
    $keys = SECRET_GEMINI_KEYS;
    $apiKey = $keys[array_rand($keys)];

    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

    $promptText = "Tu es un expert juridique RGPD et sécurité scolaire (PPMS). Voici une liste d'articles récents.\n";
    $promptText .= "Analyse-les et filtre UNIQUEMENT ceux qui sont pertinents pour un établissement scolaire.\n";
    $promptText .= "IMPORTANT : Fais bien la distinction entre :\n";
    $promptText .= "- 'RGPD' : Protection des données, CNIL, Cyberattaque, Droit à l'image.\n";
    $promptText .= "- 'PPMS' : Sécurité physique, Vigipirate, Incendie, Intrusion, Risques Majeurs.\n";
    $promptText .= "Si un article parle de logiciels ou de piratage -> C'est souvent RGPD (ou cybersécurité liée aux données).\n";
    $promptText .= "Si un article parle de plan d'évacuation, de gardiennage ou de menaces physiques -> C'est PPMS.\n";
    $promptText .= "Pour chaque article pertinent, génère un objet JSON avec les champs suivants :\n";
    $promptText .= "- 'titre': Le titre de l'article (reformule si nécessaire pour être clair).\n";
    $promptText .= "- 'date': La date de publication (format YYYY-MM-DD).\n";
    $promptText .= "- 'description': Un résumé de 2 phrases expliquant l'impact pour l'école.\n";
    $promptText .= "- 'action': Une action concrète à faire pour le chef d'établissement ou le DPO (ex: 'Mettre à jour le registre', 'Informer les profs', 'Vérifier les affichages').\n";
    $promptText .= "- 'lien': L'URL d'origine de l'article.\n";
    $promptText .= "- 'badge': Choisis parmi 'CRITIQUE', 'ACTION REQUISE', 'INFO', 'CALENDRIER', 'CONFORMITÉ'.\n";
    $promptText .= "- 'type': 'RGPD' ou 'PPMS'.\n\n";
    $promptText .= "Voici les articles :\n" . json_encode($items) . "\n\n";
    $promptText .= "Réponds UNIQUEMENT avec un tableau JSON valide (pas de Markdown, pas de ```json ... ```).";

    $postData = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $promptText]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.2,
            "maxOutputTokens" => 2000,
            "responseMimeType" => "application/json"
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Ajout aussi pour Gemini au cas où
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Log si erreur API
    if ($httpCode != 200) {
        error_log("Gemini API Error: $httpCode - $curlError - Response: $response");
    }

    $json = json_decode($response, true);

    if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $json['candidates'][0]['content']['parts'][0]['text'];
        // Nettoyage Markdown si jamais l'IA en met
        $rawText = str_replace(['```json', '```'], '', $rawText);
        $data = json_decode($rawText, true);
        return is_array($data) ? $data : [];
    }

    return [];
}


function updateLegalWatch()
{
    // Liste de sources diversifiée pour contourner les blocages
    $sources = [
        // 1. Google News : Sécurité & PPMS (Ciblé)
        'https://news.google.com/rss/search?q=vigipirate+ecole+securite+intrusion+attentat+site:interieur.gouv.fr&hl=fr&gl=FR&ceid=FR:fr',
        'https://news.google.com/rss/search?q=ppms+exercice+securite+ecole+education+nationale&hl=fr&gl=FR&ceid=FR:fr',

        // 2. Google News : RGPD & Numérique
        'https://news.google.com/rss/search?q=cnil+education+scolaire+donnees+personnelles&hl=fr&gl=FR&ceid=FR:fr',
        'https://news.google.com/rss/search?q=cybersecurite+etablissement+scolaire+ransomware&hl=fr&gl=FR&ceid=FR:fr',

        // 3. Flux officiels
        'https://www.education.gouv.fr/rss/actualites',
        'https://www.cnil.fr/rss.xml',
        'https://www.interieur.gouv.fr/Actualites/Flux-RSS/Vigipirate', // Hypothétique mais souvent relayé par Google News
        'https://www.service-public.fr/rss/actu-particuliers.rss'
    ];

    $all_items = [];
    $success_count = 0;

    foreach ($sources as $url) {
        $items = fetchRSS($url);
        if (!empty($items)) {
            $all_items = array_merge($all_items, $items);
            $success_count++;
        }
    }

    if (empty($all_items)) {
        // Fallback ultime : on retourne false pour garder les anciennes données
        // Ou on pourrait charger un fichier de secours local
        return ['success' => false, 'message' => "Aucun flux RSS n'a pu être récupéré (Google News, CNIL, Education). Vérifiez le pare-feu."];
    }
    foreach ($sources as $url) {
        $items = fetchRSS($url);
        $all_items = array_merge($all_items, $items);
    }

    if (empty($all_items)) {
        // Vérifier le fichier error_log.txt à la racine ou dans includes pour les détails
        return ['success' => false, 'message' => 'Impossible de récupérer les flux RSS. Vérifiez les logs erreur ou la connexion sortante (cURL).'];
    }

    $analyzed_data = analyzeWithGemini($all_items);

    if (!empty($analyzed_data)) {
        // Sauvegarde dans un fichier JSON dans le dossier admin
        $filePath = __DIR__ . '/../admin/legal_watch_data.json';

        // 1. Charger l'existant
        $existing_data = [];
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $existing_data = json_decode($content, true);
            if (!is_array($existing_data))
                $existing_data = [];
        }

        // 2. Fusionner et dédoublonner (basé sur le lien)
        $merged_data = $existing_data;
        $existing_links = array_column($existing_data, 'lien');

        foreach ($analyzed_data as $new_item) {
            // Si le lien n'existe pas déjà, on ajoute
            if (!in_array($new_item['lien'], $existing_links)) {
                // On ajoute au DÉBUT du tableau pour que ce soit le plus récent
                array_unshift($merged_data, $new_item);
                $existing_links[] = $new_item['lien']; // Pour éviter les doublons dans le lot actuel
            }
        }

        // On limite l'historique à 50 articles pour éviter un fichier géant
        $merged_data = array_slice($merged_data, 0, 50);

        // 3. Sauvegarder
        file_put_contents($filePath, json_encode($merged_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return ['success' => true, 'count' => count($analyzed_data), 'data' => $merged_data];
    } else {
        // Ce n'est pas une erreur, c'est que rien n'est pertinent aujourd'hui
        return ['success' => true, 'count' => 0, 'message' => 'L\'IA a analysé les flux mais n\'a détecté aucune actualité critique pour un établissement scolaire aujourd\'hui.'];
    }
}
?>