class Main {
    public static void main(String args[]) {

        int port = 9090;

        System.out.printf("Running StockServer on port %d.\n", port);
        StockServer server = new StockServer(port);
        new Thread(server).start();
        try {
            Thread.sleep(24 * 60 * 60 * 1000);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
        System.out.println("Stopping server...");
        server.stop();
    }
}
