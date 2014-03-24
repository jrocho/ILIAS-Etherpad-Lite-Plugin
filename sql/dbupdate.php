<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
        'is_online' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	),
	'epadl_id' => array(
		'type' => 'text',
		'length' => 128,
		'notnull' => false
	)
);

$ilDB->createTable("rep_robj_xpdl_data", $fields);
$ilDB->addPrimaryKey("rep_robj_xpdl_data", array("id"));
?>
<#2>
<?php
if(!$ilDB->tableColumnExists("rep_robj_xpdl_data", "show_controls"))
{
    $query = "ALTER TABLE  `rep_robj_xpdl_data` ADD  `show_controls` BOOLEAN NOT NULL DEFAULT TRUE";
    $res = $ilDB->query($query);
}

if(!$ilDB->tableColumnExists("rep_robj_xpdl_data", "show_lines"))
{
    $query = "ALTER TABLE  `rep_robj_xpdl_data` ADD  `show_lines` BOOLEAN NOT NULL DEFAULT TRUE";
    $res = $ilDB->query($query);
}

if(!$ilDB->tableColumnExists("rep_robj_xpdl_data", "use_color"))
{
    $query = "ALTER TABLE  `rep_robj_xpdl_data` ADD  `use_color` BOOLEAN NOT NULL DEFAULT TRUE";
    $res = $ilDB->query($query);
}

if(!$ilDB->tableColumnExists("rep_robj_xpdl_data", "show_chat"))
{
    $query = "ALTER TABLE  `rep_robj_xpdl_data` ADD  `show_chat` BOOLEAN NOT NULL DEFAULT TRUE";
    $res = $ilDB->query($query);
}

?>
<#3>
<?php
if($ilDB->tableColumnExists("rep_robj_xpdl_data", "show_lines"))
{
    $query = "ALTER TABLE `rep_robj_xpdl_data` CHANGE `show_lines` `line_numbers` TINYINT( 1 ) NOT NULL DEFAULT '1'";
    $res = $ilDB->query($query);
}
if($ilDB->tableColumnExists("rep_robj_xpdl_data", "use_color"))
{
	$query = "ALTER TABLE `rep_robj_xpdl_data` CHANGE `use_color` `show_colors` TINYINT( 1 ) NOT NULL DEFAULT '1'";
	$res = $ilDB->query($query);
}
?>
<#4>
<?php
    $fields = array(
    'epkey' => array(
    'type' => 'text',
    'length' => 128,
    'notnull' => true
    ),
    'epvalue' => array(
        'type' => 'clob',
        'notnull' => false
    ),
    );

    $ilDB->createTable("rep_robj_xpdl_adm_set", $fields);
    $ilDB->addPrimaryKey("rep_robj_xpdl_adm_set", array("epkey"));
?>
<#5>
<?php
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_chat'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_chat",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','monospace_font'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","monospace_font",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_controls'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_controls",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_style'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_style",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_list'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_list",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_redo'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_redo",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_coloring'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_coloring",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_heading'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_heading",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_import_export'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_import_export",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','show_timeline'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","show_timeline",array("type"=>"boolean"));
    }
    if(!$ilDB->tableColumnExists('rep_robj_xpdl_data','old_pad'))
	{
        $ilDB->addTableColumn("rep_robj_xpdl_data","old_pad",array("type"=>"boolean"));
    }
?>
<#6>
<?php
// import old configuration file if it exists
$file = "./Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/etherpadlite.ini.php";
if(file_exists($file))
{
	$ini = new ilIniFile($file);
	$ini->read();
	
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'host','".$ini->readVariable("etherpadlite", "host")."' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'host');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'port','".$ini->readVariable("etherpadlite", "port")."' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'port');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'apikey','".$ini->readVariable("etherpadlite", "apikey")."' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'apikey');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'domain','".$ini->readVariable("etherpadlite", "domain")."' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'domain');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'https','".$ini->readVariable("etherpadlite", "https")."' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'https');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'defaulttext','".$ini->readVariable("etherpadlite", "defaulttext")."' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'defaulttext');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'old_group','".$ini->readVariable("etherpadlite", "group")."' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'old_group');";
} else {
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'host','etherpad.ilias.local' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'host');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'port','9001' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'port');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'apikey','See in Apikey.txt' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'apikey');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'domain','.ilias.local' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'domain');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'https',false FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'https');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'defaulttext','Etherpad-Lite für Ilias' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'defaulttext');";
	$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'old_group',NULL FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'old_group');";
}
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_chat',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_chat');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_monospace_font',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_monospace_font');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_line_numbers',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_line_numbers');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_colors',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_colors');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_controls',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_controls');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_controls_default_show_style',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_controls_default_show_style');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_controls_default_show_list',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_controls_default_show_list');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_controls_default_show_redo',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_controls_default_show_redo');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_controls_default_show_heading',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_controls_default_show_heading');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_controls_default_show_import_export',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_controls_default_show_import_export');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_controls_default_show_timeline',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_controls_default_show_timeline');";
$sql[] = "INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'default_show_controls_default_show_coloring',true FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'default_show_controls_default_show_coloring');";

foreach($sql as $s)
{
    $ilDB->manipulate($s);
}
?>
<#7>
<?php
// set all existing pads to old_pad, activate all features
	
	if($ilDB->tableColumnExists('rep_robj_xpdl_data','old_pad'))
	{
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set old_pad = 1 where old_pad IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_chat = 1 where show_chat IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set line_numbers = 1 where line_numbers IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set monospace_font = 1 where monospace_font IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_colors = 1 where show_colors IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_controls = 1 where show_controls IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_style = 1 where show_style IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_list = 1 where show_list IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_redo = 1 where show_redo IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_coloring = 1 where show_coloring IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_heading = 1 where show_heading IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_import_export = 1 where show_import_export IS NULL";
		$sql7[] = "UPDATE `rep_robj_xpdl_data` set show_timeline = 1 where show_timeline IS NULL";
		
		foreach($sql7 as $s7)
		{
			$res = $ilDB->query($s7);
		}
	}
?>
<#8>

<#9>
<?php
	// tables which need to be updated
	$update_tables = array(
						'show_controls',
						'line_numbers',
						'show_colors',
						'show_chat');
						
	foreach($update_tables as $table)
	{
		$res = $ilDB->query('ALTER TABLE `rep_robj_xpdl_data` CHANGE `'.$table.'` `'.$table.'` TINYINT( 1 ) NULL DEFAULT NULL');		
	}
	
?>

<#10>
<?php
	$res = $ilDB->query("INSERT INTO `rep_robj_xpdl_adm_set` (epkey, epvalue) SELECT 'path',NULL FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM rep_robj_xpdl_adm_set WHERE epkey = 'path');");
?>
