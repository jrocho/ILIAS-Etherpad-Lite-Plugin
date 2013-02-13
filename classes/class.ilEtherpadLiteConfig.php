<?php

/**
 * EtherpadLite configuration user interface class
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilEtherpadLiteConfig
{
    /**
     * @param $key
     * @param $value
     */
    public function setValue($key, $value)
    {
        global $ilDB;

        if(!is_string($this->getValue($key)))
        {
            $ilDB->insert("rep_robj_xpdl_adm_set"   , array("epkey"   => array("text",$key),"epvalue" => array("text",$value)));
        }
        else
        {
            $ilDB->update("rep_robj_xpdl_adm_set"   , array("epkey"   => array("text", $key), "epvalue" => array("text",$value))
                                                    , array("epkey" => array("text",$key))
            );
        }
    }

    /**
     * @param $key
     *
     * @return bool|string
     */
    public function getValue($key)
    {
        global $ilDB;
        $result = $ilDB->query("SELECT epvalue FROM rep_robj_xpdl_adm_set WHERE epkey = " . $ilDB->quote($key, "text"));
        if($result->numRows() == 0)
        {
            return false;
        }
        $record = $ilDB->fetchAssoc($result);

        return (string)$record['epvalue'];
    }
}


?>
