<?php

error_reporting(E_ALL);

$opts = array(
    'm_id' => '1234567890AB',
    'm_key' => 'BA0987654321',
);

require 'sage_pay.php';

echo "Creating gateway...\n";
$sage = new SagePayment($opts);

echo "Verifying EFT service... ";
$ok = $sage->EftVerifyService();
if ($ok) {
    echo "Service Verified.\n";
} else {
    echo "Houston, we have a problem.\n";
}

echo "Verifying Vault service... ";
$ok = $sage->VaultVerifyService();
if ($ok) {
    echo "Service Verified.\n";
} else {
    echo "Houston, we have a problem.\n";
}

echo "Testing VaultInsertData... ";
$ok = $sage->VaultInsertCreditCardData('4111111111111111', '01', '20');
if ($ok) {
    $guid = $ok['GUID'];
    print_r($ok);
} else {
    echo "Houston, we have a problem.\n";
}

echo "Testing VaultGetCreditCardData... ";
$ok = $sage->VaultGetCreditCardData($guid);
if ($ok) {
    echo "{$ok}\n";
} else {
    echo "Houston, we have a problem.\n";
}

echo "Testing VaultBankcardSale... ";
$vars = array(
    'first_name' => 'John',
    'last_name' => 'Doe',
    'address1' => '1060 W. Addison',
    'address2' => '',
    'city' => 'Chicago',
    'state' => 'IL',
    'zip' => '60613',
    'country' => 'USA',
    'email' => 'john.doe@cubs.com',
    'charge_card_alias' => $guid,
    'customernum' => 'customer1',
    'amount' => '1000',
    'shipping' => '100',
    'tax' => '0',
    'ordernum' => '1',
    'phone' => '216-555-1212',
    'fax' => '216-555-1212',
);
$ok = $sage->VaultBankcardSale($vars);
if ($ok) {
    print_r($ok);
} else {
    echo "Houston, we have a problem.\n";
}

echo "Testing EftBankcardSale... ";
$vars = array(
    'first_name' => 'John',
    'last_name' => 'Doe',
    'address1' => '1060 W. Addison',
    'address2' => '',
    'city' => 'Chicago',
    'state' => 'IL',
    'zip' => '60613',
    'country' => 'USA',
    'email' => 'john.doe@cubs.com',
    'card_number' => '4111111111111111',
    'card_exp_month' => '12',
    'card_exp_year' => '20',
    'card_cvv' => '123',
    'customernum' => 'customer2',
    'amount' => '2000',
    'shipping' => '200',
    'tax' => '0',
    'ordernum' => '2',
    'phone' => '216-555-1212',
    'fax' => '216-555-1212',
);
$ok = $sage->EftBankcardSale($vars);
if ($ok) {
    print_r($ok);
} else {
    echo "Houston, we have a problem.\n";
}



//$xml = simplexml_load_string($sage->getResponse());
//if ($xml !== FALSE) {
    //$xml = $xml->children('diffgr', TRUE)->children();
    //print_r($xml->NewDataSet->Table1->SERVICE_INDICATOR);
    //echo $xml->NewDataSet->Table1->SERVICE_INDICATOR;
    //$name = $xml->getName();
    //echo "We have an XML object: {$name}\n";
//} else {
    //echo "Houston, we have a problem.\n";
//}

?>
