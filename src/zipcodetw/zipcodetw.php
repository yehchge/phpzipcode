<?php

namespace zipcodetw;

include_once "config.php";

class zipcodetw {
    private function __construct(){
    }

    static public function find($addr_str){
        $dir_ = new CDirectory(_db_path, $keep_alive=True);
        return $dir_->find($addr_str);
    }
}

class Address {

    private $addr_str = '';
    public $tokens = array();

    const NO = 0;
    const SUBNO = 1;
    const NAME  = 2;
    const UNIT  = 3;

    // public static $tokens;
    public static $UNIT;

    private static $TOKEN_RE = "(?:(?P<no>\d+)(?P<subno>之\d+)?(?=[巷弄號樓]|$)|(?P<name>.+?))(?:(?P<unit>[縣市鄉鎮市區村里鄰路街段巷弄號樓])|(?=\d+(?:之\d+)?[巷弄號樓]|$))";

    private static $TO_REPLACE_RE = "[ 　,，台~-]|[０-９]|[一二三四五六七八九]?十?[一二三四五六七八九](?=[段路街巷弄號樓])";

    # the strs matched but not in here will be removed
    private static $TO_REPLACE_MAP = array(
        '　' => '',
        '，' => '',
        ',' => '',
        '-' => '之',
        '~' => '之',
        '台' => '臺',
        '１' => '1',
        '２' => '2',
        '３' => '3',
        '４' => '4',
        '５' => '5',
        '６' => '6',
        '７' => '7',
        '８' => '8',
        '９' => '9',
        '０' => '0',
        '一' => '1',
        '二' => '2',
        '三' => '3',
        '四' => '4',
        '五' => '5',
        '六' => '6',
        '七' => '7',
        '八' => '8',
        '九' => '9',
    );

    public function __construct($addr_str){
        $this->addr_str = trim($addr_str);
        $this->tokens = self::tokenize($this->addr_str);
    }

    public function __toString() {
        return self::unicodeDecode($this->addr_str);
    }

    public function pick_to_flat(){
        $result = '';
        $idxs = func_get_args();
        foreach($idxs as $idx){
            $result .= join('', $this->tokens[$idx]);
        }
        return $result;
    }

    public static function normalize($addr_str){
        preg_match_all("/".self::$TO_REPLACE_RE."/u", $addr_str, $match);

        foreach($match[0] as $value){
            $addr_str = str_replace($value, self::change_dec($value), $addr_str);
        }

        return $addr_str;
    }

    public static function tokenize($addr_str){
        if(!$addr_str) return array();
        $addr_str = self::normalize($addr_str);
        preg_match_all("/".self::$TOKEN_RE."/u", $addr_str, $match);

        $result = array();
        if (isset($match['no'][0]))
            array_push($result, array($match['no'][0],$match['subno'][0],$match['name'][0],$match['unit'][0]));
        if (isset($match['no'][1]))
            array_push($result, array($match['no'][1],$match['subno'][1],$match['name'][1],$match['unit'][1]));
        if (isset($match['no'][2]))
            array_push($result, array($match['no'][2],$match['subno'][2],$match['name'][2],$match['unit'][2]));
        if (isset($match['no'][3]))
            array_push($result, array($match['no'][3],$match['subno'][3],$match['name'][3],$match['unit'][3]));
        if (isset($match['no'][4]))
            array_push($result, array($match['no'][4],$match['subno'][4],$match['name'][4],$match['unit'][4]));
        if (isset($match['no'][5]))
            array_push($result, array($match['no'][5],$match['subno'][5],$match['name'][5],$match['unit'][5]));
        return $result;
    }

    public function parse($idx){
        try{
            if($idx<0)
                $idx = count($this->tokens)+$idx;

            $token = @$this->tokens[$idx];

            $no1 = isset($token[Address::NO])?(int)$token[Address::NO]:0;
            if($token[Address::SUBNO]){
                $aNo2 = preg_split('//u', $token[Address::SUBNO], null, PREG_SPLIT_NO_EMPTY);
                unset($aNo2[0]);
                $no2 = join('',$aNo2);
            }else{
                $no2 = isset($token[Address::SUBNO])?(int)$token[Address::SUBNO]:0;
            }

            return array($no1, $no2);
        }catch(Exception $e){
            return array(0,0);
        }
    }

