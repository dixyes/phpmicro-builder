<?php 
    $this->req("libressl");
?>
# libssh2
RUN cd <?php if($this->builddir) { echo $this->builddir; } else { ?>/work/libssh2<?php } ?> && \
    ./configure <?php if($this->confargs) { echo $this->confargs; } else { ?>--prefix=/usr --enable-shared=no --enable-static=yes --with-crypto=openssl<?php } ?> && \
    make <?php if($this->makeargs) { echo $this->makeargs; } else { ?>-j`nproc`<?php } ?> && \
    make install
<?php $this->lib("/usr/lib/libssh2.a", ["/usr/lib/libssl.a"]);?>

