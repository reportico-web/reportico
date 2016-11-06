<?php
$g_translations = array (
		"es_es" => array (
            "Project Name" => "Nom del proyecto",
            "Project Title" => "Título del proyecto",
            "Database Type" => "Tipo de base de datos",
            "Host Name" => "Nom de Anfitrión",
            "Database Name" => "Nom de Base de Datos",
            "User Name" => "Nom de Usuario",
            "Password" => "Contraseña",
            "Base URL" => "URL Base",
            "Server (Informix Only)" => "Servidor (Sóo Informix)",
            "Protocol (Informix Only)" => "Protocolo (Sóo Informix)",
            "Database Character<br>Encoding" => "Base de datos de <br>codificación de caracteres",
            "Output Character Encoding" => "Salida de la codificación de caracteres",
            "Project Language" => "Lenguaje del Proyecto",
            "Stylesheet" => "Hoja de estilo",
            "Project Password" => "Proyecto Contraseña",
            "Display Date Format" => "Formato de Fecha",
            "Database Date (for MySql leave as default YYYY-MM-DD)" => "Fecha de la base de datos (MySql para dejarlo como predeterminado AAAA-MM-DD)",
            "HELP_PROJECT_NAME" => "El nombre del proyecto. Una carpeta con este nombre se creará en la carpeta 'proyectos' dentro del directorio de instalación Reportico",
            "HELP_PROJECT_TITLE" => "Un título que aparecerá en la parte superior del menú de conjunto de informes .. es decir, un nombre humano-comprensible para el proyecto",
            "HELP_DATABASE_TYPE" => "El tipo de base de datos que desea informar en contra.",
            "HELP_DATABASE_HOST" => "La dirección IP o el nombre del host en el que reside la base de datos. Para una base de datos en la misma máquina que el uso del servidor web 127.0.0.1. Para bases de datos SQLite dejan como predeterminado. Para Oracle, MySQL, bases de datos PostgreSQL escuchando en un puerto no estándar, puede especificar en forma hostname: port o IPADDRESS: puerto »",
            "HELP_DATABASE_NAME" => "El nombre de la base de datos para informar en contra. Para bases de datos SQLite introduzca la ruta completa al archivo de base de datos.",
            "HELP_DATABASE_USER" => "El nombre de usuario requerida para conectarse a la base de datos",
            "HELP_DATABASE_PASSWORD" => "La contraseña requerida para conectarse a la base de datos",
            "HELP_DB_ENCODING" => "El formato de codificación utilizado para almacenar caracteres en su base de datos. Al aceptar el defecto None normalmente trabajar de otro modo UTF8 trabajará para las regiones de habla inglesa y la mayoría de los otros casos. Usted puede siempre volver a la página de configuración para cambiarlo en un momento posterior. ",
            "HELP_OUTPUT_ENCODING" => "El valor predeterminado es UTF8 que normalmente es la mejor. Cuando la salida de datos, la salida será convertido a este formato antes de emitir el navegador, visor de PDF, etc.",
            "HELP_PASSWORD" => "Elija una contraseña que debe introducir el usuario debe tener acceso a los informes de los proyectos. Deje este espacio en blanco para permitir el acceso al proyecto sin una contraseña.",
            "HELP_LANGUAGE" => "Elegir el idioma por defecto de este conjunto de informes se debe ejecutar. Por defecto Inglés es la única opción. Hay algunos otros paquetes de idiomas disponibles, que se encuentra debajo de la lengua / paquetes carpeta en algún lugar por debajo de la carpeta de complementos Reportico. mueva cualquier los requeridos a la carpeta del idioma ",
            "HELP_DATE_FORMAT" => "Seleccione el formato de fecha que desea utilizar para mostrar e introducir las fechas",
            "HELP_DB_DATE" => "Seleccione el formato de fecha que las fechas se almacenan en la base de datos. Para la mayoría de MySQL y otras bases de datos, la configuración de AAAA-MM-DD es correcta",
            "HELP_SAFE_MODE" => "Cuando se activa, el modo de diseño evitará la entrada de código de usuario personalizado, asignaciones, y las sentencias SQL (evitando la entrada no deseada de comandos PHP y peligrosas de inyección de SQL).
Desactive esta opción para permitir el acceso a estas funciones. ",
			),
		);

$g_report_desc = array ( 
    "es_es" => array (
		"createproject" => "
Crear una carpeta de nuevos proyectos en los que se puede crear un conjunto de informes.
<br>
Debe proporcionar al menos un nombre de proyecto que es el nombre de la carpeta utilizada para la creación de informes, y un título para su proyecto conjunto de informes.
<P>
Si va a proporcionar el conjunto de informes a los usuarios en un sitio web que le gustaría proteger con contraseña el acceso a los informes mediante el establecimiento de una contraseña informe. De lo contrario dejar en blanco.
<P>
Cuando esté satisfecho pulsa el botón Ir.
"),
		);

$g_report_desc["es_es"]["configureproject"] = 
"
Cambiar los elementos de configuración para este proyecto.
<br>
Puede modificar el título, el idioma por defecto, establecer una contraseña de proyecto, la codificación de caracteres y el formato de fecha.
<P>
Cuando esté satisfecho pulsa el botón Ir.
";
?>
