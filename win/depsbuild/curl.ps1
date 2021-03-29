# build curl using cmake

# error out if any command failed
$erroractionpreference = "stop"

cmake -DENABLE_LIB_ONLY=ON `
    -DENABLE_STATIC_LIB=ON `
    -DENABLE_SHARED_LIB=OFF `
    -DENABLE_STATIC_CRT=ON `
    -DCMAKE_INSTALL_PREFIX=${env:INSTDIR} `
    -b ${env:BUILDDIR} ${env:SRCDIR}

cmake --build ${env:BUILDDIR} --config MinSizeRel

cmake --install ${env:BUILDDIR} --config MinSizeRel
