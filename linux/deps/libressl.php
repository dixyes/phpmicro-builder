# libressl
RUN cd <?php if($this->builddir) { echo $this->builddir; } else { ?>/work/libressl<?php } ?> && \
    ./configure <?php if($this->confargs) { echo $this->confargs; } else { ?>\
        --prefix=/usr \
        --enable-shared=no \
        --enable-static=yes \
        --disable-tests <?php } ?> && \
    make <?php if($this->makeargs) { echo $this->makeargs; } else { ?>-j`nproc`<?php } ?> && \
    make install

<?php
    $this->lib("/usr/lib/libssl.a");
    $this->lib("/usr/lib/libcrypto.a");
?>
