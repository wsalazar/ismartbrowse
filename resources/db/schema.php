<?php

/**
 * @author Саша Стаменковић <umpirsky@gmail.com>
 */

$schema = new \Doctrine\DBAL\Schema\Schema();

$gloves = $schema->createTable('ibrowsesmart_orders');
$gloves->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$gloves->addColumn('firstName', 'string', array('length' => 255));
$gloves->addColumn('lastName', 'string', array('length' => 255));
$gloves->addColumn('address1Billing', 'string', array('length' => 255));
$gloves->addColumn('address2Billing', 'string', array('length' => 255));
$gloves->addColumn('zipBilling', 'string', array('length' => 255));
$gloves->addColumn('cityBilling', 'string', array('length' => 255));
$gloves->addColumn('stateBilling', 'string', array('length' => 255));
$gloves->addColumn('countryBilling', 'string', array('length' => 255));

$gloves->addColumn('address1Shipping', 'string', array('length' => 255, 'notnull' => false));
$gloves->addColumn('address2Shipping', 'string', array('length' => 255, 'notnull' => false));
$gloves->addColumn('zipShipping', 'string', array('length' => 255, 'notnull' => false));
$gloves->addColumn('cityShipping', 'string', array('length' => 255, 'notnull' => false));
$gloves->addColumn('stateShipping', 'string', array('length' => 255, 'notnull' => false));
$gloves->addColumn('countryShipping', 'string', array('length' => 255, 'notnull' => false));

$gloves->addColumn('email', 'string', array('length' => 255));
$gloves->addColumn('gender', 'string', array('length' => 1));
$gloves->addColumn('quantity', 'integer', array('length' => 11));
$gloves->addColumn('paymentChoice', 'integer', array('length' => 2));
$gloves->addColumn('sameAsShipping', 'integer', array('length' => 2));
$gloves->addColumn('trackingNumber', 'string', array('length' => 255, 'notnull' => false));
$gloves->addColumn('shipped', 'boolean', array('notnull' => false));

$gloves->setPrimaryKey(array('id'));

return $schema;
