# Run StockServer in a loop. It will restart every day

cd ~/cs673/StockServer
while [ 1 ]; do
    java Main >> StockServer.out 2>&1
done
