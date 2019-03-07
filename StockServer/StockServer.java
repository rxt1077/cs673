import java.net.ServerSocket;
import java.net.Socket;
import java.io.IOException;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.Executors;

public class StockServer implements Runnable {

    int serverPort;
    ServerSocket serverSocket = null;
    boolean isStopped = false;
    Thread runningThread = null;
    ScheduledExecutorService executor;
    StockPrices prices = new StockPrices();
    
    public StockServer(int port){
        this.serverPort = port;
        this.executor = Executors.newSingleThreadScheduledExecutor();
    }

    public void run(){
        synchronized(this){
            this.runningThread = Thread.currentThread();
            executor.scheduleAtFixedRate(prices, 0, 60, TimeUnit.SECONDS);
        }
        openServerSocket();
        while(! isStopped()){
            Socket clientSocket = null;
            try {
                clientSocket = this.serverSocket.accept();
            } catch (IOException e) {
                if(isStopped()) {
                    System.out.println("Server Stopped.") ;
                    return;
                }
                throw new RuntimeException(
                    "Error accepting client connection", e);
            }
            new Thread(new StockServerWorker(clientSocket, prices)).start();
        }
        System.out.println("Server Stopped.") ;
    }

    private synchronized boolean isStopped() {
        return this.isStopped;
    }

    public synchronized void stop() {
        this.isStopped = true;
        try {
            this.serverSocket.close();
        } catch (IOException e) {
            throw new RuntimeException("Error closing server", e);
        }
    }

    private void openServerSocket() {
        try {
            this.serverSocket = new ServerSocket(this.serverPort);
        } catch (IOException e) {
            throw new RuntimeException("Cannot open port", e);
        }
    }
};
