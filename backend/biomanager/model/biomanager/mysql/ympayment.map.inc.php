<?php
$xpdo_meta_map['YMPayment']= array (
  'package' => 'biomanager',
  'version' => '1.1',
  'table' => 'shop_ympayments',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'orderid' => 0,
    'paymentid' => '',
  ),
  'fieldMeta' => 
  array (
    'orderid' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'paymentid' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
  'indexes' => 
  array (
    'orderid' => 
    array (
      'alias' => 'orderid',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'orderid' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'paymentid' => 
    array (
      'alias' => 'paymentid',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'paymentid' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
