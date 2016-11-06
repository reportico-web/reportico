<?php
/*
 Reportico - PHP Reporting Tool
 Copyright (C) 2010-2014 Peter Deed

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 * File:        swmodify.php
 *
 * Base class for handling generic updating functionality
 * to the database where details of an operation, a data view name
 * a key and values are passed within the URL
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swmodify.php,v 1.8 2014/05/17 15:12:31 peter Exp $
 */

class reportico_db_engine
{
	public $pdo;
	public $stmt;
	public $last_sql;
	public $errorno;
	public $errormsg;
	public $errortext;

    function __construct($in_pdo)
    {
	    $this->pdo = $in_pdo;
    }
    
    function executeSQL( $in_sql, $no_rows_is_error = false )
    {
            $this->last_sql = $in_sql;
            $this->stmt =  $this->pdo->query($in_sql);
            if ( !$this->stmt )
            {
		           $this->storeErrorMessage();
                   return ( $this->stmt);
            }

            if ( $no_rows_is_error )
            {
                if ( $this->getRowsAffected() == 0 )
                {
                    $this->errorno = "100";
                    $this->errormsg = "Warning - No data was affected by the operation";
                    $this->last_sql = "";
                    return false;
                }
                
            }
            return $this->stmt;
    }
    
    function fetch()
    {
            $result = $this->stmt->fetch();
            return $result;
    }
    
    function close()
    {
            $this->stmt = null;
    }
    
    function storeErrorMessage()
    {
            $arr = $this->pdo->errorInfo();
            $this->errorno = $arr[0];
            $this->errormsg = $arr[2];
    }

    function getErrorMessage($add_sql = true)
    {
        return "Error ". $this->errorno. " - <BR>".$this->errormsg."<BR><BR>".$this->last_sql;
    }

    function showPDOError( )
    {
            $info = $this->pdo->errorInfo();
            $msg =  "Error ".$info[1]."<BR>".
                    $info[2];
            trigger_error("$msg", E_USER_NOTICE);
    }
    
    function getRowsAffected( )
    {
            return $this->stmt->rowCount();
    }
    
    function rpt_setDirtyRead()
    {
	    $sql = "SET ISOLATION TO DIRTY READ";
	    return $this->pdo->Execute($sql);
    }
    
    
    function perform_project_modifications ($project)
    {
        $filename = find_best_location_in_include_path( "projects/".$project."/modification_rules.php");
        $return_status = array (
                    "errstat" => 0,
                    "msgtext" => "Modification sucessful"
                    );

        if ( is_file ( $filename ) )
        {
            require_once($filename);
            custom_project_modifications(&$this, $return_status);
        }
        else
        {
            $return_status["errstat"] = -1;
            $return_status["msgtext"] = "No modifcation rules were found";
        }

        return $return_status;
    }

}
?>
