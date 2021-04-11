# build curl using cmake

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
    -DENABLE_LIB_ONLY=ON `
    -DENABLE_STATIC_LIB=ON `
    -DENABLE_SHARED_LIB=OFF `
    -DENABLE_STATIC_CRT=ON `
    -DCMAKE_INSTALL_PREFIX="$InstDir" `
    -B "$BuildDir"
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

cmake --build "$BuildDir" --config MinSizeRel --target install -j "${env:NUMBER_OF_PROCESSORS}"
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}
