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
                $this->checkPermission("write");
                $this->$cmd();
                break;

            case "showContent": // list all commands that need read permission here
            case "agreePolicy":
            case "revokeConsent":
            case "inspectPolicies":
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
        
        
        // tab for the "policy agreements" command
        if($this->object->getRequirePolicyConsent())
        {
        	include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteUser.php");
        	$this->EtherpadLiteUser = new ilEtherpadLiteUser();
        	if($this->EtherpadLiteUser->getPolicyAgreement())
        	{
        		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
        		{
        			$ilTabs->addTab("agreement", "Einwilligungen (Einsicht)", $ilCtrl->getLinkTarget($this, "inspectPolicies"));
        		}
        	}        	
        }

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

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
        
		// require policy consent?
		$pc = new ilCheckboxInputGUI("&lt;c.t.&gt; Einwilligungen notwendig?", "require_policy_consent");
		$pc->setInfo("&lt;c.t.&gt;: Einwilligung in die datenschutz- und urheberrechtlichen Erklärungen sowie in das Regelwerk notwendig?");
        $this->form->addItem($pc);
              
        // Show Elements depending on settings in the administration of the plugin
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteConfig.php");
        $this->adminSettings = new ilEtherpadLiteConfig();
        
	        // author identification (radio group)
        	if($this->adminSettings->getValue("author_identification_conf"))
        	{
		        $av = new ilRadioGroupInputGUI($this->txt("author_identification"), "author_identification");
		         
		        // default radio options
		        $option1 = new ilRadioOption($this->txt("fullname"),"fullname", $this->txt("info_fullname"));
		        $option2 = new ilRadioOption($this->txt("username"),"username", $this->txt("info_username"));
		        
		        // disable re-identification if the conditions are met
		        if($this->adminSettings->getValue("author_identification_conf_author_identification_no_re-identification")
		        		&& stripos($this->object->getAuthorIdentification(),'UDF') !== false) 
		        {
		        	$option1->setDisabled(true);
		        	$option2->setDisabled(true);
		        }
		        
		        // add default radio options
		        $av->addOption($option1);
		        $av->addOption($option2);
		        
		        // more radio options: changeable user defined text fields
		        include_once("./Services/User/classes/class.ilUserDefinedFields.php");
		        $user_defined_fields =& ilUserDefinedFields::_getInstance();
		        $field_definitions = $user_defined_fields->getVisibleDefinitions();
		        if($field_definitions) 
		        {
		        	foreach ($field_definitions as $key => $definition)
		        	{
		        		if($definition['field_type']==UDF_TYPE_TEXT && $definition['changeable'])
		        			$av->addOption(new ilRadioOption($definition['field_name'],"UDF:".$definition['field_id'], $this->txt("info_udf")));
		        	}
		        }
		        	
		        // add radio section
		        $this->form->addItem($av);
        	}
        	
        	
			// read only
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
        $values["author_identification"]     = $this->object->getAuthorIdentification();
        $values["require_policy_consent"]     = $this->object->getRequirePolicyConsent();
        
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
            $this->object->setAuthorIdentification($this->form->getInput("author_identification"));
            $this->object->setRequirePolicyConsent($this->form->getInput("require_policy_consent"));

            $this->object->update();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editProperties");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

//
// Agree Policies
//    
    /**
     * agree policy
     */
    function agreePolicy()
    {
    	global $lng, $ilCtrl;
    	
    	include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteUser.php");
    	$this->EtherpadLiteUser = new ilEtherpadLiteUser();
    	
    	$hash = array();
    	$policiesContent = $this->policiesContent();
    	foreach($policiesContent as $type => $data)
    	{
    		$hash[$type] = hash('sha1', $data['content']);
    	}
    	
    	if($this->EtherpadLiteUser->agreePolicy($hash))
    	{
    		// ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
    	}
    	else
    	{
    		ilUtil::sendFailure("error", true);
    	}
    	
    	$ilCtrl->redirect($this, "showContent");
    	
    }

//
// revoke consent
// !!! only for demonstration !!!
//
     function revokeConsent()
     {
     global $lng, $ilCtrl;
       
     	include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteUser.php");
    	$this->EtherpadLiteUser = new ilEtherpadLiteUser();
        	 
        if($this->EtherpadLiteUser->revokeConsent())	{
        	// ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        } else {
        	ilUtil::sendFailure("error", true);
        }
		$ilCtrl->redirect($this, "showContent");
    }

    
