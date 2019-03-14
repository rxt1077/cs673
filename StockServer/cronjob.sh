#!/bin/bash

cd ~/cs673/StockServer
kill `cat StockServer.pid`
nohup java Main > /dev/null 2>&1 &
echo $! > StockServer.pid
