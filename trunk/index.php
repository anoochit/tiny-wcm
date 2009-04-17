<?php
/*
Copyright (C) 2008 Anoochit Chalothorn <anoochit@redlinesoft.net>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

session_start();

/**
 * load APIs
 */
include_once("config.inc.php");
include_once('include/adodb/adodb.inc.php');
include_once('include/adodb/adodb-pager.inc.php');
require_once('include/adodb/adodb-active-record.inc.php');
include_once('include/adodb/adodb-exceptions.inc.php');

$db = NewADOConnection("mysql://".$cfg_username.":".$cfg_password."@".$cfg_host."/".$cfg_db);
ADOdb_Active_Record::SetDatabaseAdapter($db);
$db->debug=0;

/**
 * load ideastorm ORM
 */
/*
include_once('include/ideastorm/class.category.php');
include_once('include/ideastorm/class.comment.php');
include_once('include/ideastorm/class.member.php');
include_once('include/ideastorm/class.node.php');
include_once('include/ideastorm/class.relation.php');
*/
include_once('include/tinywcm/class.system.php');


/**
 * Initial iSystem
 */
$obsys=new iSystem($cfg_url,$cfg_theme);


/**
 *  test signed user
 */
$obsys->setSession("Anuchit Chalothorn","1","anoochit@gmail.com");

/**
 *  Align URI if deploy in sub directory
 */
if (!empty($cfg_subdir)) {
	$_SERVER['REQUEST_URI']=substr($_SERVER['REQUEST_URI'],strlen($cfg_subdir)+1);
}

/**
 *  Create strap value
 *  Example of url pattern
 * 	/contact
 * 	/what-news ==> /node/1
 * 	/node/add
 *  /node/edit/1
 * 	/node/delete/1
 */
$strap=split("/",$_SERVER['REQUEST_URI']);
$mod=$strap[1]; // module ex: node
$act=$strap[2]; // command ex: add, view, delete, edit
$item=$strap[3]; // item ex: 100

var_dump($strap);

/**
 * load language file
 */
if (file_exists("language/lang-".$cfg_lang.".php")) {
	include_once("language/lang-".$cfg_lang.".php");
} else {
	include_once("language/lang-en.php");
}

/**
 *  load bootstrape
 */
include_once("include/bootstrap.inc.php");

/**
 * load module
*/

/*
 * FIXME
 * @return array
*/

/*
 * FIXME
 * @return array
*/

/**
 * load smarty
 */
define ("SMARTY_DIR", "include/smarty/");
require_once (SMARTY_DIR."Smarty.class.php");
$smarty = new Smarty;
$smarty->compile_dir = "cache";
$smarty->template_dir = "theme/".$cfg_theme."/";
//$smarty->caching = true;
$smarty->assign("site_theme", $cfg_theme);

/**
 * set value to template engine
 */
$smarty->assign("site_url", $cfg_url);

/**
 *  set footer value
 **/
ob_start();			
require_once("include/footer.inc.php");
$content = ob_get_contents();
ob_end_clean();
$smarty->assign("site_footer", $content);

/**
 * set body value
 */
ob_start();	
include("module/".$pagefile.".php");
$content = ob_get_contents();
ob_end_clean();
$smarty->assign("site_body", $content);

$smarty->display($template.".tpl");	

?>