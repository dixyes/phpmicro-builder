# build liressl using cmake

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
    -DENABLE_LIBRESSL_INSTALL=ON `
    -DUSE_STATIC_MSVC_RUNTIMES=ON `
    -DLIBRESSL_APPS=OFF `
    -DLIBRESSL_TESTS=OFF `
    -DCMAKE_INSTALL_PREFIX="$InstDir" `
    -B "$BuildDir"
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

cmake --build "$BuildDir" --config MinSizeRel --target install -j "${env:NUMBER_OF_PROCESSORS}"
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

# shim for windows libressl as openssl
$cryptover = (Get-Content -Path crypto\VERSION | Select-String -Pattern "^([^.]+):([^.]+):([^.-]+)$").Matches.Groups
$cryptolib = "crypto-" + ($cryptover[1].Value -As [String]) + ".lib"
Copy-Item $InstDir\lib\$cryptolib $InstDir\lib\libeay32.lib

$sslver = (Get-Content -Path ssl\VERSION | Select-String -Pattern "^([^.]+):([^.]+):([^.-]+)$").Matches.Groups
$ssllib = "ssl-" + ($sslver[1].Value -As [String]) + ".lib"
Copy-Item $InstDir\lib\$ssllib $InstDir\lib\ssleay32.lib
