<?php
$xpdo_meta_map['PromoCode']= array (
  'package' => 'biomanager',
  'version' => '1.1',
  'table' => 'shop_promocodes',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name_en' => NULL,
    'name_ru' => NULL,
    'discount' => 5,
    'active' => 1,
    'activeto' => 0,
  ),
  'fieldMeta' => 
  array (
    'name_en' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '24',
      'phptype' => 'string',
      'null' => false,
    ),
    'name_ru' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '24',
      'phptype' => 'string',
      'null' => false,
    ),
    'discount' => 
    array (
      'dbtype' => 'int',
      'precision' => '3',
      'phptype' => 'integer',
      'null' => false,
      'default' => 5,
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
    'activeto' => 
    array (
      'dbtype' => 'int',
      'precision' => '20',
      'phptype' => 'timestamp',
      'null' => false,
      'default' => 0,
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
