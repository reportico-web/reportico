<?php
/*
 * File:        index.php
 *
 * Top level index file resets to admin login page
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: index.php,v 1.8 2014/05/17 15:12:31 peter Exp $
 */

header("Location: run.php?project=admin&execute_mode=ADMIN&clear_session=1");

?>
