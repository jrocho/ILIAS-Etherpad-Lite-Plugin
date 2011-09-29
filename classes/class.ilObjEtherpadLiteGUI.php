<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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


include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* User Interface class for EtherpadLite repository object.
* 
* @author Jan Rocho <jan@rocho.eu>
*
* $Id$
*
*
* @ilCtrl_isCalledBy ilObjEtherpadLiteGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjEtherpadLiteGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
*
*/
class ilObjEtherpadLiteGUI extends ilObjectPluginGUI
{
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - example: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
	}
	
	/**
	* Get type.
	*/
	final function getType()
	{
		return "xpdl";
	}
	
	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "updateProperties":
			//case "...":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			
			case "showContent":			// list all commands that need read permission here
			//case "...":
			//case "...":
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd()
	{
		return "showContent";
	}
	
//
// DISPLAY TABS
//
	
	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;
		
		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}

		// standard epermission tab
		$this->addPermissionTab();
	}
	

// THE FOLLOWING METHODS IMPLEMENT SOME EXAMPLE COMMANDS WITH COMMON FEATURES
// YOU MAY REMOVE THEM COMPLETELY AND REPLACE THEM WITH YOUR OWN METHODS.

//
// Edit properties form
//

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm()
	{
		global $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$this->form->addItem($ta);
		
		// online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$this->form->addItem($cb);
                
                $epadlid_input = new ilHiddenInputGUI("epadl_id");
		//$format_input->setValue("epadl_id");
		$this->form->addItem($epadlid_input);

		$this->form->addCommandButton("updateProperties", $this->txt("save"));
	                
		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$values["online"] = $this->object->getOnline();
		$values["epadl_id"] = $this->object->getEtherpadLiteID();		
		$this->form->setValuesByArray($values);
	}
	
	/**
	* Update properties
	*/
	public function updateProperties()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initPropertiesForm();
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->setEtherpadLiteID($this->form->getInput("epadl_id"));
			$this->object->setOnline($this->form->getInput("online"));
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
//
// Show content
//

	/**
	* Show content
	*/
	function showContent()
	{
		global $tpl, $ilTabs, $ilUser;
		
		$ilTabs->activateTab("content");
                $tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/libs/jquery-1.6.2-mod.min.js",false,3);
                $tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/libs/jquery-ui-1.8.16-mod.custom.min.js",false,3);
                $tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/libs/etherpad-lite-jquery/js/etherpad.js",false,3);
                $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/templates/css/etherpad.css");
                $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/templates/css/ui-lightness/jquery-ui-1.8.16.custom.css");

                $fullName = $ilUser->firstname.' '.$ilUser->lastname;
                $fullName = rawurlencode(iconv('utf-8','iso-8859-1', addslashes($fullName)));
                
                // build javascript required to load the pad
                $js = "<script type=\"text/javascript\">".
                      "$(function() {".
                      "$( \"#etherpad-lite\" ).resizable({helper: \"ui-resizable-helper\"});".
                      "});".
                      "$('#etherpad-lite').pad({'padId':'".
                      $this->object->getEtherpadLiteID()."','showChat':'true',".
                      "'showControls':'true',".
                      "'showLineNumbers':'true',".
                      "'showChat':'true',".
                      "'userName':'".$fullName."'});</script>\n";
                
                $tpl->setContent('<div id="etherpad-lite"></div>'.$js);
                
                // Add Permalink
                include_once './Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
                $permalink = new ilPermanentLinkGUI('xpdl', $this->object->getRefId());
                $this->tpl->setVariable('PRMLINK', $permalink->getHTML());	 
                
	}
	

}
?>
