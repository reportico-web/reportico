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
            "Database Date (for MySql leave as default YYYY-MM-DD)" => "Date de la base ( pour Mysql utilisez YYYY-MM-DD"
			),
		);

$g_report_desc = array ( 
    "fr_fr" => array (
		"configureproject" =>
"
Configure une suite nouveau projet de déclaration.
<P>
Pour créer un nouveau projet de fournir \'alimentation, les paramètres requis et appuyez sur le bouton Exécuter.
<P>
<b>Nom du projet</b><br>
Le nom du projet. Un dossier portant ce nom sera créé sous le dossier des projets dans le répertoire d'installation Reportico
<P>
<b>Titre du projet</b><br>
Un titre qui apparaîtra en haut du menu suite de rapport . à savoir un nom humain-compréhensible pour le projet
<P>
<b>Type de base de données</b><br>
Le type de base de données que vous souhaitez rapporter contre
<P>
<b>Nom d'hôte: numéro de port</b><br>
L'adresse IP ou le nom de l'hôte sur lequel réside la base de données. Pour une base de données sur la même machine que le serveur web 127.0.0.1 utilisation. Pour SQLite ce champ n'est pas utilis. Pour les bases de données PostgreSQL Oracle, Mysql, à l'écoute sur un port non standard, vous pouvez préciser dans le formulaire hostname: port ou IPADDRESS: PORT
<P>
<b>Nom de la base</b><br>
Le nom de la base de données à signaler contre. Bases de données SQLite pour entrer le chemin complet vers le fichier de base de données.
<P>
<b>Nom d'utilisateur et mot de passe</b><br>
Le nom d'utilisateur et mot de passe requis pour se connecter à la base de données
<P>
<b>URL de base</b><br>
Ceci est l'URL équivalente au répertoire d'installation reportico. Cela devrait être laissé tel q\"./\" Cependant cela peut être nécessaire de modifier une valeur complète pathed plus tard si des liens vers des pages Web reportico autres. Dans un tel cas si vous avez placé votre installation reportico dans le répertoire racine du serveur Web et l'a appelé reportico, puis vous devez utiliser http://127.0.0.1/reportico/
<P>
Serveur et protocole</b><br> Lors de la déclaration des bases de données Informix, spécifier le serveur Informix et le protocole de connexion par exemple olsoctcp.
<P>
<b>Personnage de base de donnée</b><br> Encodage Le format d'encodage utilisé pour stocker des caractères dans votre base de données. UTF8 va travailler pour Engligh parler des régions et la plupart des autres cas.
<P>
<b>Encodage de sortie</b><br> Le défaut de sortie de caractères est UTF8. Lors de la sortie des données, la production va être convertis dans ce format avant de rendre le navigateur, visualisateur PDF, etc <P>
<b>Le fichier de style</b><br> feuille de style à utiliser pour contrôler l'apparence Reportico.
<P>
<b>Mot de passe projet</b><br> Choisissez un mot de passe qui doit les utilisateurs doivent entrer pour accéder aux rapports de projet. Laissez ce champ vide pour permettre l'accès au projet, sans un mot de passe.
<P>
<b>Format de la date d'affichage</b><br> Choisissez le format de date que vous souhaitez utiliser pour l'affichage et la saisie des dates
<P>
<b>Format Date de base de données</b><br> Choisissez le format de date que vous utilisez pour stocker les dates dans votre base de données. MySQL utilise YYYYY-MM-JJ
<b> Design Mode sans échec </ b> <br>
Quand il est activé, le mode de conception permettra d'éviter l'entrée d'un code d'utilisateur personnalisé, les affectations, et des instructions SQL (en évitant l'intrusion des commandes PHP dangereuses et l'injection SQL).
Désactivez cette option pour permettre l'accès à ces fonctions. Non disponible en cours de création de projet
"),
		);

$g_report_desc["fr_fr"]["createproject"] = $g_report_desc["fr_fr"]["configureproject"];
?>
