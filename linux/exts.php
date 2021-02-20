<?php
// extensions docker file generator

require_once("deps.php");
require_once("libs.php");

class Ext {
    // static exports
    static public $extstr = "";
    static public $prepext = "COPY patches /work/patches\nCOPY files /work/files\n";
    // static internals
    static private $known = [];
    static private $used = [];
    // static methods
    static public function make(){
        // register libs
        foreach(static::$used as $ext ){
            Lib::cpp($ext->use_cpp);
            foreach($ext->libs as $libname){
                Lib::use($libname);
            }
            // prove dep
            foreach($ext->reqs as $reqname){
                $dep = Dep::find($reqname);
                if(!$dep){
                    $extname = $ext->name;
                    throw new Exception("Not provided dep $reqname which used by ext $extname");
                }
            }
        }
        // make configure args
        static::$extstr = array_reduce(static::$used, function($now, $ext){
            return $now . $ext->opts . " \\\n        ";
        });
        // make ext source
        $dirs = [];
        $files = [];
        foreach(static::$used as $name=>$ext){
            if(NULL === $ext->srcfile){
                continue;
            }
            if(is_dir($ext->srcfile)){
                $dirs[$name] = $ext->srcfile;
            }else{
                $files[$name] = $ext->srcfile;
            }
        }
        foreach($dirs as $name=>$dir){
            static::$prepext .= "COPY $dir /work/php/ext/$name/\n";
        }
        if(count($files) > 0){
            $copies = "COPY ";
            $extracts = "RUN ";
            foreach($files as $name=>$file){
                $copies .= "$file ";
                $extracts .= "mkdir -p /work/php/ext/$name && \\\n" .
                "    tar -x --strip-components=1 -C /work/php/ext/$name -f /work/$file && \\\n" .
                "    rm /work/$file && \\\n    ";
            }
            $copies .= "/work/\n";
            $extracts .= ":\n\n";
            static::$prepext .= $copies;
            static::$prepext .= $extracts;
        }
        // prepare extension patches and files
        static::$prepext .= array_reduce(static::$used, function($now, $ext){
            if(count($ext->files) < 1 && count($ext->patches) <1){
                return $now;
            }
            $extname = $ext->name;
            $now .= "cd /work/php/ext/$extname && \\\n    ";
            foreach($ext->files as $name=>$dest){
                $now .= "cp /work/files/$name $dest && \\\n    ";
            }
            foreach($ext->patches as $patch){
                $now .= "patch -p1 < /work/patches/$patch && \\\n    ";
            }
            return $now;
        }, "RUN ") . ":\n";
    }
    
    static public function prepare(){
        static::$known = [];
        foreach(scandir(__DIR__ . "/exts") as $fn){
            if(is_file(__DIR__ . "/exts/" . $fn))
            require_once(__DIR__ . "/exts/" . $fn);
        }
    }
    static public function unuse(string $name){
        unset(static::$used[$name]);
    }
    static public function use(string $name, ?string $srcfile=NULL, array $options=[]){
        if(!array_key_exists($name, static::$known)){
            //todo: warn it, then use only enable options
            throw new Exception("Extension $name is not supported");
        }
        $ext = static::$known[$name];
        if(array_key_exists($name, static::$used)){
            if($srcfile !== $ext->srcfile){
                throw new Exception("Already claims ext $name with src file $srcfile");
            }
        }else{
            $ext->srcfile = $srcfile;
            static::$used[$name] = $ext;
        }
    }
    static public function used(string $name){
        @$ext = static::$used[$name];
        return $ext;
    }
    // instance vars
    private $srcfile = NULL;
    private $opts;
    private $name;
    private $use_cpp = false;
    private $reqs = [];
    private $files = [];
    private $patches = [];
    private $libs = [];
    // instance methods
    private function __construct(string $name){
        $this->name = $name;
        $this->opts = "--enable-$name=static";
    }
    private function register(){
        static::$known[$this->name] = $this;
    }
    // things for informations provide
    private function req(string $name){
        if(!array_key_exists($name, $this->reqs)){
            array_push($this->reqs, $name);
        }
    }
    private function patch(string $name){
        array_push($this->patches, $name);
    }
    private function file(string $name, string $dest){
        $this->files[$name] = $dest;
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


