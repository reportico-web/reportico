<?php

namespace Reportico;



/**
 * Class CriteriaColumn
 *
 * Identifies a criteria item. Holds all the necessary information
 * to allow users to input criteria values including criteria presentation
 * information. Holds database query parameters to criteria selection
 * lists can be generated from the database when the criteria type is LOOKUP
 */

class CriteriaColumn extends reportico_query_column
{
	var $defaults = array();
	var $defaults_raw = "";
	var $value;
	var $range_start;
	var $range_end;
	var $criteria_type;
	var $_use;
	var $criteria_display;
	var $display_group;
	var $criteria_help;
	var $expand_display;
	var $required;
	var $hidden;
	var $order_type;
	var $list_values = array();
	var	$first_criteria_selection = true;
    var $parent_reportico = false;
    var $criteria_summary;
    
    // For criteria that is linked to in another report
    // Specifies both the report to link to and the criteria item
    // a blank criteria item means all criterias are pulled in
    var $link_to_report = false;
    var $link_to_report_item = false;
	
	var $criteria_types = array (
						"FROMDATE",
						"TODATE",
						"FROMTIME",
						"TOTIME",
						"ANY",
						"NOINPUT",
						"ANYCHAR",
						"TEXTFIELD",
						"SQLCOMMAND",
						"ANYINT",
						"LOOKUP",
						"DATERANGE",
						"DATE",
						"SWITCH"
						);

	function __construct
	(
            $parent_reportico,
			$query_name,
			$table_name,
			$column_name, 
			$column_type,
			$column_length,
			$column_mask,
			$in_select
	)
	{
        $this->parent_reportico = $parent_reportico;

		reportico_query_column::__construct(	
			$query_name,
			$table_name,
			$column_name, 
			$column_type,
			$column_length,
			$column_mask,
			$in_select);
	}

	function set_lookup($table, $return_columns, $display_columns)
	{
	}

	// -----------------------------------------------------------------------------
	// Function : execute_criteria_lookup
	// -----------------------------------------------------------------------------
	function execute_criteria_lookup($in_is_expanding = false, $no_warnings = false)
	{
		global $g_code_area;

		$g_code_area = "Criteria ".$this->query_name;
		$rep = new ReportArray();

		$this->lookup_query->rowselection = true;
		$this->lookup_query->set_datasource($this->datasource);
		$this->lookup_query->targets = array();
		$this->lookup_query->add_target($rep);
		$this->lookup_query->build_query($in_is_expanding, $this->query_name, false, $no_warnings);
		$this->lookup_query->execute_query($this->query_name);
		$g_code_area = "";
	}


    // -----------------------------------------------------------------------------
	// -----------------------------------------------------------------------------
	function criteria_summary_text(&$label, &$value)
    {
        $label = "";
        $value = "";

        if ( isset($this->criteria_summary) && $this->criteria_summary )
        {
            $label = $this->derive_attribute("column_title", $this->query_name);
            $value = $this->criteria_summary;
        }
        else
        {
        if ( get_request_item($name."_FROMDATE_DAY", "" ) )
        {
            $label = $this->derive_attribute("column_title", $this->query_name);
            $label = sw_translate($label);
            $mth = get_request_item($name."_FROMDATE_MONTH","") + 1;
            $value = get_request_item($name."_FROMDATE_DAY","")."/".
            $mth."/".
            get_request_item($name."_FROMDATE_YEAR","");
            if ( get_request_item($name."_TODATE_DAY", "" ) )
            {
                $mth = get_request_item($name."_TODATE_MONTH","") + 1;
                $value .= "-";
                $value .= get_request_item($name."_TODATE_DAY","")."/".
                $mth."/".
                get_request_item($name."_TODATE_YEAR","");
            }
        }
        else if ( get_request_item("MANUAL_".$name."_FROMDATE", "" ) )
        {
            $label = $this->derive_attribute("column_title", $this->query_name);
            $label = sw_translate($label);
            $value = get_request_item("MANUAL_".$name."_FROMDATE","");
            if ( get_request_item("MANUAL_".$name."_TODATE", "" ) )
            {
                $value .= "-";
                $value .= get_request_item("MANUAL_".$name."_TODATE");
            }

        }
        else if ( get_request_item("HIDDEN_".$name."_FROMDATE", "" ) )
        {
            $label = $this->derive_attribute("column_title", $this->query_name);
            $label = sw_translate($label);
            $value = get_request_item("HIDDEN_".$name."_FROMDATE","");
            if ( get_request_item("HIDDEN_".$name."_TODATE", "" ) )
            {
                $value .= "-";
                $value .= get_request_item("HIDDEN_".$name."_TODATE");
            }

        }
        else if ( get_request_item("EXPANDED_".$name, "" ) )
        {
            $label = $this->derive_attribute("column_title", $this->query_name);
            $label = sw_translate($label);
            $value .= implode(get_request_item("EXPANDED_".$name, ""),",");
        }
        else if ( get_request_item("MANUAL_".$name, "" ) )
        {
            $label = $this->derive_attribute("column_title", $this->query_name);
            $label = sw_translate($label);
            $value .= get_request_item("MANUAL_".$name, "");
        }
        }
    }

