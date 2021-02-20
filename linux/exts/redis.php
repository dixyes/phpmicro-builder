<?php

$ext = new Ext("redis");
//$ext->lib("/usr/lib/libstdc++.a");
// todo: igbinary,lzf,zstd,lz4,... things
//$ext->req("nghttp2");
$ext->register();
