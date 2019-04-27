<?php
$menu_title = SW_PROJECT_TITLE;
$menu = array (
	array ( "report" => "tut1_films.xml", "title" => "<AUTO>" ),
	array ( "report" => "tut2_loanhistory.xml", "title" => "<AUTO>" ),
	array ( "report" => "tut3_monthreturns.xml", "title" => "<AUTO>" ),
	array ( "report" => "tut4_lateness.xml", "title" => "<AUTO>" ),
	array ( "report" => "", "title" => "BLANKLINE" ),
	array ( "report" => "generate_tutorial.xml", "title" => "Generate The Tutorial Database" ),
	array ( "report" => "", "title" => "BLANKLINE" ),
	array ( "report" => "tut1_1_films.xml", "title" => "Film Listing - Tutorial1 Stage1" ),
	array ( "report" => "tut1_2_films.xml", "title" => "Film Listing - Tutorial1 Stage2" ),
	array ( "report" => "tut1_3_films.xml", "title" => "Film Listing - Tutorial1 Stage3" ),
	array ( "report" => "tut1_4_films.xml", "title" => "Film Listing - Tutorial1 Stage4" ),
	array ( "report" => "tut1_5_films.xml", "title" => "Film Listing - Tutorial1 Stage5" ),
	array ( "report" => "tut2_1_loanhistory.xml", "title" => "Loan History Report - Begin the Tutorial" ),
	array ( "report" => "tut3_1_monthreturns.xml", "title" => "Monthly Returns Report - Begin the Tutorial"),
	array ( "report" => "tut4_1_lateness.xml", "title" => "Late Returns Summary - Begin the Tutorial" ),
	);

$dropdown_menu = array(
                    array ( 
                        "project" => "tutorials",
                        "title" => "Listings",
                        "items" => array (
                            array ( "reportfile" => "tut1_films.xml" ),
                            array ( "reportfile" => "tut2_loanhistory.xml" )
                            )
                        ),
                    array ( 
                        "project" => "tutorials",
                        "title" => "Analysis Reports",
                        "items" => array (
                            array ( "reportfile" => "tut3_monthreturns.xml") ,
                            array ( "reportfile" => "tut4_lateness.xml")
                            )
                        ),
                    array ( 
                        "project" => "tutorials",
                        "title" => "Tutorials",
                        "items" => array (
                            array ( "reportfile" => "generate_tutorial.xml") ,
                            array ( "reportfile" => "tut1_1_films.xml") ,
                            array ( "reportfile" => "tut1_2_films.xml") ,
                            array ( "reportfile" => "tut1_3_films.xml") ,
                            array ( "reportfile" => "tut1_4_films.xml") ,
                            array ( "reportfile" => "tut1_5_films.xml") ,
                            array ( "reportfile" => "tut2_1_films.xml") ,
                            array ( "reportfile" => "tut3_1_films.xml") ,
                            array ( "reportfile" => "tut4_1_films.xml") 
                            )
                        ),
                );

?>