	// -----------------------------------------------------------------------------
	// Function : criteria_summary_display
    //
    // For a given criteria item that has been checked to identify the values
    // that would be passed to the main query, this returns the summary of user
    // selected values for displaying in the criteria summary at top of report
	// -----------------------------------------------------------------------------
	function criteria_summary_display()
	{
		$text = "";

		$type = $this->criteria_display;

		$value_string = "";

		$params = array();
		$manual_params = array();
		$hidden_params = array();
		$expanded_params = array();
		$manual_override = false;

        if ( get_request_item("MANUAL_".$this->query_name."_FROMDATE", "" ) )
        {
            $this->criteria_summary = get_request_item("MANUAL_".$this->query_name."_FROMDATE","");
            if ( get_request_item("MANUAL_".$this->query_name."_TODATE", "" ) )
            {
                $this->criteria_summary .= "-";
                $this->criteria_summary .= get_request_item("MANUAL_".$this->query_name."_TODATE");
            }
            return;
        }

        if ( get_request_item("HIDDEN_".$this->query_name."_FROMDATE", "" ) )
        {
            $this->criteria_summary = get_request_item("HIDDEN_".$this->query_name."_FROMDATE","");
            if ( get_request_item("HIDDEN_".$this->query_name."_TODATE", "" ) )
            {
                $this->criteria_summary .= "-";
                $this->criteria_summary .= get_request_item("HIDDEN_".$this->query_name."_TODATE");
            }
            return;
        }

		if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
			if ( array_key_exists($this->query_name, $_REQUEST) )
			{
					$params = $_REQUEST[$this->query_name];
					if ( !is_array($params) )
						$params = array ( $params );
			}

		$hidden_params = array();
		if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
			if ( array_key_exists("HIDDEN_".$this->query_name, $_REQUEST) )
			{
					$hidden_params = $_REQUEST["HIDDEN_".$this->query_name];
					if ( !is_array($hidden_params) )
						$hidden_params = array ( $hidden_params );
			}

		$manual_params = array();
		if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
        {
			if ( array_key_exists("MANUAL_".$this->query_name, $_REQUEST) )
			{
				$manual_params = explode(',',$_REQUEST["MANUAL_".$this->query_name]);
				if ( $manual_params )
				{
					$hidden_params = $manual_params;
					$manual_override = true;
                    $value_string = $_REQUEST["MANUAL_".$this->query_name];
				}
			}
        }

		$expanded_params = array();
		if ( array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
		{
				$expanded_params = $_REQUEST["EXPANDED_".$this->query_name];
				if ( !is_array($expanded_params) )
					$expanded_params = array ( $expanded_params );
		}

        if ( $this->criteria_type == "LIST" )
        {
            $checkedct = 0;
                $res =& $this->list_values;
                $text = "";
                if ( !$res )
                {
                    $text = "";
                }
                else
                {
                    reset($res);
                    $k = key($res);
                    for ($i = 0; $i < count($res); $i++ )
                    {
                        $line =&$res[$i];
                        $lab = $res[$i]["label"];
                        $ret = $res[$i]["value"];
                        $checked=false;
            
                        if ( in_array($ret, $params) )
                            $checked = true;
           
                        if ( in_array($ret, $hidden_params) )
                            $checked = true;
          
                        if ( in_array($ret, $expanded_params) )
                            $checked = true;
         
                        if ( $checked )
                        {
                                if ( $checkedct++ )
                                    $text .= ",";
                                $text .=  $lab;
                        }
                    }
                    $this->criteria_summary = $text;
                    return;
                }
        }

        $txt = "";
		$res =& $this->lookup_query->targets[0]->results;
		if ( !$res )
		{
			$res = array();
			$k = 0;
		}
		else
		{
			reset($res);
			$k = key($res);
            $checkedct = 0;
		    for ($i = 0; $i < count($res[$k]); $i++ )
		    {
			    $line =&$res[$i];
			    foreach ( $this->lookup_query->columns as $ky => $col )
			    {
				    if ( $col->lookup_display_flag )
				    {
					    $lab = $res[$col->query_name][$i];
				    }
				    if ( $col->lookup_return_flag )
					    $ret = $res[$col->query_name][$i];
				    if ( $col->lookup_abbrev_flag )
					    $abb = $res[$col->query_name][$i];
			    }
			    $checked=false;

			    if ( in_array($ret, $params) )
				    $checked = true;

			    if ( in_array($ret, $hidden_params) && !$manual_override )
				    $checked = true;

			    if ( in_array($ret, $expanded_params) )
				    $checked = true;

			    if ( in_array($abb, $hidden_params) && $manual_override )
				    $checked = true;

			    if ( $checked )
                {
                        if ( $checkedct++ )
                            $text .= ",";
   					    $text .=  $lab;
                }
		    }
		}

		if ( array_key_exists("EXPAND_".$this->query_name, $_REQUEST) ||
			array_key_exists("EXPANDCLEAR_".$this->query_name, $_REQUEST) ||
			array_key_exists("EXPANDSELECTALL_".$this->query_name, $_REQUEST) ||
			array_key_exists("EXPANDSEARCH_".$this->query_name, $_REQUEST) ||
			$this->criteria_display == "NOINPUT" )
		{
			$tag = $value_string;
			if ( strlen($tag) > 40 )
				$tag = substr($tag, 0, 40)."...";

			if ( !$tag )
				$tag = "ANY";

			$text .= $tag;
		}
		else if ( $this->criteria_display == "ANYCHAR" || $this->criteria_display == "TEXTFIELD" )
		{
			$txt =  $value_string;
		}

        $this->criteria_summary = $text;
	}


	// -----------------------------------------------------------------------------
	// Function : set_criteria_list
	//
	// Generates a criteria list item by taking a string of list labels and values
	// seaprated by commas and each item separated by =
	// -----------------------------------------------------------------------------
	function set_criteria_list($in_list)
	{
		if ( $in_list )
		{
            $choices = array();
            if ( $in_list == "{laravelconnections}" )
            {
                $choices[] = "Existing Laravel Connection=existingconnection";
                if (isset($this->parent_reportico) && $this->parent_reportico->available_connections )
                {
                    foreach ( $this->parent_reportico->available_connections as $k => $v )
                        $choices[] = "Database '$k'=byname_$k";
                }

                //$choices[] = "MySQL=pdo_mysql";
                //$choices[] = "PostgreSQL with PDO=pdo_pgsql";
                //$choices[] = "Informix=pdo_informix";
                //$choices[] = "Oracle without PDO (Beta)=oci8";
                //$choices[] = "Oracle with PDO (Beta)=pdo_oci";
                //$choices[] = "Mssql (with DBLIB/MSSQL PDO)=pdo_mssql";
                //$choices[] = "Mssql (with SQLSRV PDO)=pdo_sqlsrv";
                //$choices[] = "SQLite3=pdo_sqlite3";
                //$choices[] = "No Database=none";
			    $this->criteria_list = $in_list;
            }
            else
            if ( $in_list == "{connections}" )
            {
                foreach ( $this->available_connections as $k => $v )
                {
                   $choices[] = $k."=".$k;
                }
			    $this->criteria_list = $in_list;
            }
            else
            if ( $in_list == "{languages}" )
            {
                $langs = available_languages();
                foreach ( $langs as $k => $v )
                {
                   $choices[] = template_xlate($v["value"])."=".$v["value"];
                }
			    $this->criteria_list = $in_list;
            }
            else
            {
			    $this->criteria_list = $in_list;
			    if ( !is_array($in_list) )
				    $choices = explode(',', $in_list);
            }

			foreach ( $choices as $items )
			{
				$itemval = explode('=', $items);
				if ( count ( $itemval ) > 1 )
				{
					$this->list_values[] = array ( "label" => $itemval[0],
								"value" => $itemval[1] );
				}
			}
		}
	}

	// -----------------------------------------------------------------------------
	// Function : set_criteria_defaults
	// -----------------------------------------------------------------------------
	function set_criteria_defaults($in_default, $in_delimiter = false)
	{
		if ( !$in_delimiter )
			$in_delimiter = ",";

		$this->defaults_raw = $in_default;
		$this->defaults = preg_split("/".$in_delimiter."/", $this->derive_meta_value($in_default));
	}

	// -----------------------------------------------------------------------------
	// Function : set_criteria_lookup
	// -----------------------------------------------------------------------------
	function set_criteria_lookup(&$lookup_query)
	{
		$this->lookup_query = $lookup_query;
	}

	// -----------------------------------------------------------------------------
	// Function : set_criteria_required
	// -----------------------------------------------------------------------------
	function set_criteria_required($criteria_required)
	{
		$this->required = $criteria_required;
	}

	// -----------------------------------------------------------------------------
	// Function : set_criteria_display_group
	// -----------------------------------------------------------------------------
	function set_criteria_display_group($criteria_display_group)
	{
		$this->display_group = $criteria_display_group;
	}

	// -----------------------------------------------------------------------------
	// Function : set_criteria_hidden
	// -----------------------------------------------------------------------------
	function set_criteria_hidden($criteria_hidden)
	{
		$this->hidden = $criteria_hidden;
	}


	// -----------------------------------------------------------------------------
	// Function : set_criteria_type
	// -----------------------------------------------------------------------------
	function set_criteria_type($criteria_type)
	{
		$this->criteria_type = $criteria_type;
	}

	// -----------------------------------------------------------------------------
	// Function : set_criteria_help
	// -----------------------------------------------------------------------------
	function set_criteria_help($criteria_help)
	{
		$this->criteria_help = $criteria_help;
	}

	// -----------------------------------------------------------------------------
	// Function : set_criteria_link
	// -----------------------------------------------------------------------------
	function set_criteria_link_report($in_report, $in_report_item)
	{
		$this->link_to_report = $in_report;
		$this->link_to_report_item = $in_report_item;
	}


	// -----------------------------------------------------------------------------
	// Function : set_criteria_input
	// -----------------------------------------------------------------------------
	function set_criteria_input($in_source, $in_display, $in_expand_display = false, $use = "")
	{
		$this->criteria_type = $in_source;
		$this->criteria_display = $in_display;
		$this->expand_display = $in_expand_display;
		$this->_use = $use;
	}


	// -----------------------------------------------------------------------------
	// Function : collate_request_date
	// -----------------------------------------------------------------------------
	function collate_request_date($in_query_name, $in_tag, $in_default, $in_format)
	{
		$retval = $in_default;
		if ( array_key_exists($this->query_name."_".$in_tag."_DAY", $_REQUEST) )
		{
            if ( !class_exists("DateTime", false ) )
            {
                handle_error("This version of PHP does not have the DateTime class. Must be PHP >= 5.3 to use date criteria");
                return $retval;
            }
			$dy = $_REQUEST[$this->query_name."_".$in_tag."_DAY"];
			$mn = $_REQUEST[$this->query_name."_".$in_tag."_MONTH"] + 1;
			$yr = $_REQUEST[$this->query_name."_".$in_tag."_YEAR"];
			$retval = sprintf("%02d-%02d-%04d", $dy, $mn, $yr);

			$datetime = DateTime::createFromFormat("d-m-Y", $retval);
			$in_format = get_locale_date_format ( $in_format );
			$retval =$datetime->format ( $in_format );
		}
		return($retval);
	}

	// -----------------------------------------------------------------------------
	// Function : date_display
	// -----------------------------------------------------------------------------
	function & date_display()
	{

		$text = "";
		$this->range_start = $this->range_end = "";
		$this->range_start = $this->column_value;

		if ( !array_key_exists("clearform", $_REQUEST) && array_key_exists("MANUAL_".$this->query_name."_FROMDATE", $_REQUEST) )
		{
			$this->range_start = $_REQUEST["MANUAL_".$this->query_name."_FROMDATE"];
			$this->range_start = $this->collate_request_date($this->query_name, "FROMDATE", $this->range_start, SW_PREP_DATEFORMAT);
		}
		else
		if ( !array_key_exists("clearform", $_REQUEST) && array_key_exists("HIDDEN_".$this->query_name."_FROMDATE", $_REQUEST) )
		{
			$this->range_start = $_REQUEST["HIDDEN_".$this->query_name."_FROMDATE"];
			$this->range_start = $this->collate_request_date($this->query_name, "FROMDATE", $this->range_start, SW_PREP_DATEFORMAT);
		}
		else
		{
			if ( count($this->defaults) == 0 )
			{
				$this->defaults[0] = "TODAY";
			}
			if ( $this->defaults[0] )
			{
                $dummy="";
                if ( !convert_date_range_defaults_to_dates("DATE", $this->defaults[0], $this->range_start, $dummy) )
                    trigger_error( "Date default '".$this->defaults[0]."' is not a valid date. Should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR );
			}
            unset ( $_REQUEST["HIDDEN_".$this->query_name."_FROMDATE"] );
            unset ( $_REQUEST["HIDDEN_".$this->query_name."_TODATE"] );
		}

		$this->range_start = parse_date($this->range_start,false, SW_PREP_DATEFORMAT);
		$text .= $this->format_date_value($this->query_name.'_FROMDATE', $this->range_start, SW_PREP_DATEFORMAT );

		return $text;

	}

	// -----------------------------------------------------------------------------
	// Function : daterange_display
	// -----------------------------------------------------------------------------
	function & daterange_display()
	{

		$text = "";
		$this->range_start = $this->range_end = "";

		if ( !array_key_exists("clearform", $_REQUEST) && array_key_exists("MANUAL_".$this->query_name."_FROMDATE", $_REQUEST) )
		{

			$this->range_start = $_REQUEST["MANUAL_".$this->query_name."_FROMDATE"];
			$this->range_start = $this->collate_request_date($this->query_name, "FROMDATE", $this->range_start, SW_PREP_DATEFORMAT);
		}
		else
		if ( !array_key_exists("clearform", $_REQUEST) && array_key_exists("HIDDEN_".$this->query_name."_FROMDATE", $_REQUEST) )
		{
			$this->range_start = $_REQUEST["HIDDEN_".$this->query_name."_FROMDATE"];
			$this->range_start = $this->collate_request_date($this->query_name, "FROMDATE", $this->range_start, SW_PREP_DATEFORMAT);
		}
		else
		{
            // User reset form or first time in, set defaults and clear existing form info
			if ( count($this->defaults) == 0 )
				$this->defaults[0] = "TODAY-TODAY";

			if ( $this->defaults[0] )
			{
                if ( !convert_date_range_defaults_to_dates("DATERANGE", $this->defaults[0], $this->range_start, $this->range_end) )
                    trigger_error( "Date default '".$this->defaults[0]."' is not a valid date range. Should be 2 values separated by '-'. Each one should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR );

                unset ( $_REQUEST["MANUAL_".$this->query_name."_FROMDATE"] );
                unset ( $_REQUEST["MANUAL_".$this->query_name."_TODATE"] );
                unset ( $_REQUEST["HIDDEN_".$this->query_name."_FROMDATE"] );
                unset ( $_REQUEST["HIDDEN_".$this->query_name."_TODATE"] );
			}
		}

        if ( !$this->range_start )
            $this->range_end = "TODAY";

		$this->range_start = parse_date($this->range_start, false, SW_PREP_DATEFORMAT);
		$text .= $this->format_date_value($this->query_name.'_FROMDATE', $this->range_start, SW_PREP_DATEFORMAT );

		$text .= "&nbsp;- ";

		if ( array_key_exists("MANUAL_".$this->query_name."_TODATE", $_REQUEST) )
		{
			$this->range_end = $_REQUEST["MANUAL_".$this->query_name."_TODATE"];
			$this->range_end = $this->collate_request_date($this->query_name, "TODATE", $this->range_end, SW_PREP_DATEFORMAT);
		}
		else if ( array_key_exists("HIDDEN_".$this->query_name."_TODATE", $_REQUEST) )
		{
			$this->range_end = $_REQUEST["HIDDEN_".$this->query_name."_TODATE"];
			$this->range_end = $this->collate_request_date($this->query_name, "TODATE", $this->range_end, SW_PREP_DATEFORMAT);
		}

        if ( !$this->range_end )
            $this->range_end = "TODAY";

		$this->range_end = parse_date($this->range_end, false, SW_PREP_DATEFORMAT);
		$text .= $this->format_date_value($this->query_name.'_TODATE', $this->range_end, SW_PREP_DATEFORMAT);
		return $text;
	}

	// -----------------------------------------------------------------------------
	// Function : format_date_value
	// -----------------------------------------------------------------------------
	function format_date_value($in_tag, $in_value, $in_label)
	{

		$text = "";

        if ( !$in_value )
            return $text;

		$in_label = get_locale_date_format ( $in_label );


 

		$dy_tag = $in_tag."_DAY";
		$mn_tag = $in_tag."_MONTH";
		$yr_tag = $in_tag."_YEAR";

		$tag = "";
		$tag .= '<input  type="hidden" name="HIDDEN_'.$in_tag.'"';
		$tag .= ' size="'.($this->column_length).'"';
		$tag .= ' maxlength="'.$this->column_length.'"';
		$tag .= ' value="'.$in_value.'">';
		$text .= $tag;

		if ( AJAX_ENABLED )
		{
			$tag = "";

       			if ( preg_match ( "/TODATE/", $in_tag ) )
              			$tag .= "";
			$tag .= '<input  class="'.$this->lookup_query->getBootstrapStyle('textfield').'swDateField" id="swDateField_'.$in_tag.'" style="z-index: 1000" type="text" name="MANUAL_'.$in_tag.'"';
			$tag .= ' size="20"';
			$tag .= ' maxlength="20"';
			$tag .= ' value="'.$in_value.'">';
			$text .= $tag;
			return $text;
		}

		switch ( $this->criteria_display )
		{
			case "YMDFIELD":
			case "MDYFIELD":
			case "DMYFIELD":
			case "DMYFORM":

				$dyinput = '<SELECT name="'.$dy_tag.'">';
				for ( $ct = 1; $ct <= 31; $ct++ )
				{
					$checked="";
					if ( $ct == (int)$dy )
						$checked="selected";
		
					$dyinput .= '<OPTION '.$checked.' label="'.$ct.'" value="'.$ct.'">'.$ct.'</OPTION>';
				}
				$dyinput .= '</SELECT>';
		
				$mtinput = '<SELECT name="'.$mn_tag.'">';
				$cal = array  ( sw_translate('January'), sw_translate('February'), sw_translate('March'), sw_translate('April'), sw_translate('May'), sw_translate('June'),
					sw_translate('July'), sw_translate('August'), sw_translate('September'), sw_translate('October'), sw_translate('November'), sw_translate('December') );
				for ( $ct = 0; $ct <= 11; $ct++ )
				{
					$checked="";
					if ( $ct == $mn - 1 )
						$checked="selected";
		
					$mtinput .= '<OPTION '.$checked.' label="'.$cal[$ct].'" value="'.$ct.'">'.$cal[$ct].'</OPTION>';
				}
				$mtinput .= '</SELECT>';
		
				$yrinput = '<SELECT name="'.$yr_tag.'">';
				for ( $ct = 2000; $ct <= 2020; $ct++ )
				{
					$checked="";
					if ( $ct == $yr )
						$checked="selected";
		
					$yrinput .= '<OPTION '.$checked.' label="'.$ct.'" value="'.$ct.'">'.$ct.'</OPTION>';
				}
				$yrinput .= '</SELECT>';

				switch ( $this->criteria_display )
				{
					case "YMDFIELD":
						$text .= $yrinput . $mtinput . $dyinput;
						break;

					case "MDYFIELD":
						$text .= $mtinput . $dyinput . $yrinput;
						break;

					case "DMYFIELD":
					case "DMYFORM":
					default:
						$text .= $dyinput . $mtinput . $yrinput;
						break;
				}

				break;

				default:
					$tag = "";

					if ( preg_match ( "/TODATE/", $in_tag ) )
						$tag .= "";
						$tag .= '<input  type="text" name="MANUAL_'.$in_tag.'"';
						$tag .= ' size="20"';
					//$tag .= ' maxlength="'.$this->column_length.'"';
						$tag .= ' maxlength="20"';
						$tag .= ' value="'.$in_value.'">';
						$text .= $tag;


		}
		return $text;
		
	}

	// -----------------------------------------------------------------------------
	// Function : list_display
	// -----------------------------------------------------------------------------
	function & list_display($in_is_expanding)
	{
		$text = "";
		if ( $in_is_expanding )
		{	
			$tag_pref = "EXPANDED_";
			$type = $this->expand_display;
		}
		else
		{	
			$tag_pref = "";
			$type = $this->criteria_display;
		}

		$value_string = "";

		$params = array();
		$manual_params = array();
		$hidden_params = array();
		$expanded_params = array();

        if ( !$this->list_values )
        {
            trigger_error("'$this->query_name' is defined as a custom list criteria type without any list values defined", E_USER_ERROR);
        }

		if ( !array_key_exists("clearform", $_REQUEST) )
		{
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists($this->query_name, $_REQUEST) )
				{
						$params = $_REQUEST[$this->query_name];
						if ( !is_array($params) )
							$params = array ( $params );
				}

			$hidden_params = array();
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists("HIDDEN_".$this->query_name, $_REQUEST) )
				{
						$hidden_params = $_REQUEST["HIDDEN_".$this->query_name];
						if ( !is_array($hidden_params) )
							$hidden_params = array ( $hidden_params );
				}

			$manual_params = array();
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists("MANUAL_".$this->query_name, $_REQUEST) )
				{
					$manual_params = explode(',',$_REQUEST["MANUAL_".$this->query_name]);
					if ( $manual_params )
						$hidden_params = $manual_params;
				}

			// If this is first time into screen and we have defaults then
			// use these instead
			if ( !$params && !$hidden_params && get_reportico_session_param("firstTimeIn") )
			{
				$hidden_params = $this->defaults;
				$manual_params = $this->defaults;
			}

			$expanded_params = array();
			if ( array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
			{
					$expanded_params = $_REQUEST["EXPANDED_".$this->query_name];
					if ( !is_array($expanded_params) )
						$expanded_params = array ( $expanded_params );
			}
		}
		else
		{
			$hidden_params = $this->defaults;
			$manual_params = $this->defaults;
		}

		switch ( $type )
		{
				case "NOINPUT":
				case "ANYCHAR":
				case "TEXTFIELD":
 						$text .= '<SELECT style="display:none" name="'."HIDDEN_".$this->query_name.'[]" size="1" multiple>';
						$text .= '<OPTION selected label="ALL" value="(ALL)">ALL</OPTION>';
						//break;

				case "MULTI":

		                $res =& $this->list_values;
						$k = key($res);
						$multisize = 4;
						if ( $res && count($res[$k]) > 4 )
							$multisize = count($res[$k]);
                        if ( isset ( $res[$k] ) )
						    if ( count($res[$k]) >= 10 )
							    $multisize = 10;
 						$text .= '<SELECT class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelect" name="'.$tag_pref.$this->query_name.'[]" size="'.$multisize.'" multiple>';
						break;

				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
		                $res =& $this->list_values;
						$k = key($res);
						$multisize = 4;
						if ( $res && count($res[$k]) > 4 )
							$multisize = count($res[$k]);
                        if ( isset ( $res[$k] ) )
						    if ( count($res[$k]) >= 10 )
							    $multisize = 10;
                        if ( $type == "SELECT2MULTIPLE" )
 						    $text .= '<SELECT class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelect2" name="'.$tag_pref.$this->query_name.'[]" size="'.$multisize.'" multiple>';
                        else
 						    $text .= '<SELECT class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelect2" name="'.$tag_pref.$this->query_name.'[]" size="'.$multisize.'" >';
					    $text .= '<OPTION></OPTION>';
						break;

				case "CHECKBOX":
				case "RADIO":
						break;

				default:
 						$text .= '<SELECT class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelectRegular" name="'.$tag_pref.$this->query_name.'">';
						break;
		}

		$check_text = "";
		switch ( $type )
		{
			case "MULTI":
			case "DROPDOWN":
			case "ANYCHAR":
			case "TEXTFIELD":
			case "NOINPUT":
				$check_text = "selected";
				break;

			default:
				$check_text = "checked";
				break;
		}

		// If clear has been pressed we dont want any list items selected
		if ( $this->submitted('EXPANDCLEAR_'.$this->query_name) ) 
			$check_text = "";
			
		// If select all has been pressed we want all highlighted
		$selectall = false;
		if ( $this->submitted('EXPANDSELECTALL_'.$this->query_name) ) 
			$selectall = true;

		$res =& $this->list_values;
		if ( !$res )
		{
			$res = array();
			$k = 0;
		}
		else
		{
			reset($res);
			$k = key($res);
		for ($i = 0; $i < count($res); $i++ )
		{
			$line =&$res[$i];
			$lab = $res[$i]["label"];
			$ret = $res[$i]["value"];
			$checked="";

			if ( in_array($ret, $params) )
				$checked = $check_text;

			if ( in_array($ret, $hidden_params) )
				$checked = $check_text;

			if ( in_array($ret, $expanded_params) )
				$checked = $check_text;

			if ( $selectall )
				$checked = $check_text;

			if ( $checked != "" )
				if ( !$value_string )
					$value_string = $lab;
				else
					$value_string .= ",".$lab;

			switch ( $type )
			{
				case "MULTI":
					$text .= '<OPTION label="'.$lab.'" value="'.$ret.'" '.$checked.'>'.$lab.'</OPTION>';
					break;

				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
					$text .= '<OPTION label="'.$lab.'" value="'.$ret.'" '.$checked.'>'.$lab.'</OPTION>';
					break;

				case "RADIO":
    				$text .= '<INPUT type="radio" name="'.$tag_pref.$this->query_name.'" value="'.$ret.'" '.$checked.'>'.sw_translate($lab).'<BR>';
					break;

				case "CHECKBOX":
    					$text .= '<INPUT type="checkbox" name="'.$tag_pref.$this->query_name.'[]" value="'.$ret.'" '.$checked.'>'.sw_translate($lab).'<BR>';
					break;

				default:
   					$text .= '<OPTION label="'.$lab.'" value="'.$ret.'" '.$checked.'>'.$lab.'</OPTION>';
					break;
				}

			}
		}

		switch ( $type )
		{
				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
 						$text .= '</SELECT>';
						break;

				case "MULTI":
 						$text .= '</SELECT>';
						break;

				case "CHECKBOX":
				case "RADIO":
						break;

				default:
 						$text .= '</SELECT>';
						break;
		}

		if ( !$in_is_expanding )
		{
		
			if ( array_key_exists("EXPAND_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDCLEAR_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDSELECTALL_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDSEARCH_".$this->query_name, $_REQUEST) ||
				$this->criteria_display == "NOINPUT" )
			//if ( $this->criteria_display == "NOINPUT" )
			{
				$tag = $value_string;
				if ( strlen($tag) > 40 )
					$tag = substr($tag, 0, 40)."...";
	
				if ( !$tag )
					$tag = "ANY";
	
				$text .= '<br>'.$tag;
			}
			else if ( $this->criteria_display == "ANYCHAR" || $this->criteria_display == "TEXTFIELD" )
			{
				$tag = "";
				$tag .= '<br><input  class="'.$this->lookup_query->getBootstrapStyle('textfield').'swPrpTextField" type="text" name="MANUAL_'.$this->query_name.'"';
				$tag .= ' size="50%"';
				$tag .= ' value="'.$value_string.'">';
				$tag .= '<br>';
				$text .= $tag;
			}
			else if ( $this->criteria_display == "SQLCOMMAND" )
			{
				$tag = "";
				$tag .= '<br><textarea  cols="70" rows="20" class="'.$this->lookup_query->getBootstrapStyle('textfield').'swPrpTextField" type="text" name="MANUAL_'.$this->query_name.'">';
				$tag .= $value_string;
				$tag .= "</textarea>";
			}
		}

		return $text;
	}
	// -----------------------------------------------------------------------------
	// Function : lookup_ajax
	// -----------------------------------------------------------------------------
	function & lookup_ajax($in_is_expanding)
	{

		$text = "";
		if ( $in_is_expanding )
		{	
			$tag_pref = "EXPANDED_";
			$type = $this->expand_display;
		}
		else
		{	
			$tag_pref = "";
			$type = $this->criteria_display;
		}

		$value_string = "";

		$params = array();
		$manual_params = array();
		$hidden_params = array();
		$expanded_params = array();
		$manual_override = false;

		if ( !array_key_exists("clearform", $_REQUEST) )
		{
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists($this->query_name, $_REQUEST) )
				{
						$params = $_REQUEST[$this->query_name];
						if ( !is_array($params) )
							$params = array ( $params );
				}

			$hidden_params = array();
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists("HIDDEN_".$this->query_name, $_REQUEST) )
				{
						$hidden_params = $_REQUEST["HIDDEN_".$this->query_name];
						if ( !is_array($hidden_params) )
							$hidden_params = array ( $hidden_params );
				}

			$manual_params = array();
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists("MANUAL_".$this->query_name, $_REQUEST) )
				{
					$manual_params = explode(',',$_REQUEST["MANUAL_".$this->query_name]);
					if ( $manual_params )
					{
						$hidden_params = $manual_params;
						$manual_override = true;
					}
				}

