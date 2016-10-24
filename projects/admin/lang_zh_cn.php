<?php
$g_translations = array (
		"zh_cn" => array (
            "Project Name" => "项目名称",
            "Project Title" => "项目名称",
            "Database Type" => "数据库类型",
            "Host Name" => "主机名",
            "Database Name" => "数据库的名称",
            "User Name" => "用户名",
            "Password" => "密码",
            "Base URL" => "基URL",
            "Server (Informix Only)" => "服务器（Informix的）",
            "Protocol (Informix Only)" => "协议（Informix的）",
            "Database<br>Character Encoding" => "数据库字符<BR>编码",
            "Output<br>Character Encoding" => "输出字符编码",
            "Project Language" => "项目语言",
            "Stylesheet" => "样式表",
            "Project Password" => "项目密码",
            "Display Date Format" => "显示的日期格式",
            "Database Date (for MySql leave as default YYYY-MM-DD)" => "数据库日期（针对MySQL作为默认为YYYY-MM-DD离开）",
            "HELP_PROJECT_NAME"=>"项目的名称，将在Reportico安装目录中的”projects“文件夹下创建具有此名称的文件夹",
            "HELP_PROJECT_TITLE"=>"出现在报告套件菜单顶部的标题，即项目的人类可理解的名称",
            "HELP_DATABASE_TYPE"=>"要报告的数据库类型",
            "HELP_DATABASE_HOST"=>"数据库所在主机的IP地址或名称对于与Web服务器位于同一台机器上的数据库，使用127.0.0.1。对于SQLite数据库，保留为默认值。对于Oracle，Mysql，PostgreSQL数据库监听一个非标准端口，可以以HOSTNAME：PORT或IPADDRESS：PORT的形式指定",
            "HELP_DATABASE_NAME"=>"要报告的数据库的名称。对于SQLite数据库，请输入数据库文件的完整路径。",
            "HELP_DATABASE_USER"=>"连接到数据库所需的用户名",
            "HELP_DATABASE_PASSWORD"=>"连接到数据库所需的密码",
            "HELP_DB_ENCODING"=>"用于在数据库中存储字符的编码格式接受缺省值None通常会正常工作，否则UTF8将在英语区域和大多数其他情况下工作您可以随时返回配置页面更改在稍后点",
            "HELP_OUTPUT_ENCODING"=>"默认是UTF8，这通常是最好的。输出数据时，输出将在转换为浏览器，PDF查看器等之前转换为此格式",
            "HELP_PASSWORD"=>"选择用户必须输入以访问项目报告的密码。将此空白留空以允许无密码访问项目。",
            "HELP_LANGUAGE"=>"选择此报告套件应该运行的默认语言。默认情况下，英语是唯一的选择。还有一些其他语言包，您可以在Reportico插件文件夹下面的language / packages文件夹下找到。将任何所需的文件移动到语言文件夹",
            "HELP_DATE_FORMAT"=>"选择要用于显示和输入日期的日期格式",
            "HELP_DB_DATE"=>"选择日期存储在数据库中的日期格式对于Mysql和大多数其他数据库，YYYY-MM-DD的设置是正确的",
            "HELP_SAFE_MODE"=>"当打开时，设计模式将阻止输入自定义用户代码，分配和SQL语句（避免不必要地输入危险的PHP命令和SQL注入）, 关闭此功能以启​​用对这些功能的访问。",
			)
		);

$g_report_desc = array ( 
    "zh_cn" => array (
		"createproject" => "
创建一个新的项目文件夹，您可以在其中创建一组报告。
<br>
您必须至少提供一个项目名称，该名称是用于创建报告的文件夹的名称，以及报告套件的项目标题。
<p>
如果您在网站上向用户提供报告套件，则可以通过设置报告密码来密码保护对报告的访问。 否则留空。
<P>
当你快乐点击Go按钮。
" )
		);

$g_report_desc["zh_cn"]["configureproject"] =
"
更改此项目的配置项。
<br>
您可以更改标题，默认语言，设置项目密码，字符编码和日期格式。
<P>
当你快乐点击Go按钮。
";
?>
