<?php
/**
 * File: displayWindow.php
 * Created by rocean
 * Date: 24/06/16
 * Time: 22:36
 * Εμφανίζει τα περιεχόμενα του κεντρικού παραθύρου
 */

require_once('libraries/common.inc.php');




if(isset($_GET['page']))
    $page=ClearString($_GET['page']);


switch ($page) {
    case 1: OWMP::showDashboard(); break;
    case 2: OWMP::showConfiguration(); break;
}