import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.logging.Logger;
import java.util.logging.Level;

class Main {
    private final static Logger logger =  
                Logger.getLogger(Logger.GLOBAL_LOGGER_NAME);

    public static void main(String args[]) {
        int port = 9090;

        logger.log(Level.INFO, "Running StockServer on port " + port);         
        StockServer server = new StockServer(port);
        new Thread(server).start();
        try {
            Thread.sleep(7 * 24 * 60 * 60 * 1000);
        } catch (InterruptedException e) {
            logger.log(Level.INFO, e.getMessage(), e);
        }
        logger.log(Level.INFO, "Stopping server...");         
        server.stop();
    }
}
