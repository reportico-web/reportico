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
            "Database Date (for MySql leave as default YYYY-MM-DD)" => "Fecha de la base de datos (MySql para dejarlo como predeterminado AAAA-MM-DD)"
			),
		);

$g_report_desc = array ( 
    "es_es" => array (
		"configureproject" => "
Crea un conjunto de proyectos de información nueva.
<P>
Para crear un nuevo proyecto de suministro de la fuente de los parámetros requeridos y pulse el botón Ejecutar.
<P>
<b>Nombre del proyecto</b><br>
El nombre del proyecto. Una carpeta con ese nombre se creará en la carpeta de proyectos en el directorio de instalación Reportico
<P>
<b>Título del proyecto</b><br>
Un título que aparecerá en la parte superior del menú de un conjunto de informes .. es decir, un nombre humano, comprensible para el proyecto
<P>
<b>Base de datos de tipo</b><br>
El tipo de base de datos que desea que le informe sobre
<P>
<b>Nombre de host: Número de puerto</b><br>
La dirección IP o el nombre del host donde reside la base de datos. Para una base de datos en la misma máquina que el servidor web de uso 127.0.0.1. Para SQLite este campo no se utiliza. Para bases de datos PostgreSQL Oracle, MySQL, escuchando en un puerto no estándar, puede especificar en la forma hostname: puerto o IP address: PUERTO
<P>
<b>Nombre de base de datos</b><br>
El nombre de la base de datos para informar en contra. Para bases de datos SQLite introduzca la ruta completa al archivo de base de datos.
<P>
<b>Nombre de Usuario y Contraseña</b><br>
El nombre de usuario y contraseña necesarios para conectarse a la base de datos
<P>
<b>URL base</b><br>
Esta es la URL equivalente al directorio de instalación reportico. Esto se debe dejar como\"./\". Sin embargo, esto puede ser necesario cambiar a un valor totalmente pathed más tarde, si la vinculación de reportico desde otras páginas web. En tal caso, si se ha colocado la instalación reportico en el directorio raíz del servidor web y la llamó reportico, entonces deberías usar http://127.0.0.1/reportico/
<P>
<b>Servidor y el Protocolo </b><br> Al informar sobre las bases de datos Informix, especifique el servidor Informix por ejemplo, y el protocolo de conexión olsoctcp.
<P>
<b>Base de datos de caracteres de codificació </b><br>n El formato de codificación utilizado para almacenar caracteres en su base de datos. UTF8 va a trabajar para Engligh hablando regiones y la mayoría de los otros casos.
<P>
<b>Carácter de salid </b><br> Por defecto de codificación es UTF8. Cuando la salida de datos, la producción se convertirán a este formato antes de dibujar el navegador, visor de PDF, etc
<P>
<b>Hojas de Estilo</b><br> El archivo de hoja de estilo que se utiliza para controlar la apariencia Reportico.
<P>
<b>Contraseña del proyect</b><br>o Elija una contraseña que debe introducir los usuarios deben tener acceso a los informes del proyecto. Dejar en blanco para permitir el acceso al proyecto sin una contraseña.
<P>
<b>Formato de pantalla</b><br> Fecha Elija el formato de fecha que desea utilizar para visualizar y entrar en las fechas
<P>
<b>Formato de base de datos</b><br> Fecha Elija el formato de fecha que se utiliza para almacenar las fechas en su base de datos. MySQL usa YYYYY-MM-DD
<b> modo de diseño seguro </ b> <br>
Cuando está encendido, el modo de diseño evitará la entrada de código de usuario personalizada, tareas, y las sentencias de SQL (para evitar la entrada no deseada de los peligrosos comandos PHP y SQL Injection).
Desactive esta opción para permitir el acceso a estas funciones. No está disponible en en la creación del proyecto
"),
		);

$g_report_desc["es_es"]["createproject"] = $g_report_desc["es_es"]["configureproject"];
?>
