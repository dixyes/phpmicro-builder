<?php
require_once("deps.php");
require_once("exts.php");
//Dep::add("onig", "onig-6.9.6.tar.gz");
//Dep::add("nghttp2", "nghttp2-1.42.0.tar.xz");

function usage(){
// todo: say usage here
}

$defs = [];
function parseargs($argv){
    global $defs;
    $depopts = [];
    $extopts = [];
    try {
        array_shift($argv);
        foreach($argv as $arg){
            if(strlen($arg)<1){
                throw new Exception("Bad arg $arg: too short");
            }
            $op = $arg[0];
            $insts = explode(",", substr($arg, 1));
            $scope = array_shift($insts);
            $options = [];
            $name = NULL;
            foreach($insts as $inst){
                $kv = explode("=", $inst);
                @$v = $kv[1];
                if(NULL !== $v){
                    $options[$kv[0]] = $v;
                }else{
                    if(NULL !== $name){
                        throw new Exception("Bad arg $arg: duplicate $scope name $name vs $kv[0]");
                    }
                    $name = $kv[0];
                }
            }
            switch($scope){
            case 'dep':
                @$srcfile = $options["srcfile"];
                if(NULL === $srcfile){
                    throw new Exception("Bad arg $arg: lack srcfile=");
                }
                array_push($depopts, [$op, $name, $srcfile, $options]);
                break;
            case 'ext':
                array_push($extopts, [$op, $name, $options]);
                break;
            case 'def':
                switch($op){
                    case "-":
                        foreach($options as $k=>$v){
                            unset($defs[$k]);
                        }
                        break;
                    case "+":
                        foreach($options as $k=>$v){
                            $defs[$k] = $v;
                        }
                        break;
                }
                foreach($options as $k=>$v){
                    define($k, $v);
                }
                break;
            }
        }
    }catch(Exception $e){
        usage();
        throw $e;
    }

    foreach($depopts as $depopt){
        if("+" == $depopt[0]){
            Dep::use($depopt[1], $depopt[2], $depopt[3]);
        }else{
            throw new Exception("Cannot remove deps");
        }
    }
    Dep::make();
    Ext::prepare();
    foreach($extopts as $extopt){
        if("+" == $extopt[0]){
            @$srcfile = $extopt[2]["srcfile"];
            Ext::use($extopt[1], $srcfile, $extopt[2]);
        }elseif("-" == $extopt[0]){
            Ext::unuse($extopt[1]);
        }
    }
    Ext::make();
    Lib::make();
}
parseargs($argv);
