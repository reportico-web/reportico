<?php
$g_translations = array (
        "it_it" => array (
            "Project Name" => "Nome del progetto",
            "Project Title" => "Titolo del progetto",
            "Database Type" => "Tipo di database",
            "Host Name" => "Host Name",
            "Database Name" => "Nome del database",
            "User Name" => "Nome utente",
            "Password" => "Parola d'ordine",
            "Base URL" => "URL di base",
            "Server (Informix Only)" => "Server (Solo Informix)",
            "Protocol (Informix Only)" => "(Solo Informix) Protocol",
            "Database<br>Character Encoding" => "Database dei caratteri <br> Codifica",
            "Output<br>Character Encoding" => "Codifica dei caratteri di uscita",
            "Project Language" => "Lingue del progetto",
            "Stylesheet" => "Foglio di stile",
            "Project Password" => "Password Progetto",
            "Display Date Format" => "Data di formato di visualizzazione",
            "Database Date (for MySql leave as default YYYY-MM-DD)" => "Data di database (MySql per lasciare il valore predefinito YYYY-MM-DD)",
            "Specify Project Name" => "Specifica nome di progetto",
            "Specify Project Title" => "Specifica Titolo del progetto",


            "HELP_PROJECT_NAME" => "Il nome del progetto Una cartella con questo nome. Verrà creato nella cartella 'progetti' all'interno della directory di installazione Reportico",
            "HELP_PROJECT_TITLE" => "Un titolo che apparirà nella parte superiore del menu suite di rapporto .. cioè un nome umano comprensibile per il progetto",
            "HELP_DATABASE_TYPE" => "Il tipo di database che si desidera segnalare contro.",
            "HELP_DATABASE_HOST" => "L'indirizzo IP o il nome dell'host in cui risiede il database. Per un database sulla stessa macchina del 127.0.0.1 uso server web. Per i database SQLite lasciano come predefinito. Per Oracle, MySQL, database PostgreSQL in ascolto su una porta non standard, è possibile specificare nella forma di hostname: port o IPADDRESS: pORT ",
            "HELP_DATABASE_NAME" => "Il nome del database per segnalare contro. Per i database SQLite inserire il percorso completo del file di database.",
            "HELP_DATABASE_USER" => "Il nome utente richiesto per la connessione al database",
            "HELP_DATABASE_PASSWORD" => "La password necessaria per la connessione al database",
            "HELP_DB_ENCODING" => "Il formato di codifica utilizzato per memorizzare i caratteri nel database. Accettando il predefinita Nessuno normalmente funzionerà altrimenti UTF8 funzionerà per le regioni di lingua inglese e la maggior parte dei casi. È sempre possibile tornare alla pagina di configurazione per cambiare in un secondo momento. ",
            "HELP_OUTPUT_ENCODING" => "Il valore predefinito è UTF8 che normalmente è il migliore. In caso di uscita dei dati, l'uscita verrà convertito in questo formato prima del rendering del browser, PDF viewer, ecc",
            "HELP_PASSWORD" => "Scegli una password che deve gli utenti devono inserire per accedere alle relazioni di progetto. Lasciare il campo vuoto per consentire l'accesso al progetto senza una password.",
            "HELP_LANGUAGE" => "Scegli la lingua predefinita questa suite relazione dovrebbe funzionare. Per impostazione predefinita inglese è l'unica scelta. Ci sono alcuni altri pacchetti di lingua disponibili che si trova sotto la lingua / cartella confezioni da qualche parte sotto la cartella plug Reportico. spostare tutti quelli necessari per la cartella della lingua ",
            "HELP_DB_DATE" => "Scegli il formato della data che le date sono memorizzate nel database. Per MySQL e la maggior parte degli altri database, l'impostazione di AAAA-MM-DD è corretto",
            "HELP_DATE_FORMAT" => "Scegli il formato della data che si desidera utilizzare per la visualizzazione e inserire le date",
            "HELP_SAFE_MODE" => "Quando è attivata, la modalità di progettazione consentirà di evitare l'ingresso di codice utente personalizzato, le assegnazioni, e le istruzioni SQL (evitando l'ingresso indesiderato di comandi PHP pericolose e SQL injection).
Disattivarlo per consentire l'accesso a queste funzioni. ",
            )
            );


$g_report_desc = array (
    "it_it" => array (
        "createproject" =>
"
Creare una cartella nuovi progetti in cui è possibile creare un set di report.
<br>
È necessario fornire al minimo un nome di progetto, che è il nome della cartella utilizzata per la creazione di report, e un titolo del progetto per la vostra suite rapporto.
<P>
Se si sta fornendo la suite report per gli utenti su un sito web come si potrebbe proteggere con password l'accesso ai rapporti impostando una password rapporto. In caso contrario, lasciare in bianco.
<P>
Quando si è soddisfatti premere il pulsante Andare.
<P>
"),
    );

$g_report_desc [ "it_it"] [ "configureproject"] =
"
Modificare elementi di configurazione per questo progetto.
<br>
È possibile modificare il titolo, la lingua di default, impostare una password di progetto, la codifica dei caratteri e il formato della data.
<P>
Quando si è soddisfatti premere il pulsante Andare.
<P>
";
?>
