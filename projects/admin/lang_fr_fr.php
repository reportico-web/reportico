<?php
$g_translations = array (
		"fr_fr" => array (
            "Project Name" => "Nom du Projet",
            "Project Title" => "Titre du Projet",
            "Database Type" => "Type de base de données",
            "Host Name" => "Nom de l'hôte",
            "Database Name" => "Nom de base de données",
            "User Name" => "Nom d'utilisateur",
            "Password" => "Mot de passe",
            "Base URL" => "URL de Reportico",
            "Server (Informix Only)" => "Serveur (Informix Seulement)",
            "Protocol (Informix Only)" => "Protocole (Informix Seulement)",
            "Database Character<br>Encoding" => "Encodage de la base",
            "Output Character Encoding" => "Encodage de Sortie",
            "Project Language" => "Langue du Projet",
            "Stylesheet" => "Feuille de style",
            "Project Password" => "Mot de passe du project",
            "Display Date Format" => "Format de Date de Sortie",
            "Database Date (for MySql leave as default YYYY-MM-DD)" => "Date de la base ( pour Mysql utilisez YYYY-MM-DD",

            "HELP_PROJECT_NAME" => "Le nom du projet. Un dossier avec ce nom sera créé sous le dossier« projets »dans le répertoire d'installation Reportico",
            "HELP_PROJECT_TITLE" => "Un titre qui apparaîtra en haut du menu de suite de rapports .. à-dire un nom humain compréhensible pour le projet",
            "HELP_DATABASE_TYPE" => "Le type de base de données que vous souhaitez signaler contre.",
            "HELP_DATABASE_HOST" => "L'adresse IP ou le nom de l'hôte où réside la base. Pour une base de données sur la même machine que l'utilisation du serveur web 127.0.0.1. Pour les bases de données SQLite partent comme valeur par défaut. Pour, Mysql, bases de données Oracle PostgreSQL écoute sur un port non standard, vous pouvez spécifier dans le formulaire HOSTNAME: PORT ou IPADDRESS: PORT ",
            "HELP_DATABASE_NAME" => "Le nom de la base de données à signaler contre. Pour les bases de données SQLite entrer le chemin complet vers le fichier de base de données.",
            "HELP_DATABASE_USER" => "Le nom d'utilisateur requis pour se connecter à la base de données",
            "HELP_DATABASE_PASSWORD" => "Le mot de passe requis pour se connecter à la base de données",
            "HELP_DB_ENCODING" => "Le format de codage utilisé pour stocker des caractères dans votre base de données. Accepter la valeur par défaut de None fonctionnera normalement autrement UTF8 travaillera pour les régions anglophones et la plupart des autres cas. Vous pouvez toujours revenir à la page de configuration pour le changer à un stade ultérieur. ",
            "HELP_OUTPUT_ENCODING" => "Par défaut est UTF8 qui est normalement le meilleur. Lors de la sortie des données, la sortie sera convertie à ce format avant de rendre le navigateur, visionneuse PDF, etc.",
            "HELP_PASSWORD" => "Choisissez un mot de passe qui doivent les utilisateurs doivent entrer pour accéder aux rapports de projet. Laissez ce champ vide pour permettre l'accès au projet sans un mot de passe.",
            "HELP_LANGUAGE" => "Choisissez la langue par défaut ce rapport Suite devrait fonctionner. Par défaut l'anglais est le seul choix. Il y a quelques autres packs de langues disponibles que vous trouverez dans la langue / paquets dossier quelque part en dessous du dossier plugin Reportico. Déplacez toutes celles requises dans le dossier de la langue ",
            "HELP_DATE_FORMAT" => "Choisissez le format de date que vous souhaitez utiliser pour afficher et saisir les dates",
            "HELP_DB_DATE" => "Choisissez le format de date que les dates sont stockées dans votre base de données. Pour Mysql et la plupart des autres bases de données, le réglage de AAAA-MM-JJ est correct",
            "HELP_SAFE_MODE" => "Lorsqu'elle est activée, le mode de conception permettra d'éviter l'entrée du code d'utilisateur personnalisé, des affectations et des instructions SQL (en évitant l'entrée non désirée des commandes PHP dangereuses et injection SQL).
Désactivez cette option pour permettre l'accès à ces fonctions. ",
			),
		);


$g_report_desc = array (
    "fr_fr" => array (
        "createproject" =>
"
Créer un nouveau dossier de projets dans lesquels vous pouvez créer un ensemble de rapports.
<br>
Vous devez fournir au minimum un nom de projet qui est le nom du dossier utilisé pour la création de rapports, et un titre de projet pour votre rapport de bains.
<P>
Si vous fournissez le rapport Suite aux utilisateurs sur un site web que vous aimeriez mot de passe protéger l'accès aux rapports en définissant un mot de passe du rapport. Sinon, laissez le champ vide.
<P>
Lorsque vous êtes satisfait cliquez sur le bouton Confirmer.
"),
);

$g_report_desc["fr_fr"]["configureproject"] =
"
Modifier les éléments de configuration pour ce projet.
<br>
Vous pouvez modifier le titre, la langue par défaut, définissez un mot de passe du projet, le codage de caractères et le format de date.
<P>
Lorsque vous êtes satisfait cliquez sur le bouton Confirmer.

";

?>
