<?php
require '../src/Components/PHP/ManagerJSON.php';

$testJSON = new ManagerJSON('../src/Jsons/Command.json');
//print_r($testJSON->read()); 
echo "TEST";

print_r($testJSON->GiveEveryKey());

echo '<br><br>';
echo "TEST 2 " ;
echo '<br><br>';

$testJSON->renameKey('ChangedValue', 'TestValue');

$testJSON->changeValueKEY('TestValue', ['category' => 'Tesytyyy', 'order' => 'Tyyy']);

