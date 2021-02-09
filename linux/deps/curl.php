<?php 
    $this->req("nghttp2");
    $this->req("libssh2");
    $this->req("libressl");
?>
# curl / libcurl
RUN cd <?php if($this->builddir) { echo $this->builddir; } else { ?>/work/curl<?php } ?> && \
    ./configure <?php if($this->confargs) { echo $this->confargs; } else { ?> \
        --prefix=/usr \
        --enable-shared=no \
        --enable-static=yes \
        --with-nghttp2=/usr \
        --with-libssh2=/usr \
        --with-openssl=/usr <?php } ?> && \
    make <?php if($this->makeargs) { echo $this->makeargs; } else { ?>-j`nproc`<?php } ?> && \
    make install

<?php $this->lib("/usr/lib/libcurl.a");?>
