#!/bin/bash

# Sets up the environment to run R on web.njit.edu. Based on the modulefile for
# R-Project/3.2.4
#
# Usage is: Runner.sh <script> <input> <output>
#
# <script> must be in the scripts directory
# <input> should be from the UPLOADS directory
# <output> will be placed in the local output directory

# Environment variables required for R
ROOT=/afs/cad/linux/R-3.2.4
PATH=$PATH:$ROOT/bin
MANPATH=$ROOT/share/man
LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/afs/cad/linux/gcc-4.9.2-sl6/lib64:/afs/cad/linux/mpfr-3.1.0/lib:/afs/cad/linux/curl-7.40.0/lib

# Local variables used
LOCAL_PATH="/afs/cad/u/r/x/rxt1077/public_html/Runner"

Rscript $LOCAL_PATH/scripts/$1 $2 $3
