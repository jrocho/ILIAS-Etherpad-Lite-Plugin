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