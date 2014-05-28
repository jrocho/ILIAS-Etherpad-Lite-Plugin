<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * EtherpadLite configuration user interface class
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author	Jan Rocho <jan.rocho@fh-dortmund.de>
 * @version $Id$
 *
 */
class ilEtherpadLiteConfigGUI extends ilPluginConfigGUI
{
    /**
     * @var array
     */
    protected $fields = array(
        "host"                      => array("type"=>"ilTextInputGUI","info"=>"info_host","subelements"=>null),
        "port"                      => array("type"=>"ilTextInputGUI","info"=>"info_port","subelements"=>null),
        "apikey"                    => array("type"=>"ilTextInputGUI","info"=>"info_apikey","subelements"=>null),
        "domain"                    => array("type"=>"ilTextInputGUI","info"=>"info_domain","subelements"=>null),
        "https"                     => array("type"=>"ilCheckboxInputGUI","info"=>"info_https","subelements"=>array(
    				"validate_curl"        => array("type"=>"ilCheckboxInputGUI","info"=>"info_validate_curl"))
        ),
        "path"                  	=> array("type"=>"ilTextInputGUI","info"=>"info_path","subelements"=>null),
        "defaulttext"               => array("type"=>"ilTextAreaInputGUI","info"=>"info_defaulttext","subelements"=>null),
        "old_group"       	        => array("type"=>"ilTextInputGUI","info"=>"info_old_group","subelements"=>null),

        "default_show_chat"         => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_chat","subelements"=>null),
        "conf_show_chat"            => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_chat","subelements"=>null),

        "default_line_numbers"      => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_line_numbers","subelements"=>null),
        "conf_line_numbers"         => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_line_numbers","subelements"=>null),

        "default_monospace_font"    => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_monospace_font","subelements"=>null),
        "conf_monospace_font"       => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_monospace_font","subelements"=>null),

        "default_show_colors"       => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_colors","subelements"=>null),
        "conf_show_colors"           => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_colors","subelements"=>null),

        "default_show_controls"     => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_controls","subelements"=>array(
                    "default_show_style"        => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_style"),
                    "default_show_list"         => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_list"),
                    "default_show_redo"         => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_redo"),
                    "default_show_coloring"     => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_coloring"),
                    "default_show_heading"      => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_heading"),
                    "default_show_import_export"=> array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_import_export"),
                    "default_show_timeline"     => array("type"=>"ilCheckboxInputGUI","info"=>"info_default_show_timeline"),
            ),
        ),

        "conf_show_controls"        => array("type"=>"ilCheckboxInputGUI","info"=>"conf_show_controls","subelements"=>array(
                "conf_show_style"               => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_style"),
                "conf_show_list"                => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_list"),
                "conf_show_redo"                => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_redo"),
                "conf_show_coloring"            => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_coloring"),
                "conf_show_heading"             => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_heading"),
                "conf_show_import_export"       => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_import_export"),
                "conf_show_timeline"            => array("type"=>"ilCheckboxInputGUI","info"=>"info_conf_show_timeline"),
            ),
        ),
    );

    /**
     * Handles all commmands, default is "configure"
     */
    function performCommand($cmd)
    {
        switch($cmd)
        {
            case "configure":
            case "save":
                $this->$cmd();
                break;

        }
    }

    /**
     * Configure screen
     */
    function configure()
    {
        global $tpl;

        $this->initConfigurationForm();
        $this->getValues();
        $tpl->setContent($this->form->getHTML());
    }


    public function getValues()
    {
        foreach($this->fields as $key => $item)
        {

            $values[$key] = $this->object->getValue($key);
            if(is_array($item["subelements"]))
            {
                foreach($item["subelements"] as $subkey => $subitem)
                {
                    $values[$key . "_" . $subkey] = $this->object->getValue($key . "_" . $subkey);
                }
            }

        }

        $this->form->setValuesByArray($values);
    }

    /**
     * Init configuration form.
     *
     * @return object form object
     */
    public function initConfigurationForm()
    {
        global $lng, $ilCtrl;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/classes/class.ilEtherpadLiteConfig.php");
        $this->object = new ilEtherpadLiteConfig();

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();


        foreach($this->fields as $key => $item)
        {
            $field = new $item["type"]($this->plugin_object->txt($key), $key);
            $field->setInfo($this->plugin_object->txt($item["info"]));
            if(is_array($item["subelements"]))
            {
                foreach($item["subelements"] as $subkey => $subitem)
                {
                    $subfield = new $subitem["type"]($this->plugin_object->txt($key . "_" . $subkey), $key . "_" . $subkey);
                    $subfield->setInfo($this->plugin_object->txt($subitem["info"]));
                    $field->addSubItem($subfield);
                }
            }

            $this->form->addItem($field);
        }

        $this->form->addCommandButton("save", $lng->txt("save"));

        $this->form->setTitle($this->plugin_object->txt("configuration"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        return $this->form;
    }


    /**
     * Save form input (currently does not save anything to db)
     *
     */
    public function save()
    {
        global $tpl, $ilCtrl;

        $this->initConfigurationForm();
        if($this->form->checkInput())
        {

            // Save Checkbox Values
            foreach($this->fields as $key => $item)
            {

                $this->object->setValue($key, $this->form->getInput($key));
                if(is_array($item["subelements"]))
                {
                    foreach($item["subelements"] as $subkey => $subitem)
                    {
                        $this->object->setValue($key . "_" . $subkey, $this->form->getInput($key . "_" . $subkey));
                    }
                }

            }

            $ilCtrl->redirect($this, "configure");
        }
        else
        {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }

}

?>
