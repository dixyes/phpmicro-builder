# build libzip using cmake

param (
    [String]$InstDir = "..\deps",
    [String]$BuildDir = "build"
)

# prove builddir exist
New-Item "$BuildDir" -ItemType Container -Force | Out-Null

# error out if any command failed
$erroractionpreference = "stop"

cmake `
    -G "Visual Studio 16 2019" `
    -DENABLE_BZIP2=ON `
    -DENABLE_LZMA=ON `
    -DBUILD_SHARED_LIBS=OFF `
    -DBUILD_DOC=OFF `
    -DBUILD_EXAMPLES=OFF `
    -DBUILD_REGRESS=OFF `
    -DBUILD_TOOLS=OFF `
    '-DCMAKE_C_FLAGS_MINSIZEREL="/MT /O1 /Ob1 /DNDEBUG /DLZMA_API_STATIC"' `
    -DCMAKE_INSTALL_PREFIX="$InstDir" `
    -B "$BuildDir"
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

cmake --build "$BuildDir" --config MinSizeRel --target install -j "${env:NUMBER_OF_PROCESSORS}"
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

# post: lib/zip.lib ->libzip_a.lib for PHP
Copy-Item $InstDir\lib\zip.lib $InstDir\lib\libzip_a.lib
