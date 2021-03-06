<?php
require_once("argparse.php");
?>
# due to ubuntu bug https://bugs.launchpad.net/ubuntu/+source/libseccomp/+bug/1914939
# we're not using latest alpine yet
FROM alpine:3.12

<?php if(isset($defs['USE_MIRROR'])){ ?>
# for dbg
RUN echo -ne 'https://<?php echo $defs['USE_MIRROR']; ?>/alpine/edge/main\nhttps://<?php echo $defs['USE_MIRROR']; ?>/alpine/edge/community\n' > /etc/apk/repositories
<?php } ?>

# install dependencies
RUN apk add --no-cache vim alpine-sdk xz autoconf automake bison re2c \
    linux-headers clang-dev clang lld cmake zlib-dev zlib-static \
    bzip2-dev bzip2-static

# setup common environs
ENV CC=clang
ENV CXX=clang++
ENV LD=ld.lld

ENV CFLAGS='<?php if(!isset($defs["CFLAGS"])){ ?> -fno-ident -march=nehalem -mtune=haswell -Os <?php }else{ echo $defs["CFLAGS"]; }?>'
ENV CXXFLAGS='<?php if(!isset($defs["CXXFLAGS"])){ ?><?php }else{ echo $defs["CXXFLAGS"]; }?>'

# make dirs
RUN mkdir -p /usr/lib /usr/include && \
    ln -s /usr/lib /usr/lib64

# prepare lib dependencies
<?php
    echo Dep::$prepares;
    echo Dep::$buildstr;
?>


# prepare PHP codes
COPY <?php
if(isset($defs["PHP_SRC"])){
    echo $defs['PHP_SRC'];
}else{
    echo "php-src";
} ?> /work/php/

# prepare extension codes
<?php echo Ext::$prepext; ?>

# configure php
RUN cd /work/php && \
    ./buildconf --force && \
    ./configure \
        --disable-all \
        --disable-cgi \
        --disable-cli \
        --disable-phpdbg \
        --enable-micro \
        --without-pear \
        --disable-shared \
        --enable-static \
        <?php if(Dep::find("libxml")){ ?>\
        --disable-dom \
        --disable-simplexml \
        --disable-xml \
        --disable-xmlreader \
        --disable-xmlwriter \
        <?php } ?>\
        <?php echo Ext::$extstr ?> && \
    :

# build PHPmicro
RUN cd /work/php && \
    make \
        EXTRA_LIBS="<?php echo Lib::$libstr; ?>" \
        -j$(nproc) && \
    elfedit --output-osabi linux sapi/micro/micro.sfx
