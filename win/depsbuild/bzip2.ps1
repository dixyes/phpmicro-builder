# build bzip2 using cmake

param (
    [String]$InstDir = "..\deps"
)

# error out if any command failed
$erroractionpreference = "stop"

# build bzip2
# bzip2 is small, so we use /Ox
nmake /f Makefile.msc CFLAGS="/DWIN32 /MT /Ox /D_FILE_OFFSET_BITS=64 /nologo" lib
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

# post copy libbz2.lib->libbz2_a.lib for PHP
Copy-Item libbz2.lib $InstDir\lib\libbz2_a.lib
# post copy libbz2.lib->libbz2.lib for cmake
Copy-Item libbz2.lib $InstDir\lib\libbz2.lib

# post bzlib.h to deps/include
Copy-Item bzlib.h $InstDir\include
