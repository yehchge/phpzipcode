<?php

require "src/zipcodetw/zipcodetw.php";

use zipcodetw\zipcodetw;

$fn = fopen('php://stdin', 'r');
echo "Please type your Address: ";

while($address = fread($fn, 2000)){
    $zipcodetw = zipcodetw::find($address);
    echo "Your 3+2 zipcode is: ".$zipcodetw.PHP_EOL;
    echo "Please type your Address: ";
}

fclose($fn);
