import java.util.Date;
import java.io.IOException;
import java.text.ParseException;

public class Test {
        public static void main(String[] args) throws IOException, ParseException {
            Fetcher fetcher = new Fetcher();
            Date date = fetcher.dateFormat.parse("2019-02-12");
            
            System.out.println(fetcher.getUSPrice("IBM", date));
        }
}
