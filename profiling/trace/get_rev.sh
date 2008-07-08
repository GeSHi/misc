#!/bin/bash
cd `dirname $0`

#folder=$(basename $(dirname `pwd`))

#if [[ "$folder" = "geshi" ]]; then
  # this is trunk
  rev="r"$(svn info ../geshi-trunk/geshi.php | grep "Revision" | egrep -o "[0-9]+")

  if [[ "$(svn status ../geshi-trunk/geshi.php)" != "" ]]; then
    rev=$rev"-patched"
  fi
#else
#  rev="v"$folder
#fi
echo $rev
