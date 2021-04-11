# build zlib using cmake

param (
    [String]$InstDir = "..\deps",
    [String]$BuildDir = "build"
)

# prove builddir exist
New-Item "$BuildDir" -ItemType Container -Force | Out-Null

# error out if any command failed
$erroractionpreference = "stop"

# zlib
cmake `
    -G "Visual Studio 16 2019" `
    -DBUILD_SHARED_LIBS=OFF `
    -DSKIP_INSTALL_FILES=ON `
    '-DCMAKE_C_FLAGS_MINSIZEREL="/MT /O1 /Ob1 /DNDEBUG"' `
    -DCMAKE_INSTALL_PREFIX="$InstDir" `
    -B "$BuildDir"
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

cmake --build "$BuildDir" --config MinSizeRel --target install -j "${env:NUMBER_OF_PROCESSORS}"
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

# post: lib/zlibstatic.lib->{zlib.lib, zlib_a.lib}
Copy-Item $InstDir\lib\zlibstatic.lib $InstDir\lib\zlib.lib
Copy-Item $InstDir\lib\zlibstatic.lib $InstDir\lib\zlib_a.lib
