<?php
// common in-tree php extensions

$common_exts = [
    "ctype",
    "pdo",
    "fileinfo",
    "filter",
    "hash",
    "json",
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
    "mysqlnd",
];
foreach ($common_exts as $name){
    $ext = new Ext($name);
    Ext::register($name, $ext);
    // enable by default
    Ext::add($name);
}

$with_exts = [
    "zlib", // have dependencies, however it's must exist in alpine:edge
    "bz2", //  ^
    "mysqli",
    "pdo-mysql",
    "pdo-sqlite",
];
foreach ($with_exts as $name){
    $ext = new Ext($name);
    $ext->opts = "--with-$name";
    Ext::register($name, $ext);
}

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


