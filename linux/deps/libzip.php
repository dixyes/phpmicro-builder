<?php
$this->req("xz");
$this->req("libressl");
?>
# libzip
# this include is for libressl
RUN echo "#include <limits.h>" >> /usr/include/openssl/rand.h && \
    cd <?php if($this->builddir) { echo $this->builddir; } else { ?>/work/libzip<?php } ?> &&\
    cmake \
<?php if($this->confargs) { echo $this->confargs; } else { ?>
        -DENABLE_BZIP2=ON \
        -DENABLE_LZMA=ON \
        -DBUILD_SHARED_LIBS=OFF \
        -DBUILD_DOC=OFF \
        -DBUILD_EXAMPLES=OFF \
        -DBUILD_REGRESS=OFF \
        -DBUILD_TOOLS=OFF \
        -DZLIB_LIBRARY=/usr/lib \
        -DZLIB_INCLUDE_DIR=/usr/include \
        -DCMAKE_INSTALL_PREFIX=/usr . <?php } ?>&&\
    make <?php if($this->makeargs) { echo $this->makeargs; } else { ?>-j`nproc`<?php } ?> &&\
    make install

<?php $this->lib("/usr/lib/libzip.a", ["/usr/lib/libssl.a", "/usr/lib/libbz2.a", "/lib/libz.a"]);?>
