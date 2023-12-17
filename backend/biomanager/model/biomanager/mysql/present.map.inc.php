<?php
$xpdo_meta_map['Present']= array (
  'package' => 'biomanager',
  'version' => '1.1',
  'table' => 'shop_presents',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name' => NULL,
    'link' => NULL,
    'image' => NULL,
    'koleso' => 0,
    'active' => 1,
    'forceshow' => 1,
    'threshold1' => NULL,
    'threshold2' => NULL,
    'propability' => NULL,
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '24',
      'phptype' => 'string',
      'null' => false,
    ),
    'link' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
    ),
    'image' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
    ),
    'koleso' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 1,
    ),
    'forceshow' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 1,
    ),
    'threshold1' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
    ),
    'threshold2' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
    ),
    'propability' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
    ),
  ),
  'indexes' => 
  array (
    'id' => 
    array (
      'alias' => 'id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