    public function flat(){
        $sarg = func_get_args(); // 取得所有的參數
        $numargs = func_num_args(); // 取得所有參數的個數

        $arg1 = isset($sarg[0])?$sarg[0]:0;
        $arg2 = isset($sarg[1])?$sarg[1]:0;

        $result = '';
        if(!$numargs) $aOutput = array_slice($this->tokens, 0);
        else if($numargs==1) $aOutput = array_slice($this->tokens, 0, $arg1);
        else if($numargs==2) $aOutput = array_slice($this->tokens, $arg1, $arg2-$arg1);

        foreach($aOutput as $val){
            $result .= join('', $val);
        }
        return $result;
    }

    private static function change_dec($str){
        if(!$str) return '';
        $result = str_replace(array_keys(self::$TO_REPLACE_MAP), array_values(self::$TO_REPLACE_MAP), $str);
        if(!$result) return '';
        $aResult = preg_split('//u', $result, null, PREG_SPLIT_NO_EMPTY);
        if ($aResult[0]=='十') {
            if(isset($aResult[1]) && is_numeric($aResult[1])){
                $aResult[0] = 1;
            }else{
                $aResult[0] = 10;
            }
        }else{
            $aResult[1] = '';
            $aResult[0] = trim($aResult[0]);
        }
        return join('', $aResult);
    }

    public static function unicodeDecode($data){
        $rs = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
                    function ($match) {
                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    }, $data
              );
        return $rs;
    }

}

class Rule extends Address {

    private $rule_str = '';

    public $rule_tokens = array();

    private static $RULE_TOKEN_RE = "及以上附號|含附號以下|含附號全|含附號|以下|以上|附號全|[連至單雙全](?=[\d全]|$)";

    public function __construct($rule_str){
        $this->rule_str = $rule_str;
        list($this->rule_tokens, $addr_str) = self::part($rule_str);
        parent::__construct($addr_str);
    }

    public function __toString() {
        return self::unicodeDecode($this->rule_str);
    }

    public static function part($rule_str){
        $rule_str = Address::normalize($rule_str);

        $rule_tokens = array();

        $addr_str = preg_replace_callback(
            "/".self::$RULE_TOKEN_RE."/u",
            function($matches) use (&$rule_tokens) {
                foreach($matches as $token){
                    $retval = '';
                    if($token == '連'){
                        $token = '';
                    }elseif($token == '附號全'){
                        $retval = '號';
                    }
                    if($token){
                        array_push($rule_tokens,$token);
                    }
                    return $retval;
                }
            },
            $rule_str
        );

        return array($rule_tokens, $addr_str);
    }

    public function match(Address $addr){

        # except tokens reserved for rule token
        $my_last_pos = count($this->tokens)-1;

        if($this->rule_tokens AND !in_array('全', $this->rule_tokens)){
            $my_last_pos--;
        }

        if(in_array('至', $this->rule_tokens)){
            $my_last_pos--;
        }

        # tokens must be matched exactly
        if ($my_last_pos >= count($addr->tokens)){
            return false;
        }

        $i = $my_last_pos;
        while ($i >= 0){
            if($this->tokens[$i]!=$addr->tokens[$i])
                return false;
            $i--;
        }

        # check the rule tokens
        $his_no_pair = $addr->parse($my_last_pos+1);
        if ($this->rule_tokens and !array_diff($his_no_pair,array(0, 0)))
            return false;

        $my_no_pair = $this->parse(-1);
        $my_asst_no_pair = $this->parse(-2);

        foreach($this->rule_tokens as $rt){
            if(($rt=='單' AND !(($his_no_pair[0] & 1) == 1)) OR
               ($rt=='雙' AND !(($his_no_pair[0] & 1) == 0)) OR
               ($rt=='以上' AND !($his_no_pair >= $my_no_pair)) OR
               ($rt=='以下' AND !($his_no_pair <= $my_no_pair)) OR
               ($rt=='至' AND !($my_asst_no_pair <= $his_no_pair AND $his_no_pair <= $my_no_pair OR in_array('含附號全', $this->rule_tokens) AND $his_no_pair[0]==$my_no_pair[0])) OR
               ($rt=='含附號' AND !($his_no_pair[0] == $my_no_pair[0])) OR
               ($rt=='附號全' AND !($his_no_pair[0] == $my_no_pair[0] and $his_no_pair[1] > 0)) OR
               ($rt=='及以上附號' AND !($his_no_pair >= $my_no_pair)) OR
               ($rt=='含附號以下' AND !($his_no_pair <= $my_no_pair  or $his_no_pair[0] == $my_no_pair[0]))

            ){
                return false;
            }
        }
        return true;
    }

