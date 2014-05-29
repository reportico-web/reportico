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
            "Database Date (for MySql leave as default YYYY-MM-DD)" => "数据库日期（针对MySQL作为默认为YYYY-MM-DD离开）"
			)
		);

$g_report_desc = array ( 
    "zh_cn" => array (
		"configureproject" => "
配置项目申报套件。
<P>
要创建一个新的项目，提供供应所需的参数，并按下
<br>
<b>执行</b><br>按钮。
<P>
<b>项目名称</b><br>
该项目的名称。将这个名字的文件夹下创建的Reportico安装指南</b>文件夹内的<b>项目
<P>

<b>项目名称</b><br>
一个标题将出现在报告套房菜单的顶端......即该项目的人理解的名称
<P>
oo
的<b>数据库类型</b><br>
你想对报告的数据库类型
<P>

<b>主机名：端口号</b><br>
数据库所在的主机的IP地址或名称。 127.0.0.1作为网络服务器使用同一台机器上的数据库。对于SQLite此字段不用于。对于甲骨文，MySQL和PostgreSQL数据库的非标准端口上侦听，你可以指定形式的主机名称：端口或IP地址：端口
<P>

的<b>数据库名称</b><br>参考
报告对数据库的名称。对于SQLite数据库输入到数据库文件的完整路径。

<P>

的<b>用户名</b><br> <b>密码</b>参考
登录名和密码需要连接到数据库
<P>
的<b>基本URL </b><br>参考
这是相当于的reportico安装目录的URL。
这应该离开。“/”。然而，这可能需要，改为完全pathed值后，如果从其他网页链接到reportico。在这种情况下，如果您放置您的reportico安装Web服务器的根目录下，并呼吁它<b> reportico </b>，那么你会使用http://127.0.0.1/reportico/
<P>

服务器</b>和<b>议定书“</b><br>
报告对Informix数据库时，指定Informix服务器和连接协议，例如olsoctcp。
<P>
的<b>数据库字符编码</b><br>
编码格式，用于存储在数据库中的字符。 UTF8的将Engligh发言地区和其他大多数情况下工作。
<P>
<b>输出字符编码</b><br>
默认是UTF8。输出数据时，输出将被转换为这种格式之前渲染的浏览器，PDF阅读器等。
<P>
的<b>样式表</b><br>
用于控制Reportico外观样式表文件。
<P>
<b>项目密码</b><br>
选择一个密码必须用户必须输入访问该项目的报告。离开这个空白项目允许无密码的访问。
<P>
<b>显示的日期格式</b><br>
选择日期格式，你想为显示和进入日期<P>的使用
<b>数据库日期格式</b><br>
您使用存储在数据库中的日期选择日期格式。 MySQL使用YYYYY-MM-DD的<P>
<B>安全设计模式</ B>参考
开启时，设计模式将防止用户自定义代码，分配，和SQL语句（避免不必要的侵入危险的PHP命令和SQL注入）的条目。
关闭这个功能，以便对这些功能的访问。并不适用于在创建项目
</DIV>

" )
		);

$g_report_desc["zh_cn"]["createproject"] = $g_report_desc["zh_cn"]["configureproject"];
?>
