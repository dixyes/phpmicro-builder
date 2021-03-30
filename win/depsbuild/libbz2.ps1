# build libbz2 using cmake

# error out if any command failed
$erroractionpreference = "stop"

# build libbz2
nmake /f Makefile.msc CFLAGS="-DWIN32 -MT -Ox -D_FILE_OFFSET_BITS=64 -nologo" lib

# post copy bzlib.h into deps/include, libbz2.lib->libbz2_a.lib
Copy-Item libbz2.lib ${env:INSTDIR}\lib\libbz2_a.lib

# post src\liblzma\api\* to deps/include
Copy-Item bzlib.h ${env:INSTDIR}\include