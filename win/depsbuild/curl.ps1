# build curl using nmake

param (
    [String]$InstDir = "..\deps",
    [String]$BuildDir = "build"
)

# error out if any command failed
$erroractionpreference = "stop"

$InstDir = Resolve-Path $InstDir
New-Item $InstDir -ItemType Container -Force | Out-Null
New-Item $InstDir\lib -ItemType Container -Force | Out-Null

Set-Location winbuild 

if($null -Eq (Select-String -Path MakefileBuild.vc -Pattern 'staticlib: \$\(CURL_LIBCURL_LIBNAME\)')){
    Write-Output 'staticlib: $(CURL_LIBCURL_LIBNAME)' | Out-File -Encoding utf8 -Append -FilePath MakefileBuild.vc
    Write-Output ('    COPY /Y $(LIB_DIROBJ)\$(LIB_NAME_STATIC) $(DIRDIST)\lib\libcurl_a.lib') | Out-File -Encoding utf8 -Append -FilePath MakefileBuild.vc
    Write-Output ('    IF NOT EXIST $(DIRDIST)\include\curl MKDIR $(DIRDIST)\include\curl') | Out-File -Encoding utf8 -Append -FilePath MakefileBuild.vc
    Write-Output ('    COPY /Y ..\include\curl\*.h $(DIRDIST)\include\curl\') | Out-File -Encoding utf8 -Append -FilePath MakefileBuild.vc
}

nmake `
    /f Makefile.vc `
    mode=static `
    RTLIBCFG=static `
    WITH_NGHTTP2=static `
    WITH_ZLIB=static `
    WITH_SSH2=static `
    WITH_SSL=static `
    WITH_PREFIX="$InstDir" `
    SSL_PATH="$InstDir" `
    ENABLE_IPV6=yes `
    ENABLE_UNICODE=yes `
    ENABLE_OPENSSL_AUTO_LOAD_CONFIG=no `
    MAKE="nmake staticlib"
#    GEN_PDB=yes `

if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}

Set-Location ..

#Copy-Item 
if($LASTEXITCODE -Ne 0){
    exit $LASTEXITCODE
}
