<?php
/**
 * Données brutes de la veille juridique pour la Plateforme Providence.
 * Basé sur les recherches réglementaires 2025-2026.
 */

$veille_rgpd = [
    [
        'date' => '2025-12-16',
        'titre' => 'Renouvellement du partenariat CNIL - Éducation Nationale',
        'description' => 'Le partenariat historique de 2018 a été renouvelé pour 2026. Priorité à l\'accompagnement des directeurs d\'établissement dans leur rôle de Responsable de Traitement.',
        'action' => 'Vérifier la désignation du DPO (Délégué à la Protection des Données) dans la FAQ admin.',
        'lien' => 'https://www.cnil.fr/fr/education-au-numerique-le-ministere-de-leducation-nationale-et-la-cnil-renouvellent-leur-partenariat',
        'badge' => 'CRITIQUE'
    ],
    [
        'date' => '2025-09-01',
        'titre' => 'Obligation PIX IA (Rentrée 2025)',
        'description' => 'Déploiement obligatoire des parcours Pix IA pour les élèves de 4ème et 2nde dès la rentrée 2025-2026.',
        'action' => 'Inscrire le traitement de données "PIX" dans votre registre des activités.',
        'lien' => 'https://www.education.gouv.fr',
        'badge' => 'ACTION REQUISE'
    ],
    [
        'date' => '2026-01-01',
        'titre' => 'Phase "Accountability" CNIL 2026',
        'description' => 'La CNIL exige désormais des preuves concrètes de formation du personnel et des audits de sécurité réguliers.',
        'action' => 'Générer un bilan des utilisateurs ayant complété leur bilan annuel via le bouton "Suivi" des utilisateurs.',
        'badge' => 'CONFORMITÉ'
    ]
];

$veille_ppms = [
    [
        'date' => '2024-09-01',
        'titre' => 'Généralisation du PPMS Unifié',
        'description' => 'Fusion des anciens PPMS "Risques majeurs" et "Attentat-intrusion" en un seul document unique.',
        'action' => 'Vérifier que vos 3 fiches réflexes (Évacuation, Confinement, Attentat) sont à jour dans le centre de gestion PPMS.',
        'lien' => 'https://www.education.gouv.fr/un-seul-ppms-pour-les-ecoles-et-les-etablissements-scolaires',
        'badge' => 'MAJEUR'
    ],
    [
        'date' => '2024-01-01',
        'titre' => 'Nouvelle répartition des responsabilités (Circulaire 8 juin 2023)',
        'description' => 'Pour les écoles du 1er degré, la DSDEN prend en charge la rédaction. Les directeurs collaborent mais ne sont plus les seuls rédacteurs.',
        'action' => 'Prendre contact avec votre DSDEN pour le calendrier de bascule vers le PPMS unifié.',
        'badge' => 'INFO'
    ],
    [
        'date' => '2025-01-01',
        'titre' => 'Calendrier de renouvellement 2023-2028',
        'description' => 'Tous les établissements devront être passés au PPMS unifié au plus tard d\'ici septembre 2028.',
        'action' => 'Planifier un exercice PPMS "Risques majeurs" avant les vacances d\'hiver si ce n\'est pas encore fait.',
        'badge' => 'CALENDRIER'
    ]
];
