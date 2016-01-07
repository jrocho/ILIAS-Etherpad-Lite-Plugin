<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* EtherpadLite repository object plugin
*
* @author Jan Rocho <jan@rocho.eu>
* @version $Id$
*
*/
class ilEtherpadLitePlugin extends ilRepositoryObjectPlugin
{

	protected function uninstallCustom() {
                global $ilDB;

				// removes plugin tables if they exist                
                if($ilDB->tableExists('rep_robj_xpdl_data'))
                	$ilDB->dropTable('rep_robj_xpdl_data');
                	
                if($ilDB->tableExists('rep_robj_xpdl_adm_set'))
                	$ilDB->dropTable('rep_robj_xpdl_adm_set');
    }

	function getPluginName()
	{
		return "EtherpadLite";
	}
}
?>
