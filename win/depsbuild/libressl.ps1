# build liressl using cmake

# error out if any command failed
$erroractionpreference = "stop"

cmake -DUSE_STATIC_MSVC_RUNTIMES=ON `
    -DLIBRESSL_APPS=OFF `
    -DCMAKE_INSTALL_PREFIX=${env:INSTDIR} `
    -b ${env:BUILDDIR} ${env:SRCDIR}

cmake --build ${env:BUILDDIR} --config MinSizeRel

cmake --install ${env:BUILDDIR} --config MinSizeRel
