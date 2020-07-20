#!/bin/bash

if [ -z $1 ];then
    str='太懒，未写备注！'
else
    str="$1"
fi

git add -A
git commit -m "$str"     # 赋值不用$符号，输出里要用$
git push

