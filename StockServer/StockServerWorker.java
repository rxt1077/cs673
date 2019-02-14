/* Java imports */
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.text.SimpleDateFormat;
import java.text.ParseException;
import java.util.Date;
import java.math.BigDecimal;
import java.net.Socket;

/* jsoup imports */
import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

/* A worker thread to fetch stock prices */
public class StockServerWorker implements Runnable {

        /* Dates in our protocol should be in ISO 8601 format */
        SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd");

        Socket clientSocket = null;

        public StockServerWorker(Socket clientSocket) {
            this.clientSocket = clientSocket;
        }

        public void run() {
            try {
                BufferedReader input = new BufferedReader(
                    new InputStreamReader(clientSocket.getInputStream()));
                System.out.println(input.readLine());
                input.close();
            } catch (IOException e) {
                e.printStackTrace();
            }
        }

        /* Returns the price in USD of a US stock */
        BigDecimal getUSPrice(String symbol, Date date) throws IOException, ParseException {

            /* URL for the HISTORY page on Yahoo finance. This shows one year
               of data as well as the current price */
            String url = String.format(
                "https://finance.yahoo.com/quote/%1$s/history?p=%1$s", symbol);

            /* Yahoo Finance uses this stype of date: Feb 14, 2019 */
            String dateString = String.format("%1$tb %1$td, %1$tY", date);

            /* Download and parse the URL */
            Document doc = Jsoup.connect(url).get();
           
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
                    return new BigDecimal(tds.eq(4).text());
                }
            }
            throw new ParseException("Unable to get stock price from HTML", 0);
        }
}
