import java.util.concurrent.ExecutorService;
import java.util.concurrent.Future;
import java.util.concurrent.Executors;
import java.io.IOException;
import java.text.ParseException;
import java.util.Date;
import java.text.SimpleDateFormat;

/* jsoup imports */
import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

/* Class to Fetch Prices asynchronously */
class PriceFetcher {

        /* Maximum amount of connections to make to Yahoo Finance */
        ExecutorService executor = Executors.newFixedThreadPool(10);

        /* Parses an input line, gets the price of the stock, returns an output
           line */
        Future<String> getPrice(String inputLine) {
            /* Dates in our protocol should be in ISO 8601 format
               NOT THREADSAFE, hence it's declared in the method */
            SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd");

            return executor.submit(() -> {
                Document doc;
                String userInput[], symbol, url, dateString;
                Date date;

                /* Parse the input and make sure it's valid */
                userInput = inputLine.split(",");
                if (userInput.length != 2)
                    return "ERROR Invalid Input";
                symbol = userInput[0];
                try {
                    date = dateFormat.parse(userInput[1]);
                } catch (ParseException e)  {
                    return "ERROR Invalid Date (yyyy-MM-dd)";
                } 

                /* URL for the HISTORY page on Yahoo finance. This shows one year
                   of data as well as the current price */
                url = String.format(
                    "https://finance.yahoo.com/quote/%1$s/history?p=%1$s",
                    symbol);

                /* Yahoo Finance uses this stype of date: Feb 14, 2019 */
                dateString = String.format("%1$tb %1$td, %1$tY", date);

                /* Download and parse the URL */
                try {
                    doc = Jsoup.connect(url).get();
                } catch (IOException e) {
                    return "ERROR Unable to connect to Yahoo Finance";
                }
               
                /* Go through each row of the historical prices table */ 
                for (Element tr:doc.select("table[data-test='historical-prices'] tr")) {

                    /* Pull the data cells from the table */
                    Elements tds = tr.select("td");

                    /* Make sure the first column is our date and the row has at
                       least seven data cells. This rules out dividend rows. */
                    if ((tds.eq(0).text().equals(dateString)) &&
                        (tds.size() == 7)) {

                        /* The 5th column (index 4) is the price at close. Even
                           though it is called the price at close it is kept up to
                           date for the current day as the market is open */
                        return String.format("USD,%s", tds.eq(4).text());
                    }
                }
                return "ERROR Unable to parse response";
            });
        }
}
