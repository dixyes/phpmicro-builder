<?php

$ext = new Ext("swoole");
// TODO: allow post modify
$ext->opts .= " --enable-swoole-curl --enable-swoole-json";
$ext->cpp(true);
$ext->lib("/lib/libz.a");
$ext->req("nghttp2");
$ext->req("libressl");
$ext->patch("swoole_nonghttp2.patch");
$ext->patch("swoole_config_h.patch");
$ext->patch("swoole_clang.patch");
$ext->patch("swoole_sapi_checks.patch");
$ext->file("swoole_for_conf.h", "php_swoole_for_conf.h");
$ext->register();

