# StockServer

This is a multithreaded server for fetching stock prices. It maintains a hash
of the Dow 30 and Nifty 50 by periodically web-scraping finance sites.

## Requirements

**v1.0 3a**: Stock prices should be fetched by a Java component which will listen on a
specific TCPIP port and fetch the price of the stock from Yahoo or Google.  (We
will test this component separately.) This component will accept text input of
the form ‘TICKER,DATE’ and it will respond with the price as ‘USD,200.92’ or
‘INR,1822.92’ Your system must use this component to fetch all stock prices. Later
in the term you will be supplied with another team/s component which will do the
same thing.

## Errors

The following errors may be output by the server

* `ERROR Invalid Input` - This occurs if the submitted string cannot be
interpreted at all.
