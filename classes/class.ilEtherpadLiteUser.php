<?php

/**
 * EtherpadLite User class
 * @author  Christoph Becker <christoph.becker@uni-passau.de>
 * @version $Id$
 *
 */
class ilEtherpadLiteUser
{
	public function  __construct() 
	{
		global $ilUser;
		$this->setUsername($ilUser->getLogin());
		if(!$this->getUserValues()) 
		{
			$this->addUser();
		}
	}
	
    /**
     * Get user values
     */
    function getUserValues()
    {
    	global $ilDB;
		$result = $ilDB->query("SELECT * FROM rep_robj_xpdl_user WHERE username = " . $ilDB->quote($this->getUsername(), "text"));
		if($result->numRows() == 0) return false; 
		while ($rec = $ilDB->fetchAssoc($result))
		{
			$this->setPolicyAgreement($rec["policy_agreement"]);
		}
		return true;
    }
    
    
    /**
     * New user
     */
	public function addUser(){
		global $ilDB;
			    
	    $ilDB->manipulate("INSERT INTO rep_robj_xpdl_user (username, policy_agreement) VALUES (" .
	    		$ilDB->quote($this->getUsername(), "text") . "," .
	    		$ilDB->quote(0, "boolean") .
	    		")");	    
	}
	
	/**
	 * Agree Policy
	 */
	public function agreePolicy($HashArray){
		global $ilDB;
		
		foreach($HashArray as $type => $hash)
		{
			$hashText .= $type . ":" . $hash ."#"; 
		}		
		return $ilDB->manipulate("UPDATE rep_robj_xpdl_user SET 
				policy_agreement = " . $ilDB->quote(1, "boolean") . ",
				hashes = " . $ilDB->quote($hashText, "text") . " 
				WHERE username = " . $ilDB->quote($this->getUsername(), "text"));
	}
	
	/**
	 * Revoke Consent
	 * ! only for demonstration !
	 */
	public function revokeConsent(){
		global $ilDB;
		return $ilDB->manipulate("DELETE FROM rep_robj_xpdl_user 
				WHERE username = " . $ilDB->quote($this->getUsername(), "text"));
	}
	
    
    /** 
     * class setter and getter
     */
       
    /**
     * Set policy agreement
     *
     * @param    boolean
     */
    public function setPolicyAgreement($a_val)
    {
    	$this->policy_agreement = $a_val;
    }
    
    /**
     * Get policy agreement
     *
     * @return    boolean
     */
    public function getPolicyAgreement()
    {
    	return $this->policy_agreement;
    }
    
    /**
     * Set username
     *
     * @param    boolean
     */
    public function setUsername($a_val)
    {
    	$this->username = $a_val;
    }
    
    /**
     * Get username
     *
     * @return    string username
     */
    public function getUsername()
    {
    	return $this->username;
    }
}


?>
