<?php

$param = isset($argv[1])?$argv[1]:'';

switch($param){
    case 'install':
        run();
        break;
    default:
        show();
        break;
}

function run(){
    require "config.php";
    require "zipcodetw.php";
    require "builder.php";
    echo 'Building ZIP code index ... '.PHP_EOL;
    $builder = new builder();
    $builder->build();
}

function show(){
    echo "usage: setup.php [cmd]".PHP_EOL;
    echo "   or: setup.php install".PHP_EOL;
}
