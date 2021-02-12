<?php

$ext = new Ext("swoole");
$ext->lib("/usr/lib/libstdc++.a");
$ext->lib("/lib/libz.a");
$ext->req("nghttp2");
$ext->patch("swoole_nonghttp2.patch");
$ext->patch("swoole_config_h.patch");
$ext->patch("swoole_clang.patch");
$ext->patch("swoole_sapi_checks.patch");
$ext->file("swoole_for_conf.h", "php_swoole_for_conf.h");
Ext::register("swoole", $ext);

