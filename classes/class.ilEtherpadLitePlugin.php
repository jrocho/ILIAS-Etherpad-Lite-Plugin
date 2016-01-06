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
                // uninstall placeholder 
        }

	function getPluginName()
	{
		return "EtherpadLite";
	}
}
?>
