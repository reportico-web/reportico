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

 * File:        partial.php
 *
 * Reportico runner script
 * !!! Note this script will run reports in FULL design mode
 * !!! This means that users of the reports will be able to 
 * !!! modify reports and save those modifications
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: partial.php,v 1.9 2014/05/17 15:12:31 peter Exp $
 */

	error_reporting(E_ALL);
    date_default_timezone_set(@date_default_timezone_get());

	ini_set("memory_limit","100M");

	//ob_start();
	require_once('reportico.php');
	$q = new reportico();
	$q->allow_debug = true;
	$q->forward_url_get_parameters = "";
	$q->embedded_report = true;
	$q->execute($q->get_execute_mode(), true);
	//ob_end_flush();
?>
