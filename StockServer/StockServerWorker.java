/* Java imports */
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.io.PrintWriter;
import java.net.Socket;

/* A worker thread to deal with a client */
public class StockServerWorker implements Runnable {

        Socket clientSocket = null;
        StockPrices prices;

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

            System.out.println("Worker thread starting...");
            try {
                input = new BufferedReader(new InputStreamReader(
                    clientSocket.getInputStream()));
                output = new PrintWriter(clientSocket.getOutputStream(), true);
                while ((inputLine = input.readLine()) != null) {
                    System.out.printf("Input: %s\n", inputLine);

                    /* Parse the input and make sure it's valid */
                    /* The data is bogus, it's not really needed */
                    userInput = inputLine.split(",");
                    if (userInput.length != 2) {
                        outputLine = "ERROR Invalid Input";
                    } else {
                        outputLine = prices.get(userInput[0]);
                    }
                    System.out.printf("Output: %s\n", outputLine);
                    output.println(outputLine);
                }
                input.close();
                output.close();
            } catch (IOException e) {
                e.printStackTrace();
            }
            System.out.println("Worker thread exiting...");
        }
}
