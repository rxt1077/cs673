import java.util.concurrent.ConcurrentHashMap;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.io.IOException;
import java.util.regex.Pattern;
import java.util.regex.Matcher;
import java.net.URL;
import java.util.logging.Level;
import java.util.logging.Logger;

// Retrieves prices and updates periodically as set in StockServer
class StockPrices implements Runnable {
    private ConcurrentHashMap<String, String> prices =
        new ConcurrentHashMap<String, String>();
    private final static Logger logger =  
        Logger.getLogger(Logger.GLOBAL_LOGGER_NAME); 

    public void run() {
        try {
            URL url;
            BufferedReader in;
            Pattern pattern;
            String currentLine, symbol, price;

            logger.log(Level.INFO, "Fetching latest stock prices...");

            // DOW 30
            // This can be done in one block on a single web page
            url = new URL("https://finance.yahoo.com/quote/%5EDJI/components/");
            in = new BufferedReader(new InputStreamReader(url.openStream()));
            pattern = Pattern.compile("<td class=\".*?Ta\\(start\\).*?><a .*?>(.*?)</a></td>.*?<td class=\".*?Pstart.*?>(.*?)</td>");
            while ((currentLine = in.readLine()) != null) {
                Matcher matcher = pattern.matcher(currentLine);
                while (matcher.find()) {
                    symbol = matcher.group(1);
                    price = matcher.group(2);
                    prices.put(symbol, "USD," + price); 
                }
            }
            in.close();

            // Nifty 50
            // This requries scraping 50 web pages
            String[] nifty50 = { "ADANIPORTS.NS", "ASIANPAINT.NS",
                "AXISBANK.NS", "BAJAJ-AUTO.NS", "BAJFINANCE.NS",
                "BAJAJFINSV.NS", "BHARTIARTL.NS", "INFRATEL.NS", "BPCL.NS",
                "CIPLA.NS", "COALINDIA.NS", "DRREDDY.NS", "EICHERMOT.NS",
                "GAIL.NS", "GRASIM.NS", "HCLTECH.NS", "HDFC.NS", "HDFCBANK.NS",
                "HEROMOTOCO.NS", "HINDALCO.NS", "HINDUNILVR.NS", "HINDPETRO.NS",
                "ICICIBANK.NS", "IBULHSGFIN.NS", "INDUSINDBK.NS", "INFY.NS",
                "IOC.NS", "ITC.NS", "JSWSTEEL.NS", "KOTAKBANK.NS", "LT.NS",
                "M&M.NS", "MARUTI.NS", "NTPC.NS", "ONGC.NS", "POWERGRID.NS",
                "RELIANCE.NS", "SBIN.NS", "SUNPHARMA.NS", "TCS.NS",
                "TATAMOTORS.NS", "TATASTEEL.NS", "TECHM.NS", "TITAN.NS",
                "ULTRACEMCO.NS", "UPL.NS", "VEDL.NS", "WIPRO.NS", "YESBANK.NS",
                "ZEEL.NS" };
            for (int i = 0; i < nifty50.length; i++) {
                try {
                    symbol = nifty50[i];
                    url = new URL("https://finance.yahoo.com/quote/" + symbol);
                    in = new BufferedReader(
                        new InputStreamReader(url.openStream()));
                    pattern = Pattern.compile("<span class=\"Trsdu\\(0\\.3s\\).*?>(.*?)</span>");
                    while ((currentLine = in.readLine()) != null) {
                        Matcher matcher = pattern.matcher(currentLine);
                        if (matcher.find()) {
                            price = matcher.group(1);
                            prices.put(symbol, "INR," + price.replace(",", ""));
                            continue;
                        }
                    }
                } catch ( IOException e ) {
                    // Occasionally we get HTTP 503 responses, wait a second
                    // and then move on to the next stock
                    logger.log(Level.INFO, "IOException in Nifty 50 sleeping for 1s...");
                    Thread.sleep(1000);
                }
            }
        } catch (InterruptedException | IOException e) {
            logger.log(Level.WARNING, e.getMessage(), e);
        }
    }

    public String get(String symbol) {
        return prices.get(symbol);
    }
}
