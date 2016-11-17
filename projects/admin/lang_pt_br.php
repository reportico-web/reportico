<?php
$g_translations = array (
        "pt_br" => array (
            "Project Name" => "Nome do Projeto",
            "Project Title" => "Título do projeto",
            "Database Type" => "Tipo de banco de dados",
            "Host Name" => "Host Name",
            "Database Name" => "Nome do banco de dados",
            "User Name" => "Nome de Usuário",
            "Password" => "Senha",
            "Base URL" => "Base URL",
            "Server (Informix Only)" => "Apenas Informix) Server",
            "Protocol (Informix Only)" => "Apenas Informix) Protocol",
            "Database<br>Character Encoding" => "Character Database <br> Codificação",
            "Output<br>Character Encoding" => "Codificação de caracteres de saída",
            "Project Language" => "Projecto de Línguas",
            "Stylesheet" => "Stylesheet",
            "Project Password" => "Project Password",
            "Display Date Format" => "Display Data Format",
            "Database Date (for MySql leave as default YYYY-MM-DD)" => "Data de banco de dados (para MySql deixar como padrão AAAA-MM-DD)",

            "Specify Poject Name" => "Especificar nome do projeto",
            "Specify Project Title" => "Especifique Título do Projeto",

            "HELP_PROJECT_NAME" => "O nome do projeto Uma pasta com esse nome. Será criado sob a pasta 'projectos' no diretório de instalação Reportico",
            "HELP_PROJECT_TITLE" => "Um título que aparecerá na parte superior do menu suíte relatório .. isto é um nome de homem-compreensível para o projeto",
            "HELP_DATABASE_TYPE" => "O tipo de banco de dados que você deseja denunciar contra.",
            "HELP_DATABASE_HOST" => "O endereço IP ou o nome do host no qual o banco de dados reside. Para um banco de dados na mesma máquina que o 127.0.0.1 uso webserver. Para bancos de dados SQLite deixar como padrão. Para Oracle, MySQL, bancos de dados PostgreSQL escutando em uma porta não padrão, você pode especificar, no formato máquina: pORT ou IPADDRESS: pORT ",
            "HELP_DATABASE_NAME" => "O nome do banco de dados para relatar contra. Para bancos de dados SQLite digite o caminho completo para o arquivo de banco de dados.",
            "HELP_DATABASE_USER" => "O nome de usuário necessário para conectar ao banco de dados",
            "HELP_DATABASE_PASSWORD" => "A senha necessária para se conectar ao banco de dados",
            "HELP_DB_ENCODING" => "O formato de codificação usado para armazenar os caracteres em seu banco de dados. Aceitar o padrão de None normalmente trabalhar de outra forma UTF8 irá trabalhar para regiões de língua inglesa ea maioria dos outros casos. Você pode sempre voltar à página de configuração para alterá-lo num ponto mais tarde. ",
            "HELP_OUTPUT_ENCODING" => "O padrão é UTF8 que é normalmente o melhor. Quando saída de dados, a saída será convertido para este formato antes da renderização do navegador, visualizador de PDF etc.",
            "HELP_PASSWORD" => "Escolha uma senha que deve os usuários devem inserir para acessar os relatórios do projeto. Deixe em branco para permitir o acesso ao projeto sem uma senha.",
            "HELP_LANGUAGE" => "Escolha o idioma padrão esta suite relatório deve ser executado. Por padrão o Inglês é a única opção. Existem alguns outros pacotes de idiomas disponíveis que você vai encontrar no idioma / pacotes de pasta em algum lugar abaixo da pasta do plugin Reportico. mova quaisquer aqueles necessários para a pasta do idioma ",
            "HELP_DB_DATE" => "Escolha o formato de data que as datas são armazenadas no banco de dados. Para o MySQL ea maioria dos outros bancos de dados, a definição de AAAA-MM-DD é correta",
            "HELP_DATE_FORMAT" => "Escolha o formato da data que você gostaria de usar para exibir e inserir datas",
            "HELP_SAFE_MODE" => "Quando ligado, modo de design irá impedir a entrada de código de usuário personalizada, as atribuições e instruções SQL (evitando a entrada indesejada de comandos PHP perigosas e injeção de SQL).
Desligue isto para permitir o acesso a essas funções. ",
            )
        );


$g_report_desc = array (
    "pt_br" => array (
        "createproject" =>
"
Crie uma pasta de novos projetos em que você pode criar um conjunto de relatórios.
<br>
Você deve fornecer, no mínimo, um nome de projeto que é o nome da pasta usada para a criação de relatórios, e um título projeto para a sua suíte de relatório.
<P>
Se você está fornecendo a suíte de relatório para usuários em um site que você pode gostar de senha proteger o acesso aos relatórios definindo uma senha relatório. Caso contrário, deixe em branco.
<P>
Quando você está feliz apertar o botão Go.
<P>
"),
        );

$g_report_desc [ "pt_br"] [ "configureproject"] =
"
Alterar itens de configuração para este projeto.
<br>
Você pode alterar o título, o idioma padrão, defina uma senha de projeto, a codificação de caracteres e o formato da data.
<P>
Quando você está feliz apertar o botão Go.
<P>
";
?>