			// If this is first time into screen and we have defaults then
			// use these instead
			if ( !$hidden_params && get_reportico_session_param("firstTimeIn") )
			{
				$hidden_params = $this->defaults;
				$manual_params = $this->defaults;
			}

			$expanded_params = array();
			if ( array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
			{
					$expanded_params = $_REQUEST["EXPANDED_".$this->query_name];
					if ( !is_array($expanded_params) )
						$expanded_params = array ( $expanded_params );
			}
		}
		else
		{
			$hidden_params = $this->defaults;
			$manual_params = $this->defaults;
			$params = $this->defaults;
		}

		switch ( $type )
		{
				case "NOINPUT":
				case "ANYCHAR":
				case "TEXTFIELD":
 						$text .= '<SELECT style="display:none" name="'."HIDDEN_".$this->query_name.'[]" size="0" multiple>';
						break;

				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
						$text .= '{"items": [';
						break;

				case "MULTI":
						$multisize = 12;
						$res =& $this->lookup_query->targets[0]->results;
						$k = key($res);
						$multisize = 4;
						if ( $res && count($res[$k]) > 4 )
							$multisize = count($res[$k]);
                        if ( isset ( $res[$k] ) )
						    if ( count($res[$k]) >= 10 )
							    $multisize = 10;
						if ( $in_is_expanding )
							$multisize = 12;
						$text .= '<SELECT class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelect" name="'.$tag_pref.$this->query_name.'[]" size="'.$multisize.'" multiple>';
						break;

				case "CHECKBOX":
				case "RADIO":
						break;

				default:
 						$text .= '<SELECT class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelectRegular" name="'.$tag_pref.$this->query_name.'">';
						break;
		}

