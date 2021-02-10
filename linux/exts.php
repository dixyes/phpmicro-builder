<?php
// extensions docker file generator

require_once("deps.php");

class Ext {
    static public $extstr = "";
    static public $prepext = "COPY patches /work/patches\nCOPY files /work/files\n";
    static public $libstr = "";
    static private $known = NULL;
    static private $exts = [];
    static public function make(){
        // make configure args
        static::$extstr = array_reduce(static::$exts, function($now, $ext){
            return $now . $ext->opts . " \\\n        ";
        });
        // make ext source
        $dirs = [];
        $files = [];
        foreach(static::$exts as $name=>$ext){
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
        static::$prepext .= array_reduce(static::$exts, function($now, $ext){
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
        // prepare libs
        $libs = [];
        $use_cpp = false;
        foreach(static::$exts as $ext){
            foreach($ext->libs as $lib){
                if(str_ends_with($lib, "libstdc++.a")){ // proud as PHP 8 user
                    $use_cpp = $lib;
                }else{
                    array_push($libs, $lib);
                }
            }
        };
        if($use_cpp){
            array_push($libs, $use_cpp);
        }
        if(0 < count($libs)){
            static::$libstr = implode(" ", $libs);
        }
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
    static public function del(string $name){
        unset(static::$exts[$name]);
    }
    static public function add(string $name, ?string $srcfile=NULL, array $options=[]){
        if(!array_key_exists($name, static::$known)){
            //todo: warn it, then use only enable options
            throw new Exception("Extension $name is not supported");
        }
        $ext = static::$known[$name];
        foreach($ext->reqs as $req){
            $dep = Dep::find($req);
            if(!$dep){
                $thisname = $ext->name;
                throw new Exception("Cannot satisfy deps: extension $thisname needs lib $req");
            }
            $ext->libs = array_merge($ext->libs, $dep->getlibs());
        }
        if(array_key_exists($name, static::$exts)){
            if($srcfile !== $ext->srcfile){
                throw new Exception("Already claims ext $name with src file $srcfile");
            }
        }else{
            $ext->srcfile = $srcfile;
            static::$exts[$name] = $ext;
        }
    }
    private $srcfile = NULL;
    private $opts;
    private $name;
    private $reqs = [];
    private $files = [];
    private $patches = [];
    private $libs = [];

    private function __construct(string $name){
        $this->name = $name;
        $this->opts = "--enable-$name=static";
    }
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
    private function lib(string $name){
        array_push($this->libs, $name);
    }
}


