<?php

namespace Reportico;



/**
 * Class reportico_assignment
 *
 * Identifies instructions for report column output
 * that must be calculated upon report execution. 
 */
class reportico_assignment extends reportico_object
{
	var $query_name;
	var $expression;
	var $criteria;
	var $raw_expression;
	var $raw_criteria;
    
    // Indicates an operation which causes an action rather than setting a value
	var $non_assignment_operation = false;

	function __construct($query_name, $expression, $criteria)
	{
		//echo "ink ".$query_name." ".$expression." ".$criteria."\n<br>";
		$this->raw_expression = $expression;
		$this->raw_criteria = $criteria;
		$this->query_name = $query_name;
		$this->expression = $this->reportico_string_to_php($expression);
		$this->criteria = $this->reportico_string_to_php($criteria);
	}

	// -----------------------------------------------------------------------------
	// Function : reportico_lookup_string_to_php
	// -----------------------------------------------------------------------------
	function reportico_lookup_string_to_php($in_string)
	{
		$out_string = preg_replace('/{([^}]*)}/', 
			'\"".$this->lookup_queries[\'\1\']->column_value."\"', 
			$in_string);

		$cmd = '$out_string = "'.$out_string.'";';
		// echo  "==$cmd===";
		eval($cmd);
		return $out_string;
	}