		$check_text = "";
		switch ( $type )
		{
			case "MULTI":
			case "DROPDOWN":
			case "ANYCHAR":
			case "TEXTFIELD":
			case "NOINPUT":
				$check_text = "selected";
				break;

			default:
				$check_text = "checked";
				break;
		}

		// If clear has been pressed we dont want any list items selected
		if ( $this->submitted('EXPANDCLEAR_'.$this->query_name) ) 
			$check_text = "";
			
		// If select all has been pressed we want all highlighted
		$selectall = false;
		if ( $this->submitted('EXPANDSELECTALL_'.$this->query_name) ) 
			$selectall = true;

		$res =& $this->lookup_query->targets[0]->results;
		if ( !$res )
		{
			$res = array();
			$k = 0;
		}
		else
		{
			reset($res);
			$k = key($res);
		for ($i = 0; $i < count($res[$k]); $i++ )
		{
			$line =&$res[$i];
			foreach ( $this->lookup_query->columns as $ky => $col )
			{
				if ( $col->lookup_display_flag )
				{
					$lab = $res[$col->query_name][$i];
				}
				if ( $col->lookup_return_flag )
					$ret = $res[$col->query_name][$i];
				if ( $col->lookup_abbrev_flag )
					$abb = $res[$col->query_name][$i];
				
			}
       			//$text .= '<OPTION label="'.$ret.'" value="'.$ret.'">'.$lab.'</OPTION>';
			$checked="";

			if ( in_array($ret, $params) )
			{
				$checked = $check_text;
			}

			if ( in_array($ret, $hidden_params) && !$manual_override )
			{
				$checked = $check_text;
			}

			if ( in_array($ret, $expanded_params) )
			{
				$checked = $check_text;
			}

			if ( in_array($abb, $hidden_params) && $manual_override )
			{
				$checked = $check_text;
			}

			if ( $selectall )
			{
				$checked = $check_text;
			}

			if ( $checked != "" )
				if ( !$value_string && $value_string != "0" )
					$value_string = $abb;
				else
					$value_string .= ",".$abb;

			switch ( $type )
			{
				case "MULTI":
   					$text .= '<OPTION label="'.$lab.'" value="'.$ret.'" '.$checked.'>'.$lab.'</OPTION>';
					break;

				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
                    if ( $i > 0 )
                        $text .= ",";
   					$text .= "{\"id\":\"$ret\", \"text\":\"$lab\"}";
					break;

				case "RADIO":
    				$text .= '<INPUT type="radio" name="'.$tag_pref.$this->query_name.'" value="'.$ret.'" '.$checked.'>'.$lab.'<BR>';
					break;

				case "CHECKBOX":
    					$text .= '<INPUT type="checkbox" name="'.$tag_pref.$this->query_name.'[]" value="'.$ret.'" '.$checked.'>'.$lab.'<BR>';
					break;

				default:
                    if ( $i == 0 )
			            $text .= '<OPTION label="" value=""></OPTION>';
   					$text .= '<OPTION label="'.$lab.'" value="'.$ret.'" '.$checked.'>'.$lab.'</OPTION>';
					break;
				}

		}
		}

