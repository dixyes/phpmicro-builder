# build zlib using cmake

# error out if any command failed
$erroractionpreference = "stop"

# zlib
cmake -DBUILD_SHARED_LIBS=OFF `
    -DSKIP_INSTALL_FILES=ON `
    -DCMAKE_C_FLAGS_MINSIZEREL="/MT /O1 /Ob1 /DNDEBUG" `
    -DCMAKE_INSTALL_PREFIX=${env:INSTDIR} `
    -b ${env:BUILDDIR} ${env:SRCDIR}

cmake --build ${env:BUILDDIR} --config MinSizeRel

cmake --install ${env:BUILDDIR} --config MinSizeRel

# post: lib/zlibstatic.lib->{zlib.lib, zlib_a.lib}
Copy-Item ${env:INSTDIR}\lib\zlibstatic.lib ${env:INSTDIR}\lib\zlib.lib
Copy-Item ${env:INSTDIR}\lib\zlibstatic.lib ${env:INSTDIR}\lib\zlib_a.lib
