<?php
// extensions docker file generator

require_once("deps.php");

class Ext {
    static public $extstr = "";
    static private $known = NULL;
    static private $exts = [];
    static public function make(){
        static::$extstr = array_reduce(static::$exts, function($now, $ext){
            return $now . $ext->opts . " \\\n        ";
        });
    }
    static public function register(string $name,Ext $ext){
        static::$known[$name] = $ext;
    }
    static public function prepare(){
        static::$known = [];
        foreach(scandir(__DIR__ . "/exts") as $fn){
            if(is_file(__DIR__ . "/exts/" . $fn))
            require_once(__DIR__ . "/exts/" . $fn);
        }
    }
    static public function add(string $name){
        if(!array_key_exists($name, static::$known)){
            //todo: warn it, then use only enable options
            throw new Exception("Extension $name is not supported");
        }
        $ext = static::$known[$name];
        foreach($ext->reqs as $req){
            if(!Dep::find($req)){
                $thisname = $ext->name;
                throw new Exception("Cannot satisfy deps: extension $thisname needs lib $req");
            }
        }
        if(!array_key_exists($name, static::$exts)){
            static::$exts[$name] = $ext;
        }
    }
    private $opts;
    private $name;
    private $reqs = [];
    private function __construct(string $name){
        $this->name = $name;
        $this->opts = "--enable=$name=static";
    }
    private function req(string $name){
        if(!array_key_exists($name, $this->reqs)){
            array_push($this->reqs, $name);
        }
    }
}