		switch ( $type )
		{
				case "MULTI":
 						$text .= '</SELECT>';
						break;

				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
 						$text .= ']}';
						break;

				case "CHECKBOX":
				case "RADIO":
						break;

				default:
 						$text .= '</SELECT>';
						break;
		}

		if ( !$in_is_expanding )
		{
		
			if ( array_key_exists("EXPAND_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDCLEAR_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDSELECTALL_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDSEARCH_".$this->query_name, $_REQUEST) ||
				$this->criteria_display == "NOINPUT" )
			//if ( $this->criteria_display == "NOINPUT" )
			{
				$tag = $value_string;
				if ( strlen($tag) > 40 )
					$tag = substr($tag, 0, 40)."...";
	
				if ( !$tag )
					$tag = "ANY";
	
				$text .= $tag;
			}
			else if ( $this->criteria_display == "ANYCHAR" || $this->criteria_display == "TEXTFIELD" )
			{
				if ( $manual_override && !$value_string )
                {
					$value_string = $_REQUEST["MANUAL_".$this->query_name];
                }

				$tag = "";
				$tag .= '<input  type="text" class="'.$this->lookup_query->getBootstrapStyle('textfield').'swPrpTextField" name="MANUAL_'.$this->query_name.'"';
				$tag .= ' value="'.$value_string.'">';
				$text .= $tag;
			}
		}

