<?php

// library dependencies dockerfile generator

class Dep{
    static public $prepares = "";
    static public $builds = "";
    //static public $libstr = "";
    static private $deps = [];
    static public function make(){
        // generate build part and prove dep
        foreach(static::$deps as $dep){
            $dep->gentext();
        }
        // generate code preparation part
        $dirs = [];
        $files = [];
        foreach(static::$deps as $name=>$dep){
            if(is_dir($dep->srcfile)){
                $dirs[$name] = $dep->srcfile;
            }else{
                $files[$name] = $dep->srcfile;
            }
        }
        foreach($dirs as $name=>$dir){
            static::$prepares .= "COPY $dir /work/$name\n";
        }
        if(count($files) > 0){
            $copies = "COPY ";
            $extracts = "RUN ";
            foreach($files as $name=>$file){
                $copies .= "$file ";
                $extracts .= "mkdir -p /work/$name && \\\n" .
                "    tar -x --strip-components=1 -C /work/$name -f /work/$file && \\\n" .
                "    rm /work/$file && \\\n    ";
            }
            $copies .= "/work/\n";
            $extracts .= ":\n\n";
            static::$prepares .= $copies;
            static::$prepares .= $extracts;
        }
        // make libs part
        /*
        static::$libstr = array_reduce(static::$deps, function($now, $dep){
            if(NULL === $now){
                return implode(" ", $dep->libs);
            }
            if(0 >= count($dep->libs)){
                return $now;
            }
            return $now . " " . implode(" ", $dep->libs);
        });
         */
    }
    static public function add(string $name, string $srcfile, array $options=[]){
        if(!array_key_exists($name, static::$deps)){
            static::$deps[$name] = new Dep($name, $srcfile, $options);
        } 
    }
    static public function find($name){
        if(isset(static::$deps[$name]))
            return static::$deps[$name];
    }
    private $name;
    private $srcfile;
    private $confargs;
    private $makeargs;
    private $builddir;
    private $libs = [];
    private $provides = [];
    private $shown = false;
    private function __construct(string $name, string $srcfile, array $options=[]){
        $this->name = $name;
        $this->srcfile = $srcfile;
        foreach(["confargs", "makeargs", "builddir"] as $opt){
            @$this->$opt = $options[$opt];
        }
    }
    private function gentext(){
        if($this->shown){
            return ;
        }
        ob_start();
        require("deps/" . $this->name . ".php");
        static::$builds .= ob_get_contents();
        ob_end_clean();
        $this->shown = true;
    }
    private function req(string $name){
        if(!array_key_exists($name, static::$deps)){
            $thisname = $this->name;
            throw new Exception("Cannot satisfy deps: lib $thisname needs $name\n");
        }
        static::$deps[$name]->gentext();
    }
    private function provides(string $name){
        // do nothing yet , WIP: make openssl/libressl chosable
        //array_push($this->provides, $name);
    }
    private function lib(string $name){
        array_push($this->libs, $name);
    }
    public function getlibs(){
        return $this->libs;
    }
}

