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
