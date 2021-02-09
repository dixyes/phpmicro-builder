<?php
require_once("deps.php");
require_once("exts.php");
/*
Dep::add("onig", "onig-1.2.3.tar.xz");
Dep::add("libzip", "libzip-1.2.3.tar.xz");
Dep::add("xz", "xz-1.2.3.tar.xz");
Dep::add("nghttp2", "xz-1.2.3.tar.xz");
Dep::add("curl", "xz-1.2.3.tar.xz");
Dep::add("libssh2", "xz-1.2.3.tar.xz");
Dep::add("libressl", "xz-1.2.3.tar.xz");
Dep::make();
*/

Ext::prepare();
Ext::make();
//define("CFLAGS", 1);
$php_dir = "php-src";
