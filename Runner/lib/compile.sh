#!/bin/bash

# Run this from web.njit.edu

# Environment variables required for R
ROOT=/afs/cad/linux/R-3.2.4
PATH=$PATH:$ROOT/bin
MANPATH=$ROOT/share/man
LD_LIBRARY_PATH=/afs/cad/linux/gcc-4.9.2-sl6/lib64:/afs/cad/linux/mpfr-3.1.0/lib:/afs/cad/linux/curl-7.40.0/lib:$LD_LIBRARY_PATH

R CMD INSTALL -l /afs/cad/u/r/x/rxt1077/public_html/UPLOADS lpSolve_5.6.13.tar.gz
