<?php

// library EXTRA_LIBS string generator

class Lib{
    static private $known = [];
    static private $used = [];
    static public $libstr = "";
    static public function make(){
        static::$libstr = array_reduce(static::$used, function($a, $c){
            //echo "reduce $c\n";
            //echo "into $a\n";
            return "$a $c";
        }) . (static::$use_cpp ? " /usr/lib/libstdc++.a" : "");
    }
    static public function find(string $name): ?Lib{
        @$ret = static::$known[$name];
        return $ret;
    }
    static public function use(string $name){
        //echo "use $name\n";
        // if it not exist, build it with no dep.
        $lib = static::find($name) ?? new Lib($name);
        $lib->register();
        // make it into last position
        if(array_key_exists($name, static::$used)){
            unset(static::$used[$name]);
        }
        static::$used[$name] = $lib;
        // then add its deps recursively
        foreach($lib->deps as $dep){
            static::use($dep);
        }
    }
    static private $use_cpp = false;
    static public function cpp(bool $use_cpp){
        if($use_cpp){
            static::$use_cpp = true;
        }
    }
    private $name;
    private $deps;
    public function register(){
        if(array_key_exists($this->name, static::$used) && 0 < count($this->deps)){
            // if already registered this lib, we merge its deps with already exist one.
            static::$known[$this->name]->deps = array_merge(static::$known[$this->name]->deps, $this->deps);
            return;
        }
        static::$known[$this->name] = $this;
    }
    public function __construct(string $name, array $deps = []){
        $this->name = $name;
        $this->deps = $deps;
    }
    public function __toString(){
        return $this->name;
    }
    
}