# build libzip using cmake

param (
    [String]$InstDir = "..\deps"
)

# error out if any command failed
$erroractionpreference = "stop"

# liblzma
msbuild windows\vs2019\liblzma.vcxproj -t:Build -p:Configuration=ReleaseMT
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

# post windows\vs2019\ReleaseMT\x64\liblzma\liblzma.lib to lib/liblzma_a.lib for PHP
Copy-Item windows\vs2019\ReleaseMT\x64\liblzma\liblzma.lib $InstDir\lib\liblzma_a.lib
# post windows\vs2019\ReleaseMT\x64\liblzma\liblzma.lib to lib/liblzma.lib for cmake
Copy-Item windows\vs2019\ReleaseMT\x64\liblzma\liblzma.lib $InstDir\lib\liblzma.lib

# post src\liblzma\api\* to deps/include
Copy-Item "src\liblzma\api\*" $InstDir\include -Recurse -Force