    public static function unicodeDecode($data){
        $rs = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
                    function ($match) {
                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    }, $data
              );
        return $rs;
    }

}

class CDirectory {

    var $db_path;
    var $keep_alive;
    var $conn = null;
    var $cur = null;

    public function __construct($db_path, $keep_alive=false){
        $this->db_path = $db_path;

        # It will always use a same connection if keep_alive is true.
        $this->keep_alive = $keep_alive;
        $this->conn = NULL;
        $this->cur = NULL;
    }

    static function get_common_part($str_a, $str_b){
        if($str_a=='none') return $str_b;
        if($str_b=='none') return $str_a;

        $k = 0; # for the case range is empty

        $a_str = preg_split('//', $str_a, -1, PREG_SPLIT_NO_EMPTY);
        $b_str = preg_split('//', $str_b, -1, PREG_SPLIT_NO_EMPTY);

        for($i=0;$i<min(count($a_str), count($b_str));$i++){
            if ($a_str[$i] != $b_str[$i]){
                break;
            }else{
                $k++;
            }
        }
        return substr($str_a, 0, $k);
    }

    public function create_tables(){
        $create_table1 = "CREATE TABLE IF NOT EXISTS precise (
                            addr_str TEXT,
                            rule_str TEXT,
                            zipcode TEXT,
                            PRIMARY KEY (addr_str, rule_str)
                        )";

