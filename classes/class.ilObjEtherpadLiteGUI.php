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


include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* User Interface class for EtherpadLite repository object.
* 
* @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
* @author Jan Rocho <jan.rocho@fh-dortmund.de>
*
* $Id$
*
*
* @ilCtrl_isCalledBy ilObjEtherpadLiteGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjEtherpadLiteGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
*
*/
class ilObjEtherpadLiteGUI extends ilObjectPluginGUI
{
    /**
     * Initialisation
     */
    protected function afterConstructor()
    {

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
            case "editProperties": // list all commands that need write permission here
            case "updateProperties":
                //case "...":
                $this->checkPermission("write");
                $this->$cmd();
                break;

            case "showContent": // list all commands that need read permission here
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

        // hidden Inputfield for ID
        $epadlid_input = new ilHiddenInputGUI("epadl_id");
        $this->form->addItem($epadlid_input);

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
        
        // Show Elements depending on settings in the administration of the plugin
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteConfig.php");
        $this->adminSettings = new ilEtherpadLiteConfig();

        if($this->adminSettings->getValue("allow_read_only"))
        {
			$ro = new ilCheckboxInputGUI($this->txt("read_only"), "read_only");
			$this->form->addItem($ro);
		}


        // show Chat
        if($this->adminSettings->getValue("conf_show_chat"))
        {

            $chat = new ilCheckboxInputGUI($this->txt("show_chat"), "show_chat");
            //$chat->setInfo($this->txt("info_show_chat"));
            $this->form->addItem($chat);
        }

        // show line number
        if($this->adminSettings->getValue("conf_line_numbers"))
        {
            $line = new ilCheckboxInputGUI($this->txt("show_line_numbers"), "show_line_numbers");
            //$line->setInfo($this->txt("info_show_line_numbers"));
            $this->form->addItem($line);
        }

        // monospace font
        if($this->adminSettings->getValue("conf_monospace_font"))
        {
            $font = new ilCheckboxInputGUI($this->txt("monospace_font"), "monospace_font");
            $font->setInfo($this->txt("info_monospace_font"));
            $this->form->addItem($font);
        }


        // show colors
        if($this->adminSettings->getValue("conf_show_colors"))
        {
            $colors = new ilCheckboxInputGUI($this->txt("show_colors"), "show_colors");
            //$colors->setInfo($this->txt("info_show_colors"));
            $this->form->addItem($colors);
        }


        // show controls
        if($this->adminSettings->getValue("conf_show_controls"))
        {
            $controls = new ilCheckboxInputGUI($this->txt("show_controls"), "show_controls");
            //$controls->setInfo($this->txt("info_show_controls"));



            // show style
            if($this->adminSettings->getValue("conf_show_controls_conf_show_style"))
            {
                $style = new ilCheckboxInputGUI($this->txt("show_style"), "show_style");
                $style->setInfo($this->txt("info_show_style"));
                $controls->addSubItem($style);
            }


             // show list
            if($this->adminSettings->getValue("conf_show_controls_conf_show_list"))
            {
                $list = new ilCheckboxInputGUI($this->txt("show_list"), "show_list");
                $list->setInfo($this->txt("info_show_list"));
                $controls->addSubItem($list);
            }


            // show redo
            if($this->adminSettings->getValue("conf_show_controls_conf_show_redo"))
            {
                $redo = new ilCheckboxInputGUI($this->txt("show_redo"), "show_redo");
                //$redo->setInfo($this->txt("info_show_redo"));
                $controls->addSubItem($redo);
            }


            // show coloring
            if($this->adminSettings->getValue("conf_show_controls_conf_show_coloring"))
            {
                $coloring = new ilCheckboxInputGUI($this->txt("show_coloring"), "show_coloring");
                $coloring->setInfo($this->txt("info_show_coloring"));
                $controls->addSubItem($coloring);
            }


            // show heading
            if($this->adminSettings->getValue("conf_show_controls_conf_show_heading"))
            {
                $heading = new ilCheckboxInputGUI($this->txt("show_heading"), "show_heading");
                $heading->setInfo($this->txt("info_show_heading"));
                $controls->addSubItem($heading);
            }


            // show import/export
            if($this->adminSettings->getValue("conf_show_controls_conf_show_import_export"))
            {
                $import = new ilCheckboxInputGUI($this->txt("show_import_export"), "show_import_export");
                $import->setInfo($this->txt("info_show_import_export"));
                $controls->addSubItem($import);
            }


            // show timeline
            if($this->adminSettings->getValue("conf_show_controls_conf_show_timeline"))
            {
                $timeline = new ilCheckboxInputGUI($this->txt("show_timeline"), "show_timeline");
                $timeline->setInfo($this->txt("info_show_timeline"));
                $controls->addSubItem($timeline);
            }

            $this->form->addItem($controls);
        }

        $this->form->addCommandButton("updateProperties", $this->txt("save"));

        $this->form->setTitle($this->txt("edit_properties"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get values for edit properties form
     */
    function getPropertiesValues()
    {
        $values["title"]    = $this->object->getTitle();
        $values["desc"]     = $this->object->getDescription();
        $values["online"]   = $this->object->getOnline();
        $values["epadl_id"] = $this->object->getEtherpadLiteID();
        $values["show_chat"]= $this->object->getShowChat();
        $values["show_line_numbers"]= $this->object->getLineNumbers();
        $values["monospace_font"]= $this->object->getMonospaceFont();
        $values["show_colors"]= $this->object->getShowColors();
        $values["show_controls"]= $this->object->getShowControls();
        $values["show_style"]= $this->object->getShowStyle();
        $values["show_list"]= $this->object->getShowList();
        $values["show_coloring"]= $this->object->getShowColoring();
        $values["show_redo"]= $this->object->getShowRedo();
        $values["show_heading"]= $this->object->getShowHeading();
        $values["show_import_export"]= $this->object->getShowImportExport();
        $values["show_timeline"]= $this->object->getShowTimeline();
        $values["read_only"]= $this->object->getReadOnly();

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
            $this->object->setShowChat($this->form->getInput("show_chat"));
            $this->object->setLineNumbers($this->form->getInput("show_line_numbers"));
            $this->object->setMonospaceFont($this->form->getInput("monospace_font"));
            $this->object->setShowColors($this->form->getInput("show_colors"));
            $this->object->setShowControls($this->form->getInput("show_controls"));
            $this->object->setShowStyle($this->form->getInput("show_style"));
            $this->object->setShowList($this->form->getInput("show_list"));
            $this->object->setShowColoring($this->form->getInput("show_coloring"));
            $this->object->setShowRedo($this->form->getInput("show_redo"));
            $this->object->setShowHeading($this->form->getInput("show_heading"));
            $this->object->setShowImportExport($this->form->getInput("show_import_export"));
            $this->object->setShowTimeline($this->form->getInput("show_timeline"));
            $this->object->setReadOnly($this->form->getInput("read_only"));

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
        global $tpl, $ilTabs, $ilUser, $lng;
        try
        {

            $this->object->init();
            $ilTabs->activateTab("content");
            $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/templates/css/etherpad.css");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/js/ilEtherpadLite.js");

            // Show Elements depending on settings in the administration of the plugin
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteConfig.php");
            $this->adminSettings = new ilEtherpadLiteConfig();

			if($this->object->getReadOnly()) 
			{ 
				$padID = $this->object->getReadOnlyID(); 
				ilUtil::sendInfo($this->txt("read_only_notice"), true);
			} 
			else 
			{
				$padID = $this->object->getEtherpadLiteID(); 
			}
			
		    //$pad->setVariable("ETHERPADLITEID", $padID);

            // build javascript required to load the pad
            $pad = new ilTemplate("tpl.pad.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite");
            $pad->setVariable("ENTER_FULLSCREEN",$this->txt("enter_fullscreen"));
            $pad->setVariable("LEAVE_FULLSCREEN",$this->txt("leave_fullscreen"));
            $pad->setVariable("PROTOCOL",($this->adminSettings->getValue("https") ? "https" : "http"));
            $pad->setVariable("HOST",($this->adminSettings->getValue("host")));
            $pad->setVariable("PORT",($this->adminSettings->getValue("port")));
            $pad->setVariable("PATH",($this->adminSettings->getValue("path")));
            $pad->setVariable("ETHERPADLITE_ID",$padID);
            $pad->setVariable("USER_NAME",rawurlencode($ilUser->firstname . ' ' . $ilUser->lastname));
            $pad->setVariable("SHOW_CONTROLS",($this->object->getShowControls() ? "true" : "false"));
            $pad->setVariable("SHOW_CHAT",($this->object->getShowChat() ? "true" : "false"));
            $pad->setVariable("SHOW_LINE_NUMBERS",($this->object->getLineNumbers() ? "true" : "false"));
            $pad->setVariable("USE_MONOSPACE_FONT",($this->object->getMonospaceFont()? "true" : "false"));
            $pad->setVariable("NO_COLORS",($this->object->getShowColors()? "false" : "true"));
            $pad->setVariable("SHOW_STYLE_BLOCK",($this->object->getShowStyle()? "true" : "false"));
            $pad->setVariable("SHOW_LIST_BLOCK",($this->object->getShowList()? "true" : "false"));
            $pad->setVariable("SHOW_REDO_BLOCK",($this->object->getShowRedo()? "true" : "false"));
            $pad->setVariable("SHOW_COLOR_BLOCK",($this->object->getShowColoring()? "true" : "false"));
            $pad->setVariable("SHOW_HEADING_BLOCK",($this->object->getShowHeading()? "true" : "false"));
            $pad->setVariable("SHOW_IMPORT_EXPORT_BLOCK",($this->object->getShowImportExport()? "true" : "false"));
            $pad->setVariable("SHOW_TIMELINE_BLOCK",($this->object->getShowTimeline()? "true" : "false"));
            $pad->setVariable("LANGUAGE",$lng->getUserLanguage());			
            $pad->setVariable("EPADL_VERSION",($this->adminSettings->getValue("epadl_version")));
            $tpl->setContent($pad->get());


            // Add Permalink
            include_once './Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
            $permalink = new ilPermanentLinkGUI('xpdl', $this->object->getRefId());
            $this->tpl->setVariable('PRMLINK', $permalink->getHTML());
        } catch (Exception $e)
        {
            $ilTabs->activateTab("content");
            $tpl->setContent($this->txt("load_error")." ".$e->getMessage());
        }


    }

}
?>
