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

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/libs/etherpad-lite-client/etherpad-lite-client.php';



/**
* Application class for EtherpadLite repository object.
*
* @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
* @author Jan Rocho <jan.rocho@fh-dortmund.de>
*
* $Id$
*/
class ilObjEtherpadLite extends ilObjectPlugin
{
	/**
	* Constructor
	*
	* @access	public
	*/
	public function __construct($a_ref_id = 0)
	{                		
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteConfig.php");
		$this->adminSettings = new ilEtherpadLiteConfig();
		parent::__construct($a_ref_id);
	}
	
	public function init()
	{
		$this->connectToEtherpad();
        $this->setSession();
	}
	
	/**
     * Sets up connection to Etherpad
     *
     * @access	protected
     */
    protected function connectToEtherpad()
    {
        global $ilUser;

        try
        {
            $this->setEtherpadLiteConnection(new EtherpadLiteClient($this->adminSettings->getValue("apikey"), 
            	($this->adminSettings->getValue("https") ? "https" : "http"). '://' . $this->adminSettings->getValue("host") . ':' . 
            	$this->adminSettings->getValue("port") . $this->adminSettings->getValue("path") . '/api',
            	$this->adminSettings->getValue("https_validate_curl")));
            	
            if($this->isOldEtherpad())
            {
            	$this->setEtherpadLiteGroupMapper($this->getEtherpadLiteConnection()->createGroupIfNotExistsFor($this->adminSettings->getValue("old_group")));	
			}
			else
			{
            	$this->setEtherpadLiteGroupMapper($this->getEtherpadLiteConnection()->createGroupIfNotExistsFor($this->getId()));
            }
            $this->setEtherpadLiteUserMapper($this->getEtherpadLiteConnection()->createAuthorIfNotExistsFor($ilUser->id, $ilUser->firstname . ' ' . $ilUser->lastname));
        }
        catch (Exception $e)
        {
            include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
            throw new ilCtrlException($e->getMessage());
        }

    }
    
    /**
     * Check if this is an old etherpad
     *
     * @access	protected
     */
    protected function isOldEtherpad()
    {
    	global $ilDB;
    
    	$r = $ilDB->query("SELECT * FROM rep_robj_xpdl_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		
		if ($r->numRows() == 1)
		{
			$rec = $r->fetchRow(DB_FETCHMODE_OBJECT);
			return $rec->old_pad;
		}
		
		
		return false;
    }
    
    /**
     * Sets a valid Session. First it checks if already a valid session exists, if not one will be created and if there is an expired one, it will be deleted
     */
    protected function setSession()
    {    	
        include_once("./Services/UICore/exceptions/class.ilCtrlException.php");
        try
        {
            //check if pad still exists in database (maybe the pad is deleted in etherpadlite-database but not in ilias), if it does not exist, throw error accordingly
            $pad = $this->getEtherpadLiteConnection()->listPads($this->getEtherpadLiteGroupMapper());
            if($pad->padIDs==null)
            {
                throw new ilCtrlException($this->txt("error_not_found_in_db"));
            }
            //check if valid Session for this user in this group already exists
            $sessionID = null;
            $sessionList = $this->getEtherpadLiteConnection()->listSessionsOfGroup($this->getEtherpadLiteGroupMapper());
            
            if (isset($sessionList))
            {
                foreach ($sessionList as $sessionKey => $sessionData)
                {
                    if ($this->getEtherpadLiteUserMapper() == $sessionData->authorID)
                    {
                        if ($sessionID->validUntil > time())
                        {
                            $sessionID = $sessionKey;
                        } else
                        {
                            $this->getEtherpadLiteConnection()->deleteSession($sessionKey);
                        }
                    }

                }
            }

            //if no valid Session exists, create a new one
            if ($sessionID == null)
            {
                $validUntil = mktime(0, 0, 0, date("m"), date("d") + 1, date("y")); // One day in the futur
                $sessionID  = $this->getEtherpadLiteConnection()->createSession($this->getEtherpadLiteGroupMapper(), $this->getEtherpadLiteUserMapper(), $validUntil);
                $sessionID  = $sessionID->sessionID;
            }
            setcookie('sessionID', $sessionID, 0, '/', $this->adminSettings->getValue("domain"));
        }
        catch (Exception $e)
        {
            throw new ilCtrlException($e->getMessage());
        }

    }

	/**
	* Get type.
	*/
	final function initType()
	{
		$this->setType("xpdl");
	}
	
	/**
	* Create object
	*/
	protected function doCreate()
    {
        global $ilDB;
        $this->connectToEtherpad();
        $tempID = $this->getEtherpadLiteConnection()->createGroupPad($this->getEtherpadLiteGroupMapper(), $this->genRandomString(), $this->adminSettings->getValue("defaulttext"));
        $this->setEtherpadLiteID($tempID->padID);
        
        $readOnlyID =  $this->getEtherpadLiteConnection()->getReadOnlyID($this->getEtherpadLiteID());
		$this->setReadOnlyID($readOnlyID->readOnlyID);

        $ilDB->manipulate("INSERT INTO rep_robj_xpdl_data (id, is_online, epadl_id,show_controls,line_numbers,show_colors,show_chat,monospace_font,show_style,show_list,show_redo,show_coloring,show_heading,show_import_export, show_timeline,old_pad, read_only_id, read_only) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote(0, "integer") . "," .
            $ilDB->quote($this->getEtherpadLiteID(), "text") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_controls"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_line_numbers"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_colors"), "boolean") . "," .
			$ilDB->quote($this->adminSettings->getValue("default_show_chat"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_monospace_font"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_controls_default_show_style"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_controls_default_show_list"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_controls_default_show_redo"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_controls_default_show_coloring"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_controls_default_show_heading"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_controls_default_show_imp_exp"), "boolean") . "," .
            $ilDB->quote($this->adminSettings->getValue("default_show_controls_default_show_timeline"), "boolean") . "," .
            $ilDB->quote(0, "boolean") . "," . 
            $ilDB->quote($this->getReadonlyID(), "text") . "," . 
            $ilDB->quote(0, "boolean") .
            ")");

        $this->getEtherpadLiteConnection()->setPublicStatus($this->getEtherpadLiteID(), 0);


    }
	
	/**
     * Read data from db
     */
    protected function doRead()
    {
        global $ilDB;

        $set = $ilDB->query("SELECT * FROM rep_robj_xpdl_data " .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set))
        {
            $this->setOnline($rec["is_online"]);
            $this->setEtherpadLiteID($rec["epadl_id"]);
            $this->setShowControls($rec["show_controls"]);
            $this->setLineNumbers($rec["line_numbers"]);
            $this->setShowColors($rec["show_colors"]);
            $this->setShowChat($rec["show_chat"]);
            $this->setMonospaceFont($rec["monospace_font"]);
            $this->setShowStyle($rec["show_style"]);
            $this->setShowList($rec["show_list"]);
            $this->setShowRedo($rec["show_redo"]);
            $this->setShowColoring($rec["show_coloring"]);
            $this->setShowHeading($rec["show_heading"]);
            $this->setShowImportExport($rec["show_import_export"]);
            $this->setShowTimeline($rec["show_timeline"]);
            $this->setOldEtherpad($rec["old_pad"]);
            $this->setReadOnlyID($rec["read_only_id"]); 
            $this->setReadOnly($rec["read_only"]);
        }
        
    }
	
	/**
     * Update data
     */
    protected function doUpdate()
    {
        global $ilDB;

        $ilDB->manipulate($up = "UPDATE rep_robj_xpdl_data SET " .
                " is_online = " . $ilDB->quote($this->getOnline(), "integer") . "," .
                " epadl_id = " . $ilDB->quote($this->getEtherpadLiteID(), "text") . "," .
                " show_controls = " . $ilDB->quote($this->getShowControls(), "integer"). "," .
                " line_numbers = " . $ilDB->quote($this->getLineNumbers(), "integer"). "," .
                " show_colors = " . $ilDB->quote($this->getShowColors(), "integer"). "," .
                " show_chat = " . $ilDB->quote($this->getShowChat(), "integer"). "," .
                " monospace_font = " . $ilDB->quote($this->getMonospaceFont(), "integer"). "," .
                " show_style = " . $ilDB->quote($this->getShowStyle(), "integer"). "," .
                " show_list = " . $ilDB->quote($this->getShowList(), "integer"). "," .
                " show_redo = " . $ilDB->quote($this->getShowRedo(), "integer"). "," .
                " show_coloring = " . $ilDB->quote($this->GetShowColoring(), "integer"). "," .
                " show_heading = " . $ilDB->quote($this->getShowHeading(), "integer"). "," .
                " show_import_export = " . $ilDB->quote($this->getShowImportExport(), "integer"). "," .
                " show_timeline = " . $ilDB->quote($this->getShowTimeline(), "integer"). "," . 
                " read_only_id = " . $ilDB->quote($this->getReadOnlyID(), "text"). "," . 
                " read_only = " . $ilDB->quote($this->getReadOnly(), "integer").
                " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }
	
	/**
	* Delete data from db
	*/
	function doDelete()
	{
		global $ilDB,$ilLog;
		$this->connectToEtherpad();
		
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xpdl_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setEtherpadLiteID($rec["epadl_id"]);
		}
		
		//echo $this->getEtherpadLiteID();
		$ilLog->write("Delete Pad ID: ".$this->getEtherpadLiteID());
		
		if($this->getEtherpadLiteConnection()->deletePad($this->getEtherpadLiteID()))
		{
			$ilDB->manipulate("DELETE FROM rep_robj_xpdl_data WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		}
		else
		{
			return false;
		}
		
	}
		
	
//
// Set/Get Methods
//
/**
     * Set online
     *
     * @param    boolean        online
     */
    public function setOnline($a_val)
    {
        $this->online = $a_val;
    }

    /**
     * Get online
     *
     * @return    boolean        online
     */
    public function getOnline()
    {
        return $this->online;
    }

    /**
     * Set etherpad lite id
     *
     * @param    integer        etherpad lite id
     */
    public function setEtherpadLiteID($a_val)
    {
        $this->etherpadlite_id = $a_val;
    }

    /**
     * Get oetherpad lit id
     *
     * @return    integer        etherpad lite id
     */
    public function getEtherpadLiteID()
    {
        return $this->etherpadlite_id;
    }

    /**
     * Set EtherpadLiteConnection
     *
     * Connection
     *
     * @param  string  $a_val  epadlconnect
     */
    public function setEtherpadLiteConnection($a_val)
    {
        $this->epadlconnect = $a_val;
    }

    /**
     * Get EtherpadLiteConnection
     *
     * @return string  epadlconnect
     */
    public function getEtherpadLiteConnection()
    {
        return $this->epadlconnect;
    }

    /**
     * Set EtherpadLiteGroupMapper
     *
     * Mapped Group for the ILIAS pads
     *
     * @param  string  $a_val  epadlgroupmapper
     */
    public function setEtherpadLiteGroupMapper($a_val)
    {
        $this->epadlgroupmapper = $a_val->groupID;
    }

    /**
     * Get EtherpadLiteGroupMapper
     *
     * @return string  epadlgroupmapper
     */
    public function getEtherpadLiteGroupMapper()
    {
        return $this->epadlgroupmapper;
    }

    /**
     * Set EtherpadLiteUserMapper
     *
     * Mapped User for the ILIAS pads
     *
     * @param  string  $a_val  epadlusermapper
     */
    public function setEtherpadLiteUserMapper($a_val)
    {
        $this->epadlusermapper = $a_val->authorID;
    }

    /**
     * Get EtherpadLiteUserMapper
     *
     * @return string  epadlusermapper
     */
    public function getEtherpadLiteUserMapper()
    {
        return $this->epadlusermapper;
    }

    /**
     * Set Show Chat
     *
     * @param  boolean  $a_val  show_chat
     */
    public function setShowChat($a_val)
    {
        $this->showChat = $a_val;
    }

    /**
     * Get Show Chat
     *
     * @return boolean  showChat
     */
    public function getShowChat()
    {
        if(!$this->adminSettings->getValue("conf_show_chat"))
        {
            return $this->adminSettings->getValue("default_show_chat");
        }
        return $this->showChat;
    }

    /**
     * Set line numbers
     *
     * @param  boolean  $a_val  line_numbers
     */
    public function setLineNumbers($a_val)
    {
        $this->lineNumbers = $a_val;
    }

    /**
     * Get line numbers
     *
     * @return boolean  lineNumbers
     */
    public function getLineNumbers()
    {
        if(!$this->adminSettings->getValue("conf_line_numbers"))
        {
            return $this->adminSettings->getValue("default_line_numbers");
        }
        return $this->lineNumbers;
    }

    /**
     * Set monospace font
     *
     * @param  boolean  $a_val  monospace_font
     */
    public function setMonospaceFont($a_val)
    {
        $this->monospaceFont = $a_val;
    }

    /**
     * Get monospace font
     *
     * @return boolean  monospace font
     */
    public function getMonospaceFont()
    {
        if(!$this->adminSettings->getValue("conf_monospace_font"))
        {
            return $this->adminSettings->getValue("default_monospace_font");
        }
        return $this->monospaceFont;
    }

    /**
     * Set Show colors
     *
     * @param  boolean  $a_val  show_colors
     */
    public function setShowColors($a_val)
    {
        $this->showColors = $a_val;
    }

    /**
     * Get Show colors
     *
     * @return boolean  showColors
     */
    public function getShowColors()
    {
        if(!$this->adminSettings->getValue("conf_show_colors"))
        {
            return $this->adminSettings->getValue("default_show_colors");
        }
        return $this->showColors;
    }

    /**
     * Set Show controls
     *
     * @param  boolean  $a_val  show_controls
     */
    public function setShowControls($a_val)
    {
        $this->showControls = $a_val;
    }

    /**
     * Get Show controls
     *
     * @return boolean  showControls
     */
    public function getShowControls()
    {
        if(!$this->adminSettings->getValue("conf_show_controls"))
        {
            return $this->adminSettings->getValue("default_show_controls");
        }
        return $this->showControls;
    }

    /**
     * Set Show style
     *
     * @param  boolean  $a_val  show_style
     */
    public function setShowStyle($a_val)
    {
        $this->showStyle = $a_val;
    }

    /**
     * Get Show colors
     *
     * @return boolean  showStyle
     */
    public function getShowStyle()
    {
        if(!$this->adminSettings->getValue("conf_show_controls_conf_show_style"))
        {
            return $this->adminSettings->getValue("default_show_controls_default_show_style");
        }
        return $this->showStyle;
    }

    /**
     * Set Show list
     *
     * @param  boolean  $a_val  show_list
     */
    public function setShowList($a_val)
    {
        $this->showList = $a_val;
    }

    /**
     * Get Show List
     *
     * @return boolean  showList
     */
    public function getShowList()
    {
        if(!$this->adminSettings->getValue("conf_show_controls_conf_show_list"))
        {
            return $this->adminSettings->getValue("default_show_controls_default_show_list");
        }
        return $this->showList;
    }

    /**
     * Set Show redo
     *
     * @param  boolean  $a_val  show_redo
     */
    public function setShowRedo($a_val)
    {

        $this->showRedo = $a_val;
    }

    /**
     * Get Show redo
     *
     * @return boolean  showRedo
     */
    public function getShowRedo()
    {
        if(!$this->adminSettings->getValue("conf_show_controls_conf_show_redo"))
        {
            return $this->adminSettings->getValue("default_show_controls_default_show_redo");
        }
        return $this->showRedo;
    }

    /**
     * Set Show coloring
     *
     * @param  boolean  $a_val  show coloring
     */
    public function setShowColoring($a_val)
    {
        $this->showColoring = $a_val;
    }

    /**
     * Get Show coloring
     *
     * @return boolean  showColoring
     */
    public function getShowColoring()
    {
        if(!$this->adminSettings->getValue("conf_show_controls_conf_show_coloring"))
        {
            return $this->adminSettings->getValue("default_show_controls_default_show_coloring");
        }
        return $this->showColoring;
    }

    /**
     * Set Show heading
     *
     * @param  boolean  $a_val showHeading
     */
    public function setShowHeading($a_val)
    {
        $this->showHeading = $a_val;
    }

    /**
     * Get Show heading
     *
     * @return boolean  showHeading
     */
    public function getShowHeading()
    {
        if(!$this->adminSettings->getValue("conf_show_controls_conf_show_heading"))
        {
            return $this->adminSettings->getValue("default_show_controls_default_show_heading");
        }
        return $this->showHeading;
    }

    /**
     * Set Show import export
     *
     * @param  boolean  $a_val show_import_export
     */
    public function setShowImportExport($a_val)
    {
        $this->showImportExport = $a_val;
    }

    /**
     * Get Show import export
     *
     * @return boolean  showImportExport
     */
    public function getShowImportExport()
    {
        if(!$this->adminSettings->getValue("conf_show_controls_conf_show_import_export"))
        {
            return $this->adminSettings->getValue("default_show_controls_default_show_imp_exp");
        }
        return $this->showImportExport;
    }

    /**
     * Set Show timeline
     *
     * @param  boolean  $a_val show_timeline
     */
    public function setShowTimeline($a_val)
    {
        $this->showTimeline = $a_val;
    }

    /**
     * Get Show import export
     *
     * @return boolean  showImportExport
     */
    public function getShowTimeline()
    {
        if(!$this->adminSettings->getValue("conf_show_controls_conf_show_timeline"))
        {
            return $this->adminSettings->getValue("default_show_controls_default_show_timeline");
        }
        return $this->showTimeline;
    }

	/**
     * Set Status if this is an old Etherpad (pre 0.0.8)
     *
     * @param  boolean  $a_val 
     */
    public function setOldEtherpad($a_val)
    {
        $this->oldEtherpad = $a_val;
    }

    /**
     * Get Status if this is an old Etherpad (pre 0.0.8)
     *
     * @return boolean  oldEtherpad
     */
    public function getOldEtherpad()
    {
        return $this->oldEtherpad;
    }
    
    /**
     * Set text of Etherpad
     *
     * @param  string  $a_val 
     */
    public function setEtherpadText($a_val)
    {
        $this->EtherpadText = ($a_val);
    }

    /**
     * Get text of Etherpad
     *
     * @return string  EtherpadText
     */
    public function getEtherpadText()
    {
        return $this->EtherpadText;
    }
    
    /**
     * Set readonly of Etherpad
     *
     * @param  boolean  $a_val 
     */
    public function setReadOnly($a_val)
    {
		$this->ReadOnly = $a_val;
    }
    
    /**
     * Get readonly-link of Etherpad
     *
     * @return boolean ReadOnly
     */
    public function getReadOnly()
    {
		return $this->ReadOnly;
    }
    
    /**
     * Set readonlyID of Etherpad
     *
     * @param  boolean  $a_val 
     */
    public function setReadOnlyID($a_val)
    {
		$this->ReadOnlyID = $a_val;
    }
    
    /**
     * Get readonlyID of Etherpad
     *
     * @return boolean ReadOnlyID
     */
    public function getReadOnlyID()
    {
		return $this->ReadOnlyID;
    }
	
	/**
	* Generates random string for pad name
	*
	* @return string  random_pad_name
	*/
	function genRandomString() {
		$length = 20;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$string = '';
			for ($p = 0; $p < $length; $p++) {
			  $string .= $characters[mt_rand(0, strlen($characters))];
			}
		return $string;
	}
}
?>
