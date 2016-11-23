<?php namespace reportico\reportico\components;
$g_translations = array (
"fi_fi" => array ( 
    "Specify Project Name" => "Valitse Projektin nimi",
    "Specify Project Title" => "Valitse Projektin otsikko",
    "HELP_PROJECT_NAME" => "Projektin nimi: Saman niminen hakemisto luodaan 'projects' hakemiston alle Reportico asennushakemistossa. ",
    "HELP_PROJECT_TITLE" => "Otsikko, jota käytetään Raportti Valikossa. Ts. selkokielinen nimi projektille. ",
    "HELP_DATABASE_TYPE" => "Tietokannan tyyppi, josta raportit muodostetaan. ",
    "HELP_DATABASE_HOST" => "Tietokantapalvelimen IP osoite tai nimi. Jos tietokanta on samassa koneessa kuin nettipalvelin, käytä 127.0.0.1   SQLite tietokanta: jätä oletus voimaan. Oracle, MySQL, PostgreSQL tietokannat: jos käytät epästandardia porttia, niin voit määritellä ne erikseen. HOSTNAME:PORT tai IPADDRESS:PORT",
    "HELP_DATABASE_NAME" => "Tietokannan nimi. SQLite: käytä koko polkua tietokantatiedostolle.",
    "HELP_DATABASE_USER" => "Tietokantayhteydelle vaadittava käyttäjänimi",
    "HELP_DATABASE_PASSWORD" => "Tietokantayhteydelle vaadittava salasana",
    "HELP_DB_ENCODING" => "Koodaus, jolla aakkosmerkit tallennetaan tietokantaan. Käytä UTF8 koodausta skandinaavisille merkeille. Voit aina palata asetusten sivulle ja vaihtaa koodauksen.",
    "HELP_OUTPUT_ENCODING" => "Oletuksena on UTF8, joka on yleensä toimiva. Kun tulostetaan tekstiä, aakkosmerkit muutetaan tähän koodaukseen, ennenkuin ne välitetään selaimelle, PDF ohjelmaan jne.",
    "HELP_PASSWORD" => "Valitse salasana, mikä käyttäjän pitää antaa ennenkuin voi käsitellä projektin tietoja. Jätä tyhjäksi, jos haluat, että kaikki voivat käsitellä projektia.",
    "HELP_LANGUAGE" => "Valitse kieli, jota käytetään Raporttien käsittelyssä. Oletuksena on Englanti. Hakemistossa language/packs on valmiiksi käännetyjä kieliversioita, eli paketteja, mukaanlukien Suomi (fi_fi). Siirrä tämä paketti language hakemiston alle.",
    "HELP_DB_DATE" => "Valitse päivämäärän muoto, millä päiväykset tallennetaan tietokantaan. MySQL ja monessa muussa tietokannassa, käytä muotoa YYYY-MM-DD",
    "HELP_DATE_FORMAT" => "Valitse päiväyksen muoto, mitä käytetään kun päivämäärä näytetään ruudulla ja kalenterin valinnoissa. Suomessa käytetään yleensä muotoa DD-MM-YYYY",
    "HELP_SAFE_MODE" => "Jos laitat valinnan tähän (SAFE ON), niin suunnittelutila ei anna tallentaa kaikkia muokattuja valintoja ja SQL kyselyjä (näin vältetään vaarallisten PHP ja SQL komentojen suorittaminen). Jätä tämä valitsematta (SAFE OFF) kun haluat muokata omia komentojasi.",
    )
);


$g_report_desc = array ( 
"fi_fi" => array (
    "createproject" =>
    "
    Luo uusi Projektihakemisto, jonne voit tallentaa valikoiman Raportteja.
    <br>
    Sinun täytyy vähintään antaa Projektille nimi, joka tulee samalla olemaan hakemiston nimi,<br>mihin Raportit tallennetaan.
    <p>
    Jos annat käyttäjille mahdollisuuden muokata Raportteja verkon kautta,<br>voit suojata ne salasanalla. Muussa tapauksessa jätä tyhjäksi.
    <P>
    Kun olet valmis, klikkaa <font color=green><b>Suorita</b></font> painiketta.
    <P>
    "),
);

$g_report_desc["fi_fi"]["configureproject"] = 
    "
    Muokkaa tämän projektin asetuksia.
    <br>
    Voit muokata otsikkoa, kieliasetuksia, salasanaa, aakkosmerkkien koodausta ja päivämäärän muotoa.
    <P>
    Kun olet valmis, klikkaa <font color=green><b>Suorita</b></font> painiketta.
    <P>
    ";
?>