        $create_table2 = "CREATE TABLE IF NOT EXISTS gradual (
                            addr_str TEXT PRIMARY KEY,
                            zipcode TEXT
                        )";

        $this->conn->exec($create_table1);
        $this->conn->exec($create_table2);
    }

    public function put_precise($addr_str, $rule_str, $zipcode){
        $this->cur = $this->conn->prepare("INSERT OR IGNORE INTO precise VALUES (?, ?, ?)");
        $this->cur->execute(array($addr_str, $rule_str, $zipcode));
        return $this->conn->lastInsertId();
    }

    public function put_gradual($addr_str, $zipcode){
        $this->cur = $this->conn->prepare("SELECT zipcode FROM gradual WHERE addr_str = ?");
        $this->cur->setFetchMode(\PDO::FETCH_ASSOC);
        $this->cur->execute(array($addr_str));

        $row = $this->cur->fetch();

        if(!$row){
            $stored_zipcode = 'none';
        }else{
            $stored_zipcode = $row['zipcode'];
        }

        $sZipCode = self::get_common_part($stored_zipcode, $zipcode);

        $this->cur = $this->conn->prepare("REPLACE INTO gradual values (?, ?)");
        $this->cur->execute(array($addr_str, $sZipCode));

        return $this->conn->lastInsertId();
    }

    public function put($head_addr_str, $tail_rule_str, $zipcode){
        $addr = new Address($head_addr_str);

        # (a, b, c)
        $this->put_precise(
            $addr->flat(),
            $head_addr_str.$tail_rule_str,
            $zipcode
        );

        # (a, b, c) -> (a,); (a, b); (a, b, c); (b,); (b, c); (c,)
        $len_tokens = count($addr->tokens);

        for($f=0;$f<$len_tokens;$f++){
            for($l=$f;$l<$len_tokens;$l++){
                $this->put_gradual(
                    $addr->flat($f, $l+1),
                    $zipcode
                );
            }
        }

        if ($len_tokens >= 3){

            # (a, b, c, d) -> (a, c)
            $this->put_gradual($addr->pick_to_flat(0, 2), $zipcode);
        }
    }

    private function within_a_transaction($method){
        try{
            if(!$this->conn){
                // connect
                $this->conn = new \PDO('sqlite:'.$this->db_path);

                // Set errormode to exceptions
                $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            $this->conn->exec('BEGIN');
            $retval = $method();
            $this->conn->exec('COMMIT');
            return $retval;
        }catch(\PDOException $e){
            $this->conn->exec('ROLLBACK');

            // Print PDOException message
            echo $e->getMessage();
        }
    }

    public function load_chp_csv($chp_csv_lines){
        $wrap_func = function() use ($chp_csv_lines){
            $this->create_tables();
            $chp_csv_lines = explode("\n", $chp_csv_lines);

            if(isset($chp_csv_lines[0])) unset($chp_csv_lines[0]);

            foreach($chp_csv_lines as $row){
                $aRow = explode(",", $row);
                $this->put(
                    trim(join('',array_slice($aRow,1,-1))),
                    trim(join('',array_slice($aRow,-1))),
                    trim($aRow[0])
                );
            }
        };
        return $this->within_a_transaction($wrap_func);
    }

    public function find($addr_str){
        $wrap_func = function() use ($addr_str){
            $addr = new Address($addr_str);

            $len_addr_tokens = count($addr->tokens);

            # avoid unnecessary iteration
            $start_len = $len_addr_tokens;
            while($start_len >=0){
                if($addr->parse($start_len-1) == array(0,0)){
                    break;
                }
                $start_len -= 1;
            }

            for($i=$start_len; $i>0; $i--){
                $addr_str = $addr->flat($i);
                $rzpairs = $this->get_rule_str_zipcode_pairs($addr_str);

                # for handling insignificant tokens and redundant unit
                if(
                    # It only runs once, and must be the first iteration.
                    $i == $start_len AND
                    $len_addr_tokens >=4 AND
                    in_array($addr->tokens[2][Address::UNIT],array('村','里')) AND
                    !$rzpairs
                ){
                    if($addr->tokens[3][Address::UNIT]== '鄰'){

                        # delete the insignificant token (whose unit is 鄰)
                        unset($addr->tokens[3]);
                        $len_addr_tokens -= 1;
                    }

                    if($len_addr_tokens>=4 AND $addr->tokens[3][Address::UNIT] == '號'){

                        # empty the redundant unit in the token
                        $addr->tokens[2] = array('','',$addr->tokens[2][Address::NAME], '');
                    }else{

                        # delete insignificant token (whose unit is 村 or 里)
                        unset($addr->tokens[2]);
                    }

                    $rzpairs = $this->get_rule_str_zipcode_pairs($addr->flat(3));
                }

                if ($rzpairs){
                    foreach($rzpairs as $row){
                        list($rule_str, $zipcode) = $row;
                        $rule = new Rule($rule_str);
                        if($rule->match($addr)){
                            return $zipcode;
                        }
                    }
                }

                $gzipcode = $this->get_gradual_zipcode($addr_str);
                if ($gzipcode){
                    return $gzipcode;
                }
            }

            return '';
        };
        return $this->within_a_transaction($wrap_func);
    }

    public function get_rule_str_zipcode_pairs($addr_str){
        $this->cur = $this->conn->prepare("SELECT rule_str, zipcode FROM precise WHERE addr_str = ?");
        $this->cur->execute(array($addr_str));
        return $this->cur->fetchAll();
    }

    public function get_gradual_zipcode($addr_str){
        $this->cur = $this->conn->prepare("SELECT zipcode FROM gradual WHERE addr_str = ?");
        $this->cur->setFetchMode(\PDO::FETCH_ASSOC);
        $this->cur->execute(array($addr_str));

        $row = $this->cur->fetch();
        return isset($row['zipcode'])?$row['zipcode']:'';
    }

}
