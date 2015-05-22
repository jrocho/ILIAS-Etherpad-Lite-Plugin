<?php
/*
	+-----------------------------------------------------------------------------+
	| EtherpadLite ILIAS Plugin                                                        |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2012-2013 Jan Rocho										      |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
* ListGUI implementation for EtherpadLite object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* @author 		Jan Rocho <jan@rocho.eu>
*/
class ilObjEtherpadLiteListGUI extends ilObjectPluginListGUI
{
	
	/**
	* Init type
	*/
	function initType()
	{
		$this->setType("xpdl");
	}
	
	/**
	* Get name of gui class handling the commands
	*/
	function getGuiClass()
	{
		return "ilObjEtherpadLiteGUI";
	}
	
	/**
	* Get commands
	*/
	function initCommands()
	{
		return array
		(
			array(
				"permission" => "read",
				"cmd" => "showContent",
				"default" => true),
			array(
				"permission" => "write",
				"cmd" => "editProperties",
				"txt" => $this->txt("edit"),
				"default" => false),
		);
	}

	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $ilUser;

		$props = array();
		
		$this->plugin->includeClass("class.ilObjEtherpadLiteAccess.php");
		
		/**
		 * offline?
		 */
		if (!ilObjEtherpadLiteAccess::checkOnline($this->obj_id))
		{
			$props[] = array(
				"alert" => true, 
				"property" => $this->txt("status"),
				"value" => $this->txt("offline"));
		}
		

		/**
		 * author identification, if "author_identification_show_in_list" is set
		 */
		$this->plugin->includeClass("class.ilEtherpadLiteConfig.php");
		if (ilEtherpadLiteConfig::getValue("author_identification_conf") && 
				ilEtherpadLiteConfig::getValue("author_identification_conf_author_identification_show_in_list"))
		{
			$type = ilObjEtherpadLiteAccess::getAuthorIdentificationFromDB($this->obj_id);
			$field_id = substr($type, strpos($type, ":")+1);
			if(stripos($type,'UDF') !== false)
			{
				include_once("./Services/User/classes/class.ilUserDefinedFields.php");
				$user_defined_fields =& ilUserDefinedFields::_getInstance();
				$udfDefinition = $user_defined_fields->getDefinition($field_id);
				$value = $udfDefinition['field_name']."-Feld";
			} 
			else
				$value = $this->txt($type);				
			
			$props[] = array(
					"alert" => true,
					"property" => $this->txt("author_identification"),
					"value" => $value,
					"newline" => true);
		}

		return $props;
	}
}
?>
