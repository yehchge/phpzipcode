<?php

$start = getMicrotime();
require "../zipcodetw.php";
$end = getMicrotime();

echo 'The package took '.number_format(($end-$start),4)." seconds to load.";

function test_find(){
    zipcodetw::find('台北市');
    zipcodetw::find('台北市中正區');
    zipcodetw::find('台北市中正區仁愛路');
    zipcodetw::find('台北市中正區仁愛路2段');
    zipcodetw::find('台北市中正區仁愛路2段45號');

    zipcodetw::find('台中市');
    zipcodetw::find('台中市中區');
    zipcodetw::find('台中市中區台灣大道');
    zipcodetw::find('台中市中區台灣大道1段');
    zipcodetw::find('台中市中區台灣大道1段239號');

    zipcodetw::find('臺南市');
    zipcodetw::find('臺南市中西區');
    zipcodetw::find('臺南市中西區府前路');
    zipcodetw::find('臺南市中西區府前路1段');
    zipcodetw::find('臺南市中西區府前路1段226號');
}
ob_start();
$start = getMicrotime();
for($i=1;$i<=1000;$i++){
    test_find();
}
$end = getMicrotime();
ob_end_clean();
echo PHP_EOL;
echo "Timeit test_find with n=1000 took ".number_format(($end-$start),4)." seconds.".PHP_EOL;


function getMicrotime(){
    list( $usec, $sec ) = explode( ' ', microtime() );
    return ( (float)$usec + (float)$sec );
}
