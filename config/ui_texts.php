<?php

/**
 * Registre des textes d'interface personnalisables (boutons, messages...) via
 * Admin → Paramètres → Textes de l'interface. Chaque entrée : clé unique =>
 * [label (pour l'admin), default (texte affiché tant que rien n'est personnalisé)].
 *
 * Pour rendre un nouveau texte éditable : ajouter une entrée ici, puis
 * remplacer le texte en dur dans la vue par ui_text('la.cle', 'Texte par défaut').
 */

return [

    'Dashboard — Boutons' => [
        'dashboard.btn.send_lead'        => ['label' => 'Tuile "Envoyer un lead"',        'default' => 'Envoyer un lead'],
        'dashboard.btn.leads_received'   => ['label' => 'Tuile "Mes leads reçus"',         'default' => 'Mes leads reçus'],
        'dashboard.btn.connections'      => ['label' => 'Tuile "Mes connexions"',          'default' => 'Mes connexions'],
        'dashboard.btn.network'          => ['label' => 'Tuile "Réseauter"',               'default' => 'Réseauter'],
        'dashboard.btn.see_all_network'  => ['label' => '"Voir tout" — Réseau à développer', 'default' => 'Voir tout'],
        'dashboard.btn.see_all_events'   => ['label' => '"Voir tout" — Événements à venir',  'default' => 'Voir tout'],
        'dashboard.btn.see_all_groups'   => ['label' => '"Voir tout" — Groupes populaires',  'default' => 'Voir tout'],
    ],

    'Achat de points — Messages' => [
        'points.msg.negative_popup_title'  => ['label' => 'Popup solde négatif — titre', 'default' => 'Solde de points négatif'],
        'points.msg.negative_popup_prefix' => ['label' => 'Popup solde négatif — avant le solde', 'default' => 'Votre solde est de'],
        'points.msg.negative_popup_suffix' => ['label' => 'Popup solde négatif — après le solde', 'default' => 'Rechargez votre compte pour continuer à recevoir des leads de la communauté.'],
        'points.msg.not_negative_notice'   => ['label' => 'Message quand le solde n\'est pas négatif', 'default' => 'Votre solde n\'est pas négatif — l\'achat de points n\'est utile que pour se remettre à flot après un solde négatif.'],
        'points.msg.capped_notice'         => ['label' => 'Message de plafond d\'achat (:max = points max)', 'default' => 'Achat plafonné à :max point(s) maximum (le montant de votre déficit).'],
    ],

];
