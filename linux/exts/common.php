<?php
// common in-tree php extensions

$common_exts = [
    "ctype",
    "pdo",
    "fileinfo",
    "filter",
    "mbregex",
    "phar",
    "pcntl",
    "posix",
    "shmop",
    "session",
    "tokenizer",
    "sockets",
    "sysvmsg",
    "sysvsem",
    "sysvshm",
];
foreach ($common_exts as $name){
    $ext = new Ext($name);
    Ext::register($name, $ext);
    // enable by default
    Ext::add($name);
}
$ext = new Ext("mysqlnd");
$ext->lib("/lib/libz.a");
Ext::register("mysqlnd", $ext);
Ext::add("mysqlnd");

$with_exts = [
    "mysqli",
    "pdo-mysql",
];
foreach ($with_exts as $name){
    $ext = new Ext($name);
    $ext->opts = "--with-$name";
    Ext::register($name, $ext);
    Ext::add($name);
}


// enable zlib and bz2 by default
$ext = new Ext("zlib");
$ext->opts = "--with-zlib";
$ext->lib("/lib/libz.a");
Ext::register("zlib", $ext);
Ext::add("zlib");

$ext = new Ext("bz2");
$ext->opts = "--with-bz2";
$ext->lib("/usr/lib/libbz2.a");
Ext::register("bz2", $ext);
Ext::add("bz2");

$libxml_exts = [
    "soap" => "--enable-soap",
    "wddx" => "--enable-wddx",
    "dom" => "",     // extensions enabled by default with libxml
    "simplexml"=> "",// ^
    "xml"=> "",      // ^
    "xmlreader"=> "",// ^
    "xmlwriter"=> "",// ^
];

foreach ($with_exts as $name=>$opts){
    $ext = new Ext($name);
    $ext->req("libxml");
    $ext->opts = $opts;
    Ext::register($name, $ext);
}

$ext = new Ext("mbstring");
$ext->req("onig");
Ext::register("mbstring", $ext);

$ext = new Ext("curl");
$ext->req("curl");
Ext::register("curl", $ext);

$ext = new Ext("openssl");
$ext->opts = "--with-openssl";
$ext->req("libressl");
Ext::register("openssl", $ext);
