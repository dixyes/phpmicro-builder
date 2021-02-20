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
    $ext->register();
    // enable by default
    Ext::use($name);
}
$ext = new Ext("mysqlnd");
$ext->lib("/lib/libz.a");
$ext->register();
Ext::use("mysqlnd");

$with_exts = [
    "mysqli",
    "pdo-mysql",
];
foreach ($with_exts as $name){
    $ext = new Ext($name);
    $ext->opts = "--with-$name";
    $ext->register();
    Ext::use($name);
}


// enable zlib and bz2 by default
$ext = new Ext("zlib");
$ext->opts = "--with-zlib";
$ext->lib("/lib/libz.a");
$ext->register();
Ext::use("zlib");

$ext = new Ext("bz2");
$ext->opts = "--with-bz2";
$ext->lib("/usr/lib/libbz2.a");
$ext->register();
Ext::use("bz2");

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
    $ext->register();
}

$ext = new Ext("mbstring");
$ext->req("onig");
$ext->register();

$ext = new Ext("curl");
$ext->opts = "--with-curl";
$ext->req("curl");
$ext->register();

$ext = new Ext("openssl");
$ext->opts = "--with-openssl";
$ext->req("libressl");
$ext->register();
