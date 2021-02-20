<?php

// library dependencies dockerfile generator

class Dep{
    // static exports
    static public $prepares = "";
    static public $buildstr = "";
    // static internals
    static private $used = [];
    // static methods
    static public function make(){
        // prove dependencies
        foreach(static::$used as $item){
            $item->show();
            Lib::cpp($item->use_cpp);
        }
        // generate code preparation part
        $dirs = [];
        $files = [];
        foreach(static::$used as $name=>$dep){
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
    }
    static public function find($name): ?Dep{
        @$ret = static::$used[$name];
        return $ret;
    }
    static public function use(string $name, string $srcfile, array $options=[]){
        static::$used[$name] = new Dep($name, $srcfile, $options);
    }
    private $name;
    private $srcfile;
    private $use_cpp = false;
    private $confargs;
    private $makeargs;
    private $builddir;
    private $libs = [];
    private $provides = [];
    private $reqs = [];
    private $shown = false;
    private function __construct(string $name, string $srcfile, array $options=[]){
        $this->name = $name;
        $this->srcfile = $srcfile;
        foreach(["confargs", "makeargs", "builddir"] as $opt){
            @$this->$opt = $options[$opt];
        }
    }
    private function show(){
        //echo "show $this\n";
        // make this str first
        ob_start();
        require("deps/" . $this->name . ".php");
        $thisstr = ob_get_contents();
        ob_end_clean();
        // print all deps
        foreach($this->reqs as $reqname){
            //echo "req $reqname\n";
            $req = static::find($reqname);
            if(!$req){
                throw new Exception("Cannot satisfy deps: lib $this needs $reqname\n");
            }
            $req->show();
        }
        // print this
        if($this->shown){
            return;
        }
        static::$buildstr .= $thisstr;
        $this->shown = true;
        // use lib at last
        foreach($this->libs as $libname){
            Lib::use($libname);
        }
    }
    public function __toString(){
        return $this->name;
    }
    // internal methods for informations provide
    private function req(string $name){
        if(!array_key_exists($name, $this->reqs)){
            array_push($this->reqs, $name);
        }
    }
    private function provides(string $name){
        // do nothing yet , WIP: make openssl/libressl chosable
        //array_push($this->provides, $name);
    }
    private function lib(string $name, array $deps=[]){
        $lib = new Lib($name, $deps);
        $lib->register();
        array_push($this->libs, $name);
    }
    private function cpp(bool $use_cpp){
        $this->use_cpp = $use_cpp;
    }
}

