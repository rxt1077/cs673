import java.util.concurrent.ConcurrentHashMap;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.io.IOException;
import java.util.regex.Pattern;
import java.util.regex.Matcher;
import java.net.URL;

// Retrieves prices and updates periodically as set in StockServer
class StockPrices implements Runnable {
    ConcurrentHashMap<String, String> prices =
        new ConcurrentHashMap<String, String>();

    public void run() {
        try {
            URL url;
            BufferedReader in;
            Pattern pattern;
            String currentLine, symbol, price;

            System.out.println("Fetching latest stock prices...");

            // DOW 30
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
            // FIXME: Need to convert names to symbols and store in hash
            // FIXME: Need to confirm with professor that we can use this site
            url = new URL("https://economictimes.indiatimes.com/indices/nifty_50_companies");
            in = new BufferedReader(new InputStreamReader(url.openStream()));

            pattern = Pattern.compile("<p class=\"flt w120.*?><a .*?>(.*?)</a></p>.*?<span class=\"ltp\">(.*?)</span>");
            while ((currentLine = in.readLine()) != null) {
                Matcher matcher = pattern.matcher(currentLine);
                while (matcher.find()) {
                    symbol = matcher.group(1);
                    price = matcher.group(2);
                }
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public String get(String symbol) {
        return prices.get(symbol);
    }
}
