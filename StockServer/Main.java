import java.text.SimpleDateFormat;
import java.util.Date;

class Main {
    private static String currentTime() {
        return new SimpleDateFormat("yyyy-MM-dd HH:mm:ss").format(new Date());
    }
    public static void main(String args[]) {
        int port = 9090;

        System.out.printf("%s - Running StockServer on port %d.\n", currentTime(), port);
        StockServer server = new StockServer(port);
        new Thread(server).start();
        try {
            Thread.sleep(24 * 60 * 60 * 1000);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
        System.out.printf("%s - Stopping server...", currentTime());
        server.stop();
    }
}
