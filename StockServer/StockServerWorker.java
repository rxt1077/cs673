/* Java imports */
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.io.PrintWriter;
import java.net.Socket;
import java.util.logging.Logger;
import java.util.logging.Level;

/* A worker thread to handle a client */
public class StockServerWorker implements Runnable {

        Socket clientSocket = null;
        StockPrices prices;
        private final static Logger logger =  
            Logger.getLogger(Logger.GLOBAL_LOGGER_NAME);

        public StockServerWorker(Socket clientSocket, StockPrices prices) {
            this.clientSocket = clientSocket;
            this.prices = prices;
        }

        /* Reads input from the socket, parses it, performs a price lookups and
           returns the results */
        public void run() {
            String inputLine, outputLine, userInput[];
            BufferedReader input;
            PrintWriter output;

            logger.log(Level.INFO, "Worker thread starting...");
            try {
                input = new BufferedReader(new InputStreamReader(
                    clientSocket.getInputStream()));
                output = new PrintWriter(clientSocket.getOutputStream(), true);
                while ((inputLine = input.readLine()) != null) {
                    logger.log(Level.INFO, "Input: " + inputLine);

                    /* Parse the input and make sure it's valid */
                    /* The data is bogus, it's not really needed */
                    userInput = inputLine.split(",");
                    if (userInput.length != 2) {
                        outputLine = "ERROR Invalid Input";
                    } else {
                        outputLine = prices.get(userInput[0]);
                    }
                    logger.log(Level.INFO, "Output: " + outputLine);
                    output.println(outputLine);
                    output.flush();
                }
                input.close();
                output.close();
            } catch (IOException e) {
                logger.log(Level.WARNING, e.getMessage(), e);
            }
            logger.log(Level.INFO, "Worker thread exiting...");
        }
}