	// -----------------------------------------------------------------------------
	// Function : reportico_meta_sql_criteria
	// -----------------------------------------------------------------------------
	static function reportico_meta_sql_criteria(&$in_query, $in_string, $prev_col_value = false, $no_warnings = false, $execute_mode = "EXECUTE")
	{
        // Replace user parameters with values
        $external_param1 = get_reportico_session_param("external_param1");
        $external_param2 = get_reportico_session_param("external_param2");
        $external_param3 = get_reportico_session_param("external_param3");
        $external_user = get_reportico_session_param("external_user");

        if ( $external_param1 ) $in_string = preg_replace ("/{EXTERNAL_PARAM1}/", "'".$external_param1."'", $in_string);
        if ( $external_param2 ) $in_string = preg_replace ("/{EXTERNAL_PARAM2}/", "'".$external_param2."'", $in_string);
        if ( $external_param3 ) $in_string = preg_replace ("/{EXTERNAL_PARAM3}/", "'".$external_param3."'", $in_string);
        if ( $external_user ) $in_string = preg_replace ("/{FRAMEWORK_USER}/", "'".$external_user."'", $in_string);
        if ( $external_user ) $in_string = preg_replace ("/{USER}/", "'".$external_user."'", $in_string);

        // Support for limesurvey prefix
        if ( isset($in_query->user_parameters["lime_"]) ) 
        {
            $in_string = preg_replace ("/{lime_}/", $in_query->user_parameters["lime_"], $in_string);
            $in_string = preg_replace ("/{prefix}/", $in_query->user_parameters["lime_"], $in_string);
        }

        // Replace External parameters specified by {USER_PARAM,xxxxx}
		if ( preg_match_all ( "/{USER_PARAM,([^}]*)}/", $in_string, $matches ) )
        {
            foreach ( $matches[0] as $k => $v )
            {
                $param = $matches[1][$k];
                if ( isset($in_query->user_parameters[$param] ) )
                {
                    $in_string = preg_replace("/{USER_PARAM,$param}/", $in_query->user_parameters[$param], $in_string);
                }
                else
                {
		            trigger_error("User parameter $param, specified but not provided to reportico", E_USER_ERROR);
                }
            }
        }

		$looping = true;
		$out_string = $in_string;
		$ct = 0;
		while ( $looping )
		{
			$ct++;
			if ( $ct > 100 )
			{
                if ( !$no_warnings )
				    echo "Problem with SQL cannot resolve Criteria Items<br>";
				break;
			}
			$regpat = "/{([^}]*)/";
			if ( preg_match ( $regpat, $out_string, $matches ) )
			{
				$crit = $matches[1];
				$first = substr($crit, 0, 1);
				$critexp = $crit;
				if ( $first == "=" )
				{
					$crit = substr ( $crit, 1 );
					$critexp = $crit;
                    $clause = ""; 
                    $label = ""; 
					if ( array_key_exists($crit, $in_query->lookup_queries) )
						$clause = $in_query->lookup_queries[$crit]->criteria_summary_text($label,$clause);
					else if ( $cl = get_query_column($crit, $this->query->columns ) )
						if ( $prev_col_value )
							$clause = $cl->old_column_value;
						else
							$clause = $cl->column_value;
					else
					{
						handle_error( "Unknown Criteria Item $crit in Query $in_string");
						return $in_string;
					}
				}
				else
				{
					$eltype = "VALUE";
                    $showquotes = true;
                    $surrounder = false;
                    
					if ( preg_match ( "/([!])(.*)/", $crit, $critel ) )
					{
							$surrounder = $critel[1];
							$crit = $critel[2];
					}
					if ( preg_match ( "/(.*),(.*),(.*)/", $crit, $critel ) )
					{
							$crit = $critel[1];
							$eltype = $critel[2];
							if ( $critel[3] == "false" )
                                $showquotes = false;
					}
					if ( preg_match ( "/(.*);(.*);(.*)/", $crit, $critel ) )
					{
							$crit = $critel[1];
							$eltype = $critel[2];
							if ( $critel[3] == "false" )
                                $showquotes = false;
					}
					if ( preg_match ( "/(.*),(.*)/", $crit, $critel ) )
					{
							$crit = $critel[1];
							if ( $critel[2] == "false" )
                                $showquotes = false;
                            else
							    $eltype = $critel[2];
					}
                    if ( $surrounder == "!" )
                        $showquotes = false;

					if ( array_key_exists($crit, $in_query->lookup_queries) )
					{
						switch ( $eltype )
						{
							case "FULL" :
								$clause = $in_query->lookup_queries[$crit]->get_criteria_clause(true, true, true, false, false, $showquotes);
								break;
	
							case "RANGE1" :
								$clause = $in_query->lookup_queries[$crit]->get_criteria_clause(false, false, false, true, false, $showquotes);
								break;
	
							case "RANGE2" :
								$clause = $in_query->lookup_queries[$crit]->get_criteria_clause(false, false, false, false, true, $showquotes);
								break;
	
							case "VALUE" :
							default :
								$clause = $in_query->lookup_queries[$crit]->get_criteria_clause(false, false, true, false, false, $showquotes);
						}
                        if ( $execute_mode == "MAINTAIN" && !$clause )
                        {
                            $clause = "'DUMMY'";
                        }
					}
					else if ( $cl = get_query_column($crit, $in_query->columns ) )
                    {
							if ( $prev_col_value )
								$clause = $cl->old_column_value;
							else
								$clause = $cl->column_value;
					}
					//else if ( strtoupper($crit) == "REPORT_TITLE" )
					//{
                        //$clause = "go";
					//}
					else
					{
						echo "Unknown Criteria Item $crit in Query $in_string";
						//handle_error( "Unknown Criteria Item $crit in Query $in_string");
						return $in_string;
					}
				}

				if  (!$clause)
				{
					$out_string = preg_replace("/\[[^[]*\{$critexp\}[^[]*\]/", '',  $out_string);
				}
				else
				{
					$out_string = preg_replace("/\{=*$critexp\}/", 
						$clause,
						$out_string);
					$out_string = preg_replace("/\[\([^[]*\)\]/", "\1", $out_string);
				}


			}
			else
				$looping = false;
		}
	

		$out_string = preg_replace("/\[\[/", "<STARTBRACKET>", $out_string);
		$out_string = preg_replace("/\]\]/", "<ENDBRACKET>", $out_string);
		$out_string = preg_replace("/\[/", "", $out_string);
		$out_string = preg_replace("/\]/", "", $out_string);
		$out_string = preg_replace("/<STARTBRACKET>/", "[", $out_string);
		$out_string = preg_replace("/<ENDBRACKET>/", "]", $out_string);
		// echo "<br>Meta clause: $out_string<BR>";

		//$out_string = addcslashes($out_string, "\"");
		//$cmd = trim('$out_string = "'.$out_string.'";');
		//echo $out_string;
		
		//if ( $cmd )
			//eval($cmd);
		return $out_string;
	}

