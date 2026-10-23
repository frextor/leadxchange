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

    'Leads — Boutons' => [
        'leads.btn.send_lead'      => ['label' => 'Bouton "Envoyer le lead" (formulaire)',         'default' => 'Envoyer le lead'],
        'leads.btn.cancel'         => ['label' => 'Bouton "Annuler" (création de lead)',            'default' => 'Annuler'],
        'leads.btn.send_lead_cta'  => ['label' => 'Bouton "Envoyer un lead" (liste des leads)',     'default' => 'Envoyer un lead'],
        'leads.btn.send_first_lead'=> ['label' => 'Bouton "Envoyer votre premier lead" (état vide)','default' => 'Envoyer votre premier lead'],
        'leads.btn.report'         => ['label' => 'Bouton "Signaler" (détail du lead)',             'default' => 'Signaler'],
        'leads.btn.reject'         => ['label' => 'Bouton "Refuser" (détail du lead)',              'default' => 'Refuser'],
        'leads.btn.accept'         => ['label' => 'Bouton "Accepter" (détail du lead)',             'default' => 'Accepter'],
        'leads.btn.rate'           => ['label' => 'Bouton "Noter le lead"',                         'default' => 'Noter le lead'],
        'leads.btn.mark_converted' => ['label' => 'Bouton "Marquer converti"',                      'default' => 'Marquer converti'],
        'leads.btn.remind'         => ['label' => 'Bouton "Relancer"',                               'default' => 'Relancer'],
    ],

    'Leads — Messages' => [
        'leads.msg.no_connections_notice'       => ['label' => 'Message aucune connexion (destinataire du lead)', 'default' => 'Connectez-vous avec des membres pour leur envoyer des leads.'],
        'leads.msg.rgpd_consent_text'           => ['label' => 'Texte consentement RGPD',                         'default' => "Je certifie avoir obtenu le consentement du prospect ou disposer d'une base légale (art. 6 RGPD) pour partager ses données personnelles à des fins de prospection commerciale."],
        'leads.msg.no_sensitive_data_text'      => ['label' => 'Texte absence de données sensibles',              'default' => "Ce lead ne contient aucune donnée sensible (santé, opinions politiques…), aucune donnée de mineur, ni d'information couverte par un accord de confidentialité."],
        'leads.msg.no_recipient_selected'       => ['label' => 'Message aucun destinataire sélectionné',          'default' => 'Aucun destinataire sélectionné.'],
        'leads.msg.empty_sent'                  => ['label' => 'État vide — leads envoyés',                       'default' => "Vous n'avez encore envoyé aucun lead."],
        'leads.msg.empty_received'              => ['label' => 'État vide — leads reçus',                        'default' => 'Aucun lead reçu pour le moment. Les leads envoyés par votre réseau apparaîtront ici.'],
        'leads.msg.no_filter_match'             => ['label' => 'Message aucun résultat de filtre',                'default' => 'Aucun lead ne correspond à ce filtre.'],
        'leads.msg.rating_due_notice'           => ['label' => 'Notice délai de notation (:date = date limite)',  'default' => "Notez ce lead avant le :date : au-delà de 4★ de moyenne, l'expéditeur reçoit un bonus."],
        'leads.msg.shared_responsibility_title' => ['label' => 'Titre responsabilité partagée',                   'default' => 'Responsabilité partagée (CGU §7.6)'],
        'leads.msg.shared_responsibility_body'  => ['label' => 'Texte responsabilité partagée',                   'default' => "l'émetteur est responsable de la licéité des données transmises, le receveur de leur traitement ultérieur."],
    ],

    'Événements — Boutons' => [
        'events.btn.create_event'     => ['label' => 'Bouton "Créer" (formulaire événement)',       'default' => 'Créer'],
        'events.btn.cancel'           => ['label' => 'Bouton "Annuler" (création événement)',       'default' => 'Annuler'],
        'events.btn.create_event_cta' => ['label' => 'Bouton "Créer un événement" (liste)',          'default' => 'Créer un événement'],
        'events.btn.invite'           => ['label' => 'Bouton "Inviter" (page événement)',            'default' => 'Inviter'],
        'events.btn.share'            => ['label' => 'Bouton "Partager"',                             'default' => 'Partager'],
        'events.btn.leave_event'      => ['label' => 'Bouton "Quitter l\'événement"',                 'default' => "Quitter l'événement"],
        'events.btn.decline_invitation' => ['label' => 'Bouton "Décliner" (invitation événement)',   'default' => 'Décliner'],
        'events.btn.accept_invitation'  => ['label' => 'Bouton "Accepter" (invitation événement)',   'default' => 'Accepter'],
        'events.btn.join'             => ['label' => 'Bouton "Rejoindre" (événement)',                'default' => 'Rejoindre'],
        'events.btn.pay'               => ['label' => 'Bouton "Payer" (préfixe, prix ajouté ensuite)','default' => 'Payer'],
        'events.btn.delete_event'      => ['label' => 'Bouton "Supprimer" (événement)',               'default' => 'Supprimer'],
        'events.btn.invite_member'     => ['label' => 'Bouton "Inviter" (ligne membre)',              'default' => 'Inviter'],
        'events.btn.invite_group'      => ['label' => 'Bouton "Inviter le groupe"',                   'default' => 'Inviter le groupe'],
        'events.btn.invite_all'        => ['label' => 'Bouton "Tout inviter"',                        'default' => 'Tout inviter'],
    ],

    'Événements — Messages' => [
        'events.msg.form_errors_banner'        => ['label' => 'Bandeau erreurs de formulaire',              'default' => 'Vérifiez les champs signalés ci-dessous.'],
        'events.msg.private_auto_invite_notice'=> ['label' => 'Notice auto-invitation (événement privé)',   'default' => 'Les membres des groupes que vous administrez et qui portent le même nom que l\'événement seront invités automatiquement.'],
        'events.msg.consul_city_notice'        => ['label' => 'Notice ville imposée (Consul)',               'default' => 'En tant que Consul, vos événements sont créés dans votre ville.'],
        'events.msg.empty_my_events'           => ['label' => 'État vide — mes événements',                  'default' => "Vous n'êtes inscrit à aucun événement à venir."],
        'events.msg.empty_suggested'           => ['label' => 'État vide — événements suggérés',             'default' => 'Aucun événement à venir pour le moment.'],
        'events.msg.no_description'            => ['label' => 'Texte absence de description',                'default' => "L'organisateur n'a pas encore ajouté de description."],
        'events.msg.no_attendees'              => ['label' => 'État vide — aucun participant',               'default' => 'Aucun participant pour le moment.'],
        'events.msg.location_tbd'              => ['label' => 'Texte adresse non précisée',                  'default' => "Adresse communiquée par l'organisateur"],
        'events.msg.event_ended'               => ['label' => 'Statut — événement terminé',                  'default' => 'Cet événement est terminé'],
        'events.msg.you_are_organizer'         => ['label' => 'Statut — vous êtes l\'organisateur',          'default' => "Vous êtes l'organisateur"],
        'events.msg.you_are_attending'         => ['label' => 'Statut — vous êtes inscrit',                  'default' => 'Vous êtes inscrit'],
        'events.msg.pending_invitation_notice' => ['label' => 'Notice invitation en attente',                'default' => 'Vous êtes invité à cet événement.'],
        'events.msg.event_full'                => ['label' => 'Statut — événement complet',                  'default' => 'Complet'],
        'events.msg.invite_only_notice'        => ['label' => 'Statut — sur invitation uniquement',          'default' => 'Sur invitation uniquement'],
        'events.msg.delete_event_title'        => ['label' => 'Titre dialogue suppression événement',        'default' => "Supprimer l'événement ?"],
        'events.msg.invite_no_connections'     => ['label' => 'État vide — invitation, aucune connexion',    'default' => "Toutes vos connexions participent déjà, ou vous n'avez pas encore de connexion."],
        'events.msg.payment_processing'        => ['label' => 'Message paiement en cours',                   'default' => 'Paiement en cours…'],
        'events.msg.payment_confirmed'         => ['label' => 'Message paiement confirmé',                   'default' => 'Paiement confirmé ! Inscription en cours…'],
        'events.msg.secure_payment_notice'     => ['label' => 'Notice paiement sécurisé',                    'default' => 'Paiement sécurisé par Stripe'],
    ],

    'Groupes — Boutons' => [
        'groups.btn.create_group'        => ['label' => 'Bouton "Créer un groupe"',                 'default' => 'Créer un groupe'],
        'groups.btn.create_group_submit' => ['label' => 'Bouton "Créer le groupe" (dialogue)',      'default' => 'Créer le groupe'],
        'groups.btn.decline_invitation'  => ['label' => 'Bouton "Décliner" (invitation groupe)',    'default' => 'Décliner'],
        'groups.btn.accept_invitation'   => ['label' => 'Bouton "Accepter" (invitation groupe)',    'default' => 'Accepter'],
        'groups.btn.manage_group'        => ['label' => 'Bouton "Gérer le groupe"',                 'default' => 'Gérer le groupe'],
        'groups.btn.leave_group'         => ['label' => 'Bouton "Quitter" (groupe)',                'default' => 'Quitter'],
        'groups.btn.join_group'          => ['label' => 'Bouton "Rejoindre" (groupe)',              'default' => 'Rejoindre'],
        'groups.btn.delete_group'        => ['label' => 'Bouton "Supprimer le groupe"',             'default' => 'Supprimer le groupe'],
        'groups.btn.publish_poll'        => ['label' => 'Bouton "Publier le sondage"',              'default' => 'Publier le sondage'],
        'groups.btn.create_activity'     => ['label' => 'Bouton "Créer l\'activité"',               'default' => "Créer l'activité"],
        'groups.btn.invite_region'       => ['label' => 'Bouton "Inviter toute ma région"',         'default' => 'Inviter toute ma région'],
        'groups.btn.invite_member'       => ['label' => 'Bouton "Inviter" (ligne membre, dialogue invitation)', 'default' => 'Inviter'],
    ],

    'Groupes — Messages' => [
        'groups.msg.no_invitations'          => ['label' => 'État vide — invitations en attente',        'default' => 'Aucune invitation en attente.'],
        'groups.msg.no_groups_joined'        => ['label' => 'État vide — aucun groupe rejoint',           'default' => "Vous n'êtes membre d'aucun groupe."],
        'groups.msg.no_groups_discover'      => ['label' => 'État vide — aucun groupe à découvrir',       'default' => 'Aucun nouveau groupe à découvrir pour le moment.'],
        'groups.msg.no_search_match'         => ['label' => 'État vide — recherche sans résultat',        'default' => 'Aucun groupe ne correspond à votre recherche.'],
        'groups.msg.wall_empty'              => ['label' => 'État vide — mur du groupe',                  'default' => 'Aucun message pour le moment.'],
        'groups.msg.wall_empty_cta'          => ['label' => 'État vide — mur, incitation (membre)',       'default' => 'Lancez la conversation !'],
        'groups.msg.private_group_label'     => ['label' => 'Étiquette "Groupe privé"',                   'default' => 'Groupe privé'],
        'groups.msg.private_invite_suffix'   => ['label' => 'Texte invitation groupe privé (après le nom)','default' => 'vous invite à rejoindre ce groupe. Acceptez pour voir le mur et participer aux échanges.'],
        'groups.msg.join_to_participate_notice' => ['label' => 'Notice — rejoindre pour participer',      'default' => 'Rejoignez le groupe pour participer aux échanges.'],
        'groups.msg.request_sent'            => ['label' => 'Statut — demande envoyée',                   'default' => 'Demande envoyée'],
        'groups.msg.no_description'          => ['label' => 'Texte absence de description (groupe)',      'default' => 'Aucune description pour le moment.'],
        'groups.msg.invite_search_hint'      => ['label' => 'Indice recherche membre à inviter',          'default' => 'Recherchez un membre par son nom ou son email.'],
        'groups.msg.invite_no_connections'   => ['label' => 'État vide — invitation, aucune connexion',   'default' => "Toutes vos connexions sont déjà membres, ou vous n'avez pas encore de connexion."],
        'groups.msg.delete_group_warning'    => ['label' => 'Avertissement suppression groupe',           'default' => "Les publications et l'historique du groupe seront supprimés. Cette action est définitive."],
    ],

    'Réseau — Boutons' => [
        'members.btn.view_plans'               => ['label' => 'Bouton "Voir les offres"',                 'default' => 'Voir les offres'],
        'members.btn.change_city'              => ['label' => 'Bouton "Changer la ville"',                 'default' => 'Changer la ville'],
        'members.btn.explore_recommendations'  => ['label' => 'Bouton "Explorer « Pour toi »"',            'default' => 'Explorer « Pour toi »'],
    ],

    'Réseau — Messages' => [
        'members.msg.banner_title'        => ['label' => 'Bandeau — titre',                        'default' => "Votre réseau prend de l'élan"],
        'members.msg.banner_subtitle'     => ['label' => 'Bandeau — sous-titre',                    'default' => 'Les profils les plus pertinents pour vous, cette semaine.'],
        'members.msg.name_restricted_notice' => ['label' => 'Notice — noms de famille masqués',     'default' => 'Avec votre offre, seuls les prénoms des membres sont visibles.'],
        'members.msg.empty_contacts'      => ['label' => 'État vide — onglet Contacts',             'default' => "Vous n'avez pas encore de contact."],
        'members.msg.empty_requests'      => ['label' => 'État vide — onglet Demandes',             'default' => 'Aucune demande de connexion en attente.'],
        'members.msg.empty_visitors'      => ['label' => 'État vide — onglet Visiteurs',            'default' => "Personne n'a encore visité votre profil."],
        'members.msg.empty_recommendations' => ['label' => 'État vide — onglet Pour toi',           'default' => 'Aucune suggestion pour le moment.'],
    ],

];
