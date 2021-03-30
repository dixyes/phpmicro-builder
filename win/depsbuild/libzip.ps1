# build libzip using cmake

# error out if any command failed
$erroractionpreference = "stop"

cmake -DENABLE_BZIP2=ON `
    -DENABLE_LZMA=ON `
    -DBUILD_SHARED_LIBS=OFF `
    -DBUILD_DOC=OFF `
    -DBUILD_EXAMPLES=OFF `
    -DBUILD_REGRESS=OFF `
    -DBUILD_TOOLS=OFF `
    -DCMAKE_C_FLAGS_MINSIZEREL="/MT /O1 /Ob1 /DNDEBUG " `
    -DCMAKE_INSTALL_PREFIX=${env:INSTDIR} `
    -b ${env:BUILDDIR} ${env:SRCDIR}

cmake --build ${env:BUILDDIR} --config MinSizeRel

cmake --install ${env:BUILDDIR} --config MinSizeRel

# post: lib/zip.lib ->libzip_a.lib
Copy-Item ${env:INSTDIR}\lib\zip.lib ${env:INSTDIR}\lib\libzip_a.lib