		return $text;
	}
	// -----------------------------------------------------------------------------
	// Function : lookup_display
	// -----------------------------------------------------------------------------
	function & lookup_display($in_is_expanding)
	{

		$text = "";
		if ( $in_is_expanding )
		{	
			$tag_pref = "EXPANDED_";
			$type = $this->expand_display;
		}
		else
		{	
			$tag_pref = "";
			$type = $this->criteria_display;
		}

		$value_string = "";

		$params = array();
		$manual_params = array();
		$hidden_params = array();
		$expanded_params = array();
		$manual_override = false;

		if ( !array_key_exists("clearform", $_REQUEST) )
		{
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists($this->query_name, $_REQUEST) )
				{
						$params = $_REQUEST[$this->query_name];
						if ( !is_array($params) )
							$params = array ( $params );
				}

			$hidden_params = array();
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists("HIDDEN_".$this->query_name, $_REQUEST) )
				{
						$hidden_params = $_REQUEST["HIDDEN_".$this->query_name];
						if ( !is_array($hidden_params) )
							$hidden_params = array ( $hidden_params );
				}

			$manual_params = array();
			if ( ! array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
				if ( array_key_exists("MANUAL_".$this->query_name, $_REQUEST) )
				{
					$manual_params = explode(',',$_REQUEST["MANUAL_".$this->query_name]);
					if ( $manual_params )
					{
						$hidden_params = $manual_params;
						$manual_override = true;
					}
				}

			// If this is first time into screen and we have defaults then
			// use these instead
			if ( !$hidden_params && get_reportico_session_param("firstTimeIn") )
			{
				$hidden_params = $this->defaults;
				$manual_params = $this->defaults;
			}

			$expanded_params = array();
			if ( array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) )
			{
					$expanded_params = $_REQUEST["EXPANDED_".$this->query_name];
					if ( !is_array($expanded_params) )
						$expanded_params = array ( $expanded_params );
			}
		}
		else
		{
			$hidden_params = $this->defaults;
			$manual_params = $this->defaults;
			$params = $this->defaults;
		}

		switch ( $type )
		{
				case "NOINPUT":
				case "ANYCHAR":
				case "TEXTFIELD":
 						$text .= '<SELECT style="display:none" name="'."HIDDEN_".$this->query_name.'[]" size="0" multiple>';
						break;

				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
                        if ( $in_is_expanding )
                            $widget_id = "select2_dropdown_expanded_";
                        else
                            $widget_id = "select2_dropdown_";

                        if ( $type == "SELECT2SINGLE" )
						    $text .= '<SELECT id="'.$widget_id.$this->query_name.'" class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelect" name="'.$tag_pref.$this->query_name.'[]" >';
                        else
						    $text .= '<SELECT id="'.$widget_id.$this->query_name.'" class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelect" name="'.$tag_pref.$this->query_name.'[]" multiple>';
					    $text .= '<OPTION></OPTION>';
						break;

				case "MULTI":
						$multisize = 12;
						$res =& $this->lookup_query->targets[0]->results;
						$k = key($res);
						$multisize = 4;
						if ( $res && count($res[$k]) > 4 )
							$multisize = count($res[$k]);
                        if ( isset ( $res[$k] ) )
						    if ( count($res[$k]) >= 10 )
							    $multisize = 10;
						if ( $in_is_expanding )
							$multisize = 12;
						$text .= '<SELECT class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelect" name="'.$tag_pref.$this->query_name.'[]" size="'.$multisize.'" multiple>';
						break;

				case "CHECKBOX":
				case "RADIO":
						break;

				default:
 						$text .= '<SELECT class="'.$this->lookup_query->getBootstrapStyle('design_dropdown').'swPrpDropSelectRegular" name="'.$tag_pref.$this->query_name.'">';
						break;
		}

		$check_text = "";
		switch ( $type )
		{
			case "MULTI":
			case "DROPDOWN":
			case "ANYCHAR":
			case "TEXTFIELD":
			case "NOINPUT":
				$check_text = "selected";
				break;

			default:
				$check_text = "checked";
				break;
		}

		// If clear has been pressed we dont want any list items selected
		if ( $this->submitted('EXPANDCLEAR_'.$this->query_name) ) 
			$check_text = "";
			
		// If select all has been pressed we want all highlighted
		$selectall = false;
		if ( $this->submitted('EXPANDSELECTALL_'.$this->query_name) ) 
			$selectall = true;

		$res =& $this->lookup_query->targets[0]->results;
		if ( !$res )
		{
			$res = array();
			$k = 0;
		}
		else
		{
			reset($res);
			$k = key($res);
		for ($i = 0; $i < count($res[$k]); $i++ )
		{
			$line =&$res[$i];
			foreach ( $this->lookup_query->columns as $ky => $col )
			{
				if ( $col->lookup_display_flag )
				{
					$lab = $res[$col->query_name][$i];
				}
				if ( $col->lookup_return_flag )
					$ret = $res[$col->query_name][$i];
				if ( $col->lookup_abbrev_flag )
					$abb = $res[$col->query_name][$i];
				
			}
       			//$text .= '<OPTION label="'.$ret.'" value="'.$ret.'">'.$lab.'</OPTION>';
			$checked="";

			if ( in_array($ret, $params) )
			{
				$checked = $check_text;
			}

			if ( in_array($ret, $hidden_params) && !$manual_override )
			{
				$checked = $check_text;
			}

			if ( in_array($ret, $expanded_params) )
			{
				$checked = $check_text;
			}

			if ( in_array($abb, $hidden_params) && $manual_override )
			{
				$checked = $check_text;
			}

			if ( $selectall )
			{
				$checked = $check_text;
			}

			if ( $checked != "" )
				if ( !$value_string && $value_string != "0" )
					$value_string = $abb;
				else
					$value_string .= ",".$abb;

			switch ( $type )
			{
				case "MULTI":
   					$text .= '<OPTION label="'.$lab.'" value="'.$ret.'" '.$checked.'>'.$lab.'</OPTION>';
					break;

				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
                    ////if ( $checked )
                        //$checked = "checked=1";
   					$text .= '<OPTION label="'.$lab.'" value="'.$ret.'" '.$checked.'>'.$lab.'</OPTION>';
					break;

				case "RADIO":
    				$text .= '<INPUT type="radio" name="'.$tag_pref.$this->query_name.'" value="'.$ret.'" '.$checked.'>'.$lab.'<BR>';
					break;

				case "CHECKBOX":
    					$text .= '<INPUT type="checkbox" name="'.$tag_pref.$this->query_name.'[]" value="'.$ret.'" '.$checked.'>'.$lab.'<BR>';
					break;

				default:
                    if ( $i == 0 )
			            $text .= '<OPTION label="" value=""></OPTION>';
   					$text .= '<OPTION label="'.$lab.'" value="'.$ret.'" '.$checked.'>'.$lab.'</OPTION>';
					break;
				}

		}
		}

		switch ( $type )
		{
				case "MULTI":
 						$text .= '</SELECT>';
						break;

				case "SELECT2MULTIPLE":
				case "SELECT2SINGLE":
 						$text .= '</SELECT>';
						break;

				case "CHECKBOX":
				case "RADIO":
						break;

				default:
 						$text .= '</SELECT>';
						break;
		}

		if ( !$in_is_expanding )
		{
		
			if ( array_key_exists("EXPAND_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDCLEAR_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDSELECTALL_".$this->query_name, $_REQUEST) ||
				array_key_exists("EXPANDSEARCH_".$this->query_name, $_REQUEST) ||
				$this->criteria_display == "NOINPUT" )
			//if ( $this->criteria_display == "NOINPUT" )
			{
				$tag = $value_string;
				if ( strlen($tag) > 40 )
					$tag = substr($tag, 0, 40)."...";
	
				if ( !$tag )
					$tag = "ANY";
	
				$text .= $tag;
			}
			else if ( $this->criteria_display == "ANYCHAR" || $this->criteria_display == "TEXTFIELD" )
			{
				if ( $manual_override && !$value_string )
                {
					$value_string = $_REQUEST["MANUAL_".$this->query_name];
                }

				$tag = "";
				$tag .= '<input  type="text" class="'.$this->lookup_query->getBootstrapStyle('textfield').'swPrpTextField" name="MANUAL_'.$this->query_name.'"';
				$tag .= ' value="'.$value_string.'">';
				$text .= $tag;
			}
		}

		return $text;
	}


	// -----------------------------------------------------------------------------
	// Function : get_criteria_value
	// -----------------------------------------------------------------------------
	function get_criteria_value($in_type, $use_del = true)
	{

		$cls = "";
		switch($in_type)
		{
				case "RANGE1":
						$cls = $this->get_criteria_clause(false, false, false, true, false, $use_del);
						break;

				case "RANGE2":
						$cls = $this->get_criteria_clause(false, false, false, false, true, $use_del);
						break;
				case "FULL" :
						$cls = $this->get_criteria_clause(true, true, true, false, false, $use_del);
						break;

				case "VALUE" :
						$cls = $this->get_criteria_clause(false, false, true, false, false, $use_del);
						break;

				default :
						handle_error( "Unknown Criteria clause type $in_type for criteria ".$this->query_name);
						break;
		}
		return $cls;
	}

	// -----------------------------------------------------------------------------
	// Function : get_criteria_clause
	// -----------------------------------------------------------------------------
	function get_criteria_clause($lhs = true, $operand = true, $rhs = true, $rhs1 = false, $rhs2 = false, $add_del = true)
	{

		$cls = "";

		if ( $this -> _use == "SHOW/HIDE-and-GROUPBY") $add_del = false;

		if ( $this->column_value == "(ALL)" )
			return $cls;

		if ( $this->column_value == "(NOTFOUND)" )
		{
			$cls = " AND 1 = 0";
			return $cls;
		}

		if ( !$this->column_value ) 
		{
			return ($cls);
		}

		$del = '';

		switch($this->criteria_type)
		{

			case "ANY":
			case "ANYCHAR":
			case "TEXTFIELD":
				if ( $add_del )
					$del = $this->get_value_delimiter();

				$extract= explode(',', $this->column_value);
				if ( is_array($extract) )
				{
					$ct = 0;
					foreach ( $extract as $col )
					{
						if ( is_string($col) )
						{
							$col = trim($col);
						}
	
						if ( !$col )
							continue;

						if ( $col == "(ALL)" )
						{
							continue;
						}

						if ( $ct == 0 )
						{
							if ( $lhs )
							{
								//$cls .= " XX".$this->table_name.".".$this->column_name;
								$cls .= " AND ".$this->column_name;
							}
							if ( $rhs )
							{
								if ( $operand )
									$cls .= " IN (";
								$cls .= $del.$col.$del;
							}
						}
						else
							if ( $rhs )
								$cls .= ",".$del.$col.$del;
						$ct++;
					}

					if ( $ct > 0 && $rhs )
						if ( $operand )
							$cls .= " )";
				}
				else
				{
					if ( $lhs )
					{
						if ( $this->table_name  && $this->column_name )
							$cls .= " AND ".$this->table_name.".".$this->column_name;
						else 
							if ( $this->column_name )
								$cls .= " AND ".$this->column_name;
					}
					if ( $rhs )
						if ( $operand )
							$cls .= " =".$del.$this->column_value.$del;
						else
							$cls .= $del.$this->column_value.$del;
				}
				break;

			case "LIST":
				if ( $add_del )
					$del = $this->get_value_delimiter();

				if ( !is_array($this->column_value) )
					$this->column_value = explode(',', $this->column_value);

				if ( is_array($this->column_value) )
				{
					$ct = 0;
					foreach ( $this->column_value as $col )
					{
						if ( is_string($col) )
						{
							$col = trim($col);
						}

						if ( $col == "(ALL)" )
						{
							continue;
						}

						if ( $ct == 0 )
						{
							if ( $lhs )
							{
								if ( $this->table_name  && $this->column_name )
									$cls .= " AND ".$this->table_name.".".$this->column_name;
								else 
									if ( $this->column_name )
										$cls .= " AND ".$this->column_name;
							}
							if ( $rhs )
							{
								if ( $operand )
									$cls .= " IN (";
								$cls .= $del.$col.$del;
							}
						}
						else
							if ( $rhs )
								$cls .= ",".$del.$col.$del;
						$ct++;
					}

					if ( $ct > 0 )
						if ( $operand )
							$cls .= " )";
				}
				else
				{
					if ( $lhs )
					{
						if ( $this->table_name  && $this->column_name )
							$cls .= " AND ".$this->table_name.".".$this->column_name;
						else 
							if ( $this->column_name )
								$cls .= " AND ".$this->column_name;
					}
					if ( $rhs )
					{
						if ( $operand )
							$cls .= " =".$del.$this->column_value.$del;
						else
							$cls .= $del.$this->column_value.$del;
					}
				}
				break;
				
			case "DATE":
				$cls = "";
				if ( $this->column_value )
				{
					$val1 = parse_date($this->column_value, false, SW_PREP_DATEFORMAT);
					$val1 = convertYMDtoLocal($val1, SW_PREP_DATEFORMAT, SW_DB_DATEFORMAT);
					if ( $lhs )
					{
						if ( $this->table_name  && $this->column_name )
							$cls .= " AND ".$this->table_name.".".$this->column_name;
						else 
							if ( $this->column_name )
								$cls .= " AND ".$this->column_name;
					}
					if ( $add_del )
						$del = $this->get_value_delimiter();

					if ( $rhs )
					{
						if ( $operand )
							$cls .= " = ";
						$cls .= $del.$val1.$del;
					}
				}
				break;
				
			case "DATERANGE":
				$cls = "";
				if ( $this->column_value )
				{
                    // If daterange value here is a range in a single value then its been
                    // run directly from command line and needs splitting up using "-"


					$val1 = parse_date($this->column_value,false, SW_PREP_DATEFORMAT);
					$val2 = parse_date($this->column_value2,false, SW_PREP_DATEFORMAT);
					$val1 = convertYMDtoLocal($val1, SW_PREP_DATEFORMAT, SW_DB_DATEFORMAT);
					$val2 = convertYMDtoLocal($val2, SW_PREP_DATEFORMAT, SW_DB_DATEFORMAT);
					if ( $lhs )
					{	
						if ( $this->table_name  && $this->column_name )
							$cls .= " AND ".$this->table_name.".".$this->column_name;
						else 
							if ( $this->column_name )
								$cls .= " AND ".$this->column_name;
					}

					if ( $add_del )
						$del = $this->get_value_delimiter();
					if ( $rhs )
					{
						$cls .= " BETWEEN ";
						//$cls .= $del.$this->column_value.$del;
						$cls .= $del.$val1.$del;
						$cls .= " AND ";
						//$cls .= $del.$this->column_value2.$del;
						$cls .= $del.$val2.$del;
					}
					if ( $rhs1 )
					{
						$cls = $del.$val1.$del;
					}
					if ( $rhs2 )
					{
						$cls = $del.$val2.$del;
					}
				}
				break;
				
			case "LOOKUP":
				if ( $add_del )
					$del = $this->get_value_delimiter();
				if ( !is_array($this->column_value) )
					$this->column_value = explode(',', $this->column_value);

				if ( is_array($this->column_value) )
				{
					$ct = 0;
					foreach ( $this->column_value as $col )
					{
						if ( is_string($col) )
						{
							$col = trim($col);
						}

						if ( $col == "(ALL)" )
						{
							continue;
						}

						if ( $ct == 0 )
						{
							if ( $lhs )
							{
								if ( $this->table_name  && $this->column_name )
									$cls .= " AND ".$this->table_name.".".$this->column_name;
								else 
									if ( $this->column_name )
										$cls .= " AND ".$this->column_name;
							}
							if ( $rhs )
							{
								if ( $operand )
									$cls .= " IN (";
								$cls .= $del.$col.$del;
							}
						}
						else
							if ( $rhs )
								$cls .= ",".$del.$col.$del;
						$ct++;
					}

					if ( $ct > 0 )
						if ( $operand )
							$cls .= " )";
				}
				else
				{
					if ( $lhs )
					{
						if ( $this->table_name  && $this->column_name )
							$cls .= " AND ".$this->table_name.".".$this->column_name;
						else 
							if ( $this->column_name )
								$cls .= " AND ".$this->column_name;
					}
					if ( $rhs )
					{
						if ( $operand )
							$cls .= " =".$del.$this->column_value.$del;
						else
							$cls .= $del.$this->column_value.$del;
					}
				}
				break;
				
			default:
				break;
		}

		return($cls);
	}

	function & expand_template()
	{
	 	$text = "";

		if ( $this->submitted('EXPANDSEARCH_'.$this->query_name) ) 
				$dosearch = true;

		// Only use then expand value if Search was press
		$expval="";
		if ( $this->submitted('EXPANDSEARCH_'.$this->query_name) )
			if ( array_key_exists("expand_value", $_REQUEST) )
				$expval=$_REQUEST["expand_value"];

		$type = $this->criteria_type;
		if ( $this->expand_display == "ANYCHAR" )
			$type = $this->expand_display;
		if ( $this->expand_display == "TEXTFIELD" )
			$type = $this->expand_display;

		switch($type)
		{
			case "LIST":
				$text .= $this->list_display(true);
				break;

			case "LOOKUP":
				$this->execute_criteria_lookup(true);
				$text .= $this->lookup_display(true);
				break;

			case "DATE":
				$text .= $this->date_display(true);
				break;

			case "DATERANGE":
				$text .= $this->daterange_display(true);
				break;

			case "ANYCHAR":
			case "TEXTFIELD":
				$tag = "";
				$tag .= '<input  type="text" name="EXPANDED_'.$this->query_name.'"';
				$tag .= ' size="'.($this->column_length).'"';
				$tag .= ' maxlength="'.$this->column_length.'"';
				$tag .= ' value="'.$this->column_value.'">';
				$text .= $tag;

				break;
				
			default:
				break;
		}

		return $text;
	}

	function & expand()
	{
	 	$text = "";
		$text .= template_xlate("Search")." ";
		$text .= $this->derive_attribute("column_title", $this->query_name);
		$text .= " :<br>";

		$tag = "";
		$tag .= '<input  class="'.$this->lookup_query->getBootstrapStyle('textfield').'" type="text" name="expand_value"';
		$tag .= ' size="30"';

		if ( $this->submitted('EXPANDSEARCH_'.$this->query_name) ) 
				$dosearch = true;

		// Only use then expand value if Search was press
		$expval="";
		if ( $this->submitted('EXPANDSEARCH_'.$this->query_name) )
			if ( array_key_exists("expand_value", $_REQUEST) )
				$expval=$_REQUEST["expand_value"];

		$tag .= ' value="'.$expval.'">';
		$text .= $tag;
		$text .= '<input class="btn btn-sm btn-default swPrpSubmit" type="submit" name="EXPANDSEARCH_'.$this->query_name.'" value="'.template_xlate("Search").'">';
		$text .= "<br>";


		$type = $this->criteria_type;
		if ( $this->expand_display == "ANYCHAR" )
			$type = $this->expand_display;
		if ( $this->expand_display == "TEXTFIELD" )
			$type = $this->expand_display;

		$text .= '<DIV id="hello" style="visibility:hide">';
		$text .= '</DIV>';
		switch($type)
		{
			case "LIST":
				$text .= $this->list_display(true);
				break;

			case "LOOKUP":
				$this->execute_criteria_lookup(true);
				$text .= $this->lookup_display(true);
				break;

			case "DATE":
				$text .= $this->date_display(true);
				break;

			case "DATERANGE":
				$text .= $this->daterange_display(true);
				break;

			case "ANYCHAR":
			case "TEXTFIELD":
				//ECHO $TAG;
				$tag = "";
				$tag .= '<input  type="text" name="EXPANDED_'.$this->query_name.'"';
				$tag .= ' size="'.($this->column_length).'"';
				$tag .= ' maxlength="'.$this->column_length.'"';
				$tag .= ' value="'.$this->column_value.'">';
				$text .= $tag;

				break;
				
			default:
				break;
		}

		$text .= '<br><input class="btn btn-sm btn-default swPrpSubmit" type="submit" name="EXPANDCLEAR_'.$this->query_name.'" value="Clear">';
		$text .= '<input class="btn btn-sm btn-default swPrpSubmit" type="submit" name="EXPANDSELECTALL_'.$this->query_name.'" value="Select All">';
		$text .= '<input class="btn btn-sm btn-default swPrpSubmit" type="submit" name="EXPANDOK_'.$this->query_name.'" value="OK">';

		return $text;
	}

	function format_form_column()
	{
		$text = "";
		$type = $this->criteria_type;

		switch($type)
		{
			case "LIST":
				$text .= $this->list_display(false);
				break;

			case "LOOKUP":
				if ( 
						//!array_key_exists("clearform", $_REQUEST) &&
						//(
						( $this->criteria_display !== "TEXTFIELD"  && $this->criteria_display !== "ANYCHAR" && $this->criteria_display != "NOINPUT" ) 
						||
						(
						array_key_exists("EXPANDED_".$this->query_name, $_REQUEST) || 
						array_key_exists("HIDDEN_".$this->query_name, $_REQUEST) ||
						$this->column_value
						)
						//)
					)
				{

                    // Dont bother running select for criteria lookup if criteria item is a dynamic
					$this->execute_criteria_lookup();
				}
				$text .= $this->lookup_display(false);
				break;

			case "DATE":
				$text .= $this->date_display();
				break;

			case "DATERANGE":
				$text .= $this->daterange_display();
				break;

			case "ANYCHAR":
			case "TEXTFIELD":
				//$text .= '<SELECT style="visibility:hidden" name="'."HIDDEN_".$this->query_name.'[]" size="1" multiple>';
				//$text .= '<SELECT name="'."HIDDEN_".$this->query_name.'[]" size="1" multiple>';
				$tag = "";
				$tag .= '<input  type="text" class="'.$this->lookup_query->getBootstrapStyle('textfield').'swPrpTextField" name="MANUAL_'.$this->query_name.'"';
				$tag .= ' size="50%"';
				$tag .= ' value="'.$this->column_value.'">';
				$text .= $tag;

				break;

			case "SQLCOMMAND":
				$tag = "";
				$tag .= '<br><textarea  cols="70" rows="20" class="'.$this->lookup_query->getBootstrapStyle('textfield').'swPrpTextField" type="text" name="MANUAL_'.$this->query_name.'">';
				$tag .= $this->column_value;
				$tag .= "</textarea>";
                $text .= $tag;
                break;
				
			default:
				break;
		}

		return $text;
	}
}