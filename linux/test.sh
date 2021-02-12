#!/bin/sh

# test micro build, this file should run in docker like
# docker run -v `realpath linux/test.sh`:/work/test.sh -v `realpath linux/rmtests.txt`:/work/iwillrmthesetestsonthismachine -w /work/php dixyes/microbuilder /work/test.sh

[ "x${1}" = xINACTION ] && INACTION=1
output()
{
    if [ x$INACTION = x1 ]
    then
        echo "::set-output name=test-result::${1}"
    fi
}

# remove unused tests
while read fn
do
    if [ -z "${fn}" ] || [ "x${fn###}" != "x${fn}" ]; then continue; fi
    if [ "x${fn%%.phpt}" = "x${fn}" ]; then echo strange rm entry ${fn} >2; exit 1; fi
    rm $fn
done < /work/iwillrmthesetestsonthismachine # < strange name for blocking users' machines

make micro_test TESTS="--show-diff --set-timeout 30 --color sapi/micro/tests"
ret=$?
if [ x0 != x$ret ]
then
    echo "Test failed"
    output "failed"
else
    echo "Test ok"
    output "ok"
fi
