# Run StockServer in a loop even after logout. It will restart every day

trap "" HUP
while [ 1 ]; do
    java Main >> StockServer.out 2>&1
done