//
// inspect policies
//
	function inspectPolicies()
	{
		global $tpl, $ilTabs, $ilCtrl;
		$ilTabs->activateTab("agreement");
		
		$policiesTpl = new ilTemplate("tpl.policies.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite");
		
		/**
		 * MODALs
		 */
		$policiesContent = $this->policiesContent();
		foreach($policiesContent as $type => $data)
		{
			$policiesTpl->setVariable(
				$type."MODAL", 
				$this->buildModal(
					$type."MODAL", 
					$data['heading'], 
					$data['content'],
					$data['pdf']
				)->getHTML()
			);
		}
		
		
		// !only fr demonstration !
		$policiesTpl->setVariable("REVOKETLINK",$ilCtrl->getLinkTarget($this, "revokeConsent"));
		
		$tpl->setContent($policiesTpl->get());
	}
   
    
//
// Show content
//

    /**
     * Show content
     */
    function showContent()
    {
        global $tpl, $ilTabs, $ilUser, $lng, $ilCtrl;
        
        try
        {
            $this->object->init();
            $ilTabs->activateTab("content");
            $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/templates/css/etherpad.css");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/js/ilEtherpadLite.js");
            			            
            // build javascript required to load the pad
            $pad = new ilTemplate("tpl.pad.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite");
            $pad->setVariable("POLICIESDIV","none");
            
            // Show Elements depending on settings in the administration of the plugin
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteConfig.php");
            $this->adminSettings = new ilEtherpadLiteConfig();
            
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteUser.php");
            $this->EtherpadLiteUser = new ilEtherpadLiteUser();
            
            // writeable pad
            $padID = $this->object->getEtherpadLiteID();
            
			if($this->object->getReadOnly()) 
			{ 
				$padID = $this->object->getReadOnlyID(); 
				ilUtil::sendInfo($this->txt("read_only_notice"), true);
			} 
			elseif($this->object->getRequirePolicyConsent() && !$this->EtherpadLiteUser->getPolicyAgreement())
			{
				$padID = $this->object->getReadOnlyID();
				
				$pad->setVariable("POLICIESDIV","block");
				$pad->setVariable("CONSENTLINK",$ilCtrl->getLinkTarget($this, "agreePolicy"));
				
				ilUtil::sendFailure("Es liegen noch keine Einwilligung in die datenschutz- und urheberrechtlichen Erklärungen vor!
						<br/>An dieser Klausur können Sie daher noch nicht teilnehmen.", true);
			}
			elseif($this->adminSettings->getValue("author_identification_conf"))
			{	
				// UDF selected by admin, but no value was set by user?						
				$authorVisibilityType = $this->object->getAuthorIdentification();
				$field_id = substr($authorVisibilityType, strpos($authorVisibilityType, ":")+1);
				if (stripos($authorVisibilityType,'UDF') !== false && !$this->getUDFValue($field_id))
				{	
					$padID = $this->object->getReadOnlyID();
					include_once("./Services/User/classes/class.ilUserDefinedFields.php");
					$user_defined_fields =& ilUserDefinedFields::_getInstance();
					$field_definition = $user_defined_fields->getDefinition($field_id);
					$profileSettingsLink = "<a href='ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToProfile'>".$lng->txt("personal_profile")."</a>";
					$noNameMsg = $this->txt("no_name_set")."! ";
					$noNameMsg .= $lng->txt("form_empty_fields")." <i>".$field_definition["field_name"]."</i> ".$this->txt("at")." $profileSettingsLink.";
					ilUtil::sendFailure($noNameMsg, true);
				}
			}
				
			/**
			 * Modals
			 */
			$policiesContent = $this->policiesContent();
			foreach($policiesContent as $type => $data)
			{
				$pad->setVariable(
					$type."MODAL", 
					$this->buildModal(
						$type."MODAL", 
						$data['heading'], 
						$data['content'],
						$data['pdf']
					)->getHTML()
				);
			}

			
            $pad->setVariable("ENTER_FULLSCREEN",$this->txt("enter_fullscreen"));
            $pad->setVariable("LEAVE_FULLSCREEN",$this->txt("leave_fullscreen"));
            $pad->setVariable("PROTOCOL",($this->adminSettings->getValue("https") ? "https" : "http"));
            $pad->setVariable("HOST",($this->adminSettings->getValue("host")));
            $pad->setVariable("PORT",($this->adminSettings->getValue("port")));
            $pad->setVariable("PATH",($this->adminSettings->getValue("path")));
            $pad->setVariable("ETHERPADLITE_ID",$padID);
                       
            // if "author_identification_conf" is'nt set, write full name of authors. Otherwise construct identifier based on object setting from DB.
            $pad->setVariable("USER_NAME",(!$this->adminSettings->getValue("author_identification_conf") ? rawurlencode($ilUser->getFullname()) : $this->constructAuthorIdentification($this->object->getAuthorIdentification())));
            
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
            include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
            $permalink = new ilPermanentLinkGUI('xpdl', $this->object->getRefId());
            $this->tpl->setVariable('PRMLINK', $permalink->getHTML());
        } catch (Exception $e)
        {
            $ilTabs->activateTab("content");
            $tpl->setContent($this->txt("load_error")." ".$e->getMessage());
        }
    }

    
    //
    // MODAL
    //
    private function buildModal($tplvar, $heading, $content, $pdf)
    {
    	$link = "<p align='right'><a target='_blank' href='".$pdf."'>als PDF</a></p>";
    	include_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
    	$modal = ilModalGUI::getInstance();
    	$modal->setHeading($heading);
    	$modal->setId("il".$tplvar);
    	$modal->setBody($content.$link);
    	return $modal;
    }
    private function policiesContent()
    {
    	// Show texts depending on settings in the administration of the plugin
    	include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteConfig.php");
    	$this->adminSettings = new ilEtherpadLiteConfig();
    	
    	$rootdir = "./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite";

    	return array(
    			"PrivacyPolicy" => array(
    					"heading" => "Datenschutz und Privatsphäre",
    					"content" => file_get_contents($rootdir.$this->adminSettings->getValue("policy_paths_privacy_html")),
    					"pdf" => $rootdir.$this->adminSettings->getValue("policy_paths_privacy_pdf")
    			),
    			"IPropPolicy" => array(
    					"heading" => "Urheberschaft",
    					"content" => file_get_contents($rootdir.$this->adminSettings->getValue("policy_paths_iprop_html")),
    					"pdf" => $rootdir.$this->adminSettings->getValue("policy_paths_iprop_pdf")
    			),
    			"Rules" => array(
    					"heading" => "Regelwerk",
    					"content" => file_get_contents($rootdir.$this->adminSettings->getValue("policy_paths_rules_html")),
    					"pdf" => $rootdir.$this->adminSettings->getValue("policy_paths_rules_pdf")
    			)
    	);
    }
    

    private function constructAuthorIdentification($type)
    {
    	global $ilUser;
    	switch (true)
    	{
    		case stripos($type,'UDF') !== false:
    			$field_id = substr($type, strpos($type, ":")+1);
    			return $this->getUDFValue($field_id) ? rawurlencode($this->getUDFValue($field_id)) : $this->txt("unknown_identity"); break;			
    		case $type === 'username':
    			return rawurlencode($ilUser->getPublicName()); break;
    		case $type === 'fullname':
    		default:
    			return rawurlencode($ilUser->getFullname());
    	}
    }    
    
    private function getUDFValue($field_id)
    {
    	global $ilUser;
    	$user_defined_data = $ilUser->getUserDefinedData();
    	return $user_defined_data['f_'.$field_id] ? $user_defined_data['f_'.$field_id] : false;
    }
    

  /*
    public function initCreateForm($a_new_type)
    {
    	$form = parent::initCreateForm($a_new_type);

    	include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteConfig.php");
    	$this->adminSettings = new ilEtherpadLiteConfig();
    	if($this->adminSettings->getValue("author_identification_conf")) 
    	{
    		$av = new ilCustomInputGUI("", "");
    		$av->setHtml($this->txt("info_author_identification") . " " . $this->txt("info_author_identification_selectable"));
    		$form->addItem($av);
    	}

    	return $form;
    }
   */

}
?>