	// -----------------------------------------------------------------------------
	// Function : reportico_string_to_php
	// -----------------------------------------------------------------------------
	function reportico_string_to_php($in_string)
	{
		// first change '(colval)' parameters
		$out_string = $in_string;

		$out_string = preg_replace('/{TARGET_STYLE}/', 
			'$this->target_style', 
			$out_string);

		$out_string = preg_replace('/{TARGET_FORMAT}/', 
			'$this->target_format', 
			$out_string);

		$out_string = preg_replace('/old\({([^}]*)},{([^}]*)}\)/', 
			'$this->old("\1")', 
			$out_string);

		$out_string = preg_replace('/old\({([^}]*)}\)/', 
			'$this->old("\1")', 
			$out_string);

		$out_string = preg_replace('/max\({([^}]*)},{([^}]*)}\)/', 
			'$this->max("\1","\2")', 
			$out_string);

		$out_string = preg_replace('/max\({([^}]*)}\)/', 
			'$this->max("\1")', 
			$out_string);

		$out_string = preg_replace('/min\({([^}]*)},{([^}]*)}\)/', 
			'$this->min("\1","\2")', 
			$out_string);

		$out_string = preg_replace('/min\({([^}]*)}\)/', 
			'$this->min("\1")', 
			$out_string);

		$out_string = preg_replace('/avg\({([^}]*)},{([^}]*)}\)/', 
			'$this->avg("\1","\2")', 
			$out_string);

		$out_string = preg_replace('/avg\({([^}]*)}\)/', 
			'$this->avg("\1")', 
			$out_string);

		$out_string = preg_replace('/sum\({([^}]*)},{([^}]*)}\)/', 
			'$this->sum("\1","\2")', 
			$out_string);

		$out_string = preg_replace('/sum\({([^}]*)}\)/', 
			'$this->sum("\1")', 
			$out_string);

		$out_string = preg_replace('/imagequery\(/', 
			'$this->imagequery(', 
			$out_string);

		$out_string = preg_replace('/reset\({([^}]*)}\)/', 
			'$this->reset("\1")', 
			$out_string);

		$out_string = preg_replace('/changed\({([^}]*)}\)/', 
			'$this->changed("\1")', 
			$out_string);

		$out_string = preg_replace('/groupsum\({([^}]*)},{([^}]*)},{([^}]*)}\)/', 
			'$this->groupsum("\1","\2", "\3")', 
			$out_string);

		//$out_string = preg_replace('/count\(\)/', 
			//'$this->query_count', 
			//$out_string);
		$out_string = preg_replace('/lineno\({([^}]*)}\)/', 
			'$this->lineno("\1")', 
			$out_string);

        if ( preg_match ( '/skipline\(\)/', $out_string ) )
        {
            $this->non_assignment_operation = true;
		    $out_string = preg_replace('/skipline\(\)/', 
			    '$this->skipline()', 
			    $out_string);
        }

        if ( preg_match ( '/apply_style\(.*\)/', $out_string ) )
        {
            $this->non_assignment_operation = true;
		    $out_string = preg_replace('/apply_style\(/', 
			    '$this->apply_style("'.$this->query_name."\",", $out_string);
        }

        if ( preg_match ( '/embed_image\(.*\)/', $out_string ) )
        {
            $this->non_assignment_operation = true;
		    $out_string = preg_replace('/embed_image\(/', 
			    '$this->embed_image("'.$this->query_name."\",", $out_string);
        }

        if ( preg_match ( '/embed_hyperlink\(.*\)/', $out_string ) )
        {
            $this->non_assignment_operation = true;
		    $out_string = preg_replace('/embed_hyperlink\(/', 
			    '$this->embed_hyperlink("'.$this->query_name."\",", $out_string);
        }

		$out_string = preg_replace('/lineno\(\)/', 
			'$this->lineno()', 
			$out_string);

		$out_string = preg_replace('/count\({([^}]*)}\)/', 
			'$this->lineno("\1")', 
			$out_string);

		$out_string = preg_replace('/count\(\)/', 
			'$this->lineno()', 
			$out_string);

		$out_string = preg_replace('/{([^}]*)}/', 
			//'$this->columns[\'\1\']->column_value', 
			'$this->get_query_column_value(\'\1\', $this->columns)', 
			$out_string);

		return $out_string;
	}

}