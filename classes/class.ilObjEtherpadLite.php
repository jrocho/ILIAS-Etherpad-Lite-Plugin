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
* @author Jan Rocho <jan@rocho.eu>
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
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
                
                global $ilUser;
                
                $file = "./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/etherpadlite.ini.php";
                $ini = new ilIniFile($file);
                $ini->read();
                
                $this->setEtherpadLiteHost($ini->readVariable("etherpadlite", "host"));
                $this->setEtherpadLitePort($ini->readVariable("etherpadlite", "port"));
                $this->setEtherpadLiteApiKey($ini->readVariable("etherpadlite", "apikey"));
                $this->setEtherpadLiteDefaultText($ini->readVariable("etherpadlite", "defaulttext"));
                $this->setEtherpadLiteGroup($ini->readVariable("etherpadlite", "group"));
                $this->setEtherpadLiteDomain($ini->readVariable("etherpadlite", "domain"));
                $this->setEtherpadLiteHTTPS($ini->readVariable("etherpadlite", "https"));
				
                 // lets connect to the api
                if(strlen($this->getEtherpadLiteHTTPS()) == 0)
				{
					$protocol = 'http';
				}
				else
				{
					switch($this->getEtherpadLiteHTTPS())
					{
						case 1:
								$protocol = 'https';
								break;
						default:
								$protocol = 'http';
								
					}
				}
				
				$this->setEtherpadLiteConnectionPlain($protocol.'://'.$this->getEtherpadLiteHost().':'.$this->getEtherpadLitePort());
				
				$this->setEtherpadLiteConnection(
                        new EtherpadLiteClient($this->getEtherpadLiteApiKey(),
							$protocol.'://'.$this->getEtherpadLiteHost().':'.$this->getEtherpadLitePort().'/api'));   

                // get our mapped group
                $this->setEtherpadLiteGroupMapper($this->getEtherpadLiteConnection()->createGroupIfNotExistsFor(
                        $this->getEtherpadLiteGroup())); 
                                
                $this->setEtherpadLiteUserMapper($this->getEtherpadLiteConnection()->createAuthorIfNotExistsFor(
                        $ilUser->id, $ilUser->firstname.' '.$ilUser->lastname)); 
                              
                $validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future
                $sessionID = $this->getEtherpadLiteConnection()->createSession(
                        $this->getEtherpadLiteGroupMapper(), 
                        $this->getEtherpadLiteUserMapper(), $validUntil);
                
                $sessionID = $sessionID->sessionID;
                setcookie('sessionID', $sessionID, 0, '/', $this->getEtherpadLiteDomain()); 
   
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
	function doCreate()
	{
		global $ilDB;
		
                $tempID = $this->getEtherpadLiteConnection()->createGroupPad(
                        $this->getEtherpadLiteGroupMapper(),$this->genRandomString(),$this->getEtherpadDefaultText());
                
                $this->setEtherpadLiteID($tempID->padID);
                
		$ilDB->manipulate("INSERT INTO rep_robj_xpdl_data ".
			"(id, is_online, epadl_id) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote($this->getEtherpadLiteID(), "text").",".
			$ilDB->quote(1 ,"integer").",".
			$ilDB->quote(1 ,"integer").",".
			$ilDB->quote(1 ,"integer").",".
			$ilDB->quote(1 ,"integer").
			")");
                
        $this->getEtherpadLiteConnection()->setPublicStatus($this->getEtherpadLiteID(),0);
	}
	
	/**
	* Read data from db
	*/
	function doRead()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xpdl_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setOnline($rec["is_online"]);
			$this->setEtherpadLiteID($rec["epadl_id"]);
			$this->setShowControls($rec["show_controls"]);
			$this->setLineNum($rec["show_lines"]);
			$this->setUseColor($rec["use_color"]);
			$this->setChatVisible($rec["show_chat"]);
		}
	}
	
	/**
	* Update data
	*/
	function doUpdate()
	{
		global $ilDB;
		
		$ilDB->manipulate($up = "UPDATE rep_robj_xpdl_data SET ".
			" is_online = ".$ilDB->quote($this->getOnline(), "integer").",".
			" epadl_id = ".$ilDB->quote($this->getEtherpadLiteID(), "text").",".
			" show_controls = ".$ilDB->quote($this->getShowControls(), "integer").",".
			" show_lines = ".$ilDB->quote($this->getLineNum(), "integer").",".
			" use_color = ".$ilDB->quote($this->getUseColor(), "integer").",".
			" show_chat = ".$ilDB->quote($this->getChatVisible(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
	}
	
	/**
	* Delete data from db
	*/
	function doDelete()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xpdl_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setEtherpadLiteID($rec["epadl_id"]);
		}
		
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
	
	/**
	* Do Cloning
	*/
	function doClone($a_target_id,$a_copy_id,$new_obj)
	{
		global $ilDB;
		
		$new_obj->setOnline($this->getOnline());
		$new_obj->setShowControls($this->getShowControls());
		$new_obj->setLineNum($this->getLineNum());
		$new_obj->setUseColor($this->getUseColor());
		$new_obj->setChatVisible($this->getChatVisible());
		
		//$new_obj->setEtherpadLiteID($this->GetEtherpadLiteID());		
		$new_obj->update();
                
                // get old pad text
                $oldPadText = $this->getEtherpadLiteConnection()->getText($this->GetEtherpadLiteID());
                // write old pad text to new pad
                $this->getEtherpadLiteConnection()->setText(
                        $new_obj->getEtherpadLiteID(),
                        $oldPadText->text);
 
	}
	
//
// Set/Get Methods
//

	/**
	* Set online
	*
	* @param	boolean		online
	*/
	function setOnline($a_val)
	{
		$this->online = $a_val;
	}
	
	/**
	* Get online
	*
	* @return	boolean		online
	*/
	function getOnline()
	{
		return $this->online;
	}
	
	/**
	* Set etherpad lite id
	*
	* @param	integer		etherpad lite id
	*/
	function setEtherpadLiteID($a_val)
	{
		$this->etherpadlite_id = $a_val;
	}
	
	/**
	* Get oetherpad lit id
	*
	* @return	integer		etherpad lite id
	*/
	function getEtherpadLiteID()
	{
		return $this->etherpadlite_id;
	}
	
	/**
	* Set EtherpadLiteHost
	*
	* Host of the EtherpadLite installation
	*
	* @param  string  $a_val  epadlhost
	*/
	function setEtherpadLiteHost($a_val)
	{
		$this->epadlhost = $a_val;
	}
	
	/**
	* Get EtherpadLiteHost
	*
	* @return string  epadlhost
	*/
	function getEtherpadLiteHost()
	{
	return $this->epadlhost;
	}

	/**
	* Set EtherpadLiteDomain
	*
	* Domain of the EtherpadLite installation
	*
	* @param  string  $a_val  epadldomain
	*/
	function setEtherpadLiteDomain($a_val)
	{
		$this->epadldomain = $a_val;
	}
	
	/**
	* Get EtherpadLiteDomain
	*
	* @return string  epadldomain
	*/
	function getEtherpadLiteDomain()
	{
	return $this->epadldomain;
	}
	
	/**
	* Set EtherpadLitePort
	*
	* Port of the EtherpadLite installation
	*
	* @param  string  $a_val  epadlport
	*/
	function setEtherpadLitePort($a_val)
	{
		$this->epadlport = $a_val;
	}
	
	/**
	* Get EtherpadLitePort
	*
	* @return string  epadlport
	*/
	function getEtherpadLitePort()
	{
	return $this->epadlport;
	}
	
	/**
	* Set EtherpadLiteApiKey
	*
	* API Key of the EtherpadLite installation
	*
	* @param  string  $a_val  epadlhost
	*/
	function setEtherpadLiteApiKey($a_val)
	{
		$this->epadlapikey = $a_val;
	}
	
	/**
	* Get EtherpadLiteApiKey
	*
	* @return string  epadlapikey
	*/
	function getEtherpadLiteApiKey()
	{
	return $this->epadlapikey;
	}
	
	/**
	* Set EtherpadLiteDefaultText
	*
	* Default text for new pads
	*
	* @param  string  $a_val  epadldefaulttext
	*/
	function setEtherpadLiteDefaultText($a_val)
	{
		$this->epadldefaulttext = $a_val;
	}
	
	/**
	* Get EtherpadLiteDefaultText
	*
	* @return string  epadldefaulttext
	*/
	function getEtherpadDefaultText()
	{
	return $this->epadldefaulttext;
	}
	
	/**
	* Set EtherpadLiteGroup
	*
	* Group for the ILIAS pads
	*
	* @param  string  $a_val  epadlgroup
	*/
	function setEtherpadLiteGroup($a_val)
	{
		$this->epadlgroup = $a_val;
	}
	
	/**
	* Get EtherpadLiteGroup
	*
	* @return string  epadlgroup
	*/
	function getEtherpadLiteGroup()
	{
	return $this->epadlgroup;
	}
	
	/**
	* Set EtherpadLiteConnection
	*
	* Connection
	*
	* @param  string  $a_val  epadlconnect
	*/
	function setEtherpadLiteConnection($a_val)
	{
		$this->epadlconnect = $a_val;
	}
	
	/**
	* Get EtherpadLiteConnection
	*
	* @return string  epadlconnect
	*/
	function getEtherpadLiteConnection()
	{
	return $this->epadlconnect;
	}
	
	/**
	* Set EtherpadLiteConnectionPlain
	*
	* Connection
	*
	* @param  string  $a_val  epadlconnectplain
	*/
	function setEtherpadLiteConnectionPlain($a_val)
	{
		$this->epadlconnectplain = $a_val;
	}
	
	/**
	* Get EtherpadLiteConnectionPlain
	*
	* @return string  epadlconnectplain
	*/
	function getEtherpadLiteConnectionPlain()
	{
	return $this->epadlconnectplain;
	}
	
	/**
	* Set EtherpadLiteGroupMapper
	*
	* Mapped Group for the ILIAS pads
	*
	* @param  string  $a_val  epadlgroupmapper
	*/
	function setEtherpadLiteGroupMapper($a_val)
	{
		$this->epadlgroupmapper = $a_val->groupID;
	}
	
	/**
	* Get EtherpadLiteGroupMapper
	*
	* @return string  epadlgroupmapper
	*/
	function getEtherpadLiteGroupMapper()
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
	function setEtherpadLiteUserMapper($a_val)
	{
		$this->epadlusermapper = $a_val->authorID;
	}
	
	/**
	* Get EtherpadLiteUserMapper
	*
	* @return string  epadlusermapper
	*/
	function getEtherpadLiteUserMapper()
	{
	return $this->epadlusermapper;
	}
	
	/**
	* Set EtherpadLiteHTTPS
	*
	* check if we are using HTTPS
	*
	* @param  string  $a_val  epadlhttps
	*/
	function setEtherpadLiteHTTPS($a_val)
	{
		$this->epadlhttps = $a_val;
	}
	
	/**
	* Get EtherpadLiteHTTPS
	*
	* @return string  epadlhttps
	*/
	function getEtherpadLiteHTTPS()
	{
	return $this->epadlhttps;
	}
	
	/**
	* Set controls (visibility)
	*
	* @param	boolean		online
	*/
	function setShowControls($a_val)
	{
		$this->controls = $a_val;
	}
	
	/**
	* Get controls (visibility)
	*
	* @return	boolean		online
	*/
	function getShowControls()
	{
		return $this->controls;
	}
	
	/**
	* Set linenum
	* Display / hide line numbers
	*
	* @param	boolean		online
	*/
	function setLineNum($a_val)
	{
		$this->linenum = $a_val;
	}
	
	/**
	* Get linenum
	* Display / hide line numbers
	*
	* @return	boolean		online
	*/
	function getLineNum()
	{
		return $this->linenum;
	}
	
	/**
	* Set chatvisible
	* Display / hide chat
	*
	* @param	boolean		online
	*/
	function setChatVisible($a_val)
	{
		$this->chatvisible = $a_val;
	}
	
	/**
	* Get chatvisible
	* Display / hide chat
	*
	* @return	boolean		online
	*/
	function getChatVisible()
	{
		return $this->chatvisible;
	}
	
	/**
	* Set usecolor
	* Use colors?
	*
	* @param	boolean		online
	*/
	function setUseColor($a_val)
	{
		$this->usecolor = $a_val;
	}
	
	/**
	* Get usecolor
	* Use colors?
	*
	* @return	boolean		online
	*/
	function getUseColor()
	{
		return $this->usecolor;
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
