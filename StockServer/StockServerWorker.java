/* Java imports */
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.util.concurrent.Future;
import java.lang.InterruptedException;
import java.util.concurrent.ExecutionException;
import java.util.Date;
import java.util.List;
import java.util.ArrayList;
import java.math.BigDecimal;
import java.net.Socket;

/* A worker thread to fetch stock prices */
public class StockServerWorker implements Runnable {

        int poll_interval = 250; /* how long to wait between checking data */
        int timeout = 1000; /* how long to wait until closing connection */

        Socket clientSocket = null;

        public StockServerWorker(Socket clientSocket) {
            this.clientSocket = clientSocket;
        }

        /* Reads input from the socket, parses it, performs a price lookups and
           returns the results */
        public void run() {
            String line, symbol, userInput[];
            BufferedReader input;
            Date date;
            PriceFetcher fetcher = new PriceFetcher();
            List<Future> futures = new ArrayList<Future>();
            int inactive = 0;

            System.out.println("Worker thread starting...");
            try {
                input = new BufferedReader(new InputStreamReader(
                    clientSocket.getInputStream()));
                
                while (true) {
                    /* if we have input available read it */
                    if (input.ready()) {
                        inactive = 0;
                        line = input.readLine();
                        System.out.printf("Input: %s\n", line);
                        futures.add(fetcher.getPrice(line));
                    /* if we have tasks complete print them */
                    } else if ((futures.size() > 0) && futures.get(0).isDone()) {
                        inactive = 0;
                        System.out.printf("Output: %s\n", futures.get(0).get());
                        futures.remove(0);
                    /* if there are no waiting futures and we've timed out */
                    } else if ((futures.size() == 0) && (inactive >= timeout)) {
                        break; 
                    /* wait */
                    } else {
                        Thread.sleep(poll_interval);
                        inactive += poll_interval;
                    }
                }
                input.close();
            } catch (IOException | InterruptedException | ExecutionException e) {
                e.printStackTrace();
            }
            System.out.println("Worker thread exiting...");
        }
}
