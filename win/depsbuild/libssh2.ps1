# build libssh2 using cmake

# error out if any command failed
$erroractionpreference = "stop"

# should we use CRYPTO_BACKEND=openssl?
cmake -DBUILD_TESTING=OFF `
    -DBUILD_EXAMPLES=OFF `
    -DCRYPTO_BACKEND=WinCNG `
    -DCMAKE_C_FLAGS_MINSIZEREL="/MT /O1 /Ob1 /DNDEBUG" `
    -DCMAKE_INSTALL_PREFIX=${env:INSTDIR} `
    -b ${env:BUILDDIR} ${env:SRCDIR}

cmake --build ${env:BUILDDIR} --config MinSizeRel

cmake --install ${env:BUILDDIR} --config MinSizeRel

