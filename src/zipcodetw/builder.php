<?php

class builder {

    function __construct(){
    }

    public function build($chp_csv_path='', $db_path=''){
        # use default path if either path is not given.
        if (!$chp_csv_path){
            $chp_csv_path = _chp_csv_path;
        }
        if (!$db_path){
            $db_path = _db_path;
        }

        # build the index
        $start = $this->getMicrotime();
        $file = file_get_contents($chp_csv_path);
        $dir_ = new \zipcodetw\CDirectory($db_path);
        $dir_->load_chp_csv($file);
        $end = $this->getMicrotime();
        echo 'The build index took '.number_format(($end-$start),4)." seconds.".PHP_EOL;
    }

    function getMicrotime(){
        list( $usec, $sec ) = explode( ' ', microtime() );
        return ( (float)$usec + (float)$sec );
    }

}
