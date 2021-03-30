# build libzip using cmake

# error out if any command failed
$erroractionpreference = "stop"

Set-Location ${env:BUILDDIR}

# liblzma
msbuild windows\vs2019\liblzma.vcxproj -t:Build -p:Configuration=ReleaseMT

# post windows\vs2019\ReleaseMT\x64\liblzma\liblzma.lib to lib/liblzma_a.lib
Copy-Item windows\vs2019\ReleaseMT\x64\liblzma\liblzma.lib ${env:INSTDIR}\lib\liblzma_a.lib
# post src\liblzma\api\* to deps/include
Copy-Item src\liblzma\api\* ${env:INSTDIR}\include
