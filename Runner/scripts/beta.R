# This script calculates the 3 year monthly beta for a list of stocks
# it is designed to get results as close to Yahoo Finance as possible:
# http://investexcel.net/how-does-yahoo-finance-calculate-beta/
#
# Usage is: Rscript beta.R <input> <output>

# Calculates the 36 month returns for a stock or index
calcReturns = function(historical, symbol) {
    data <- tail(historical$close[historical$symbol == symbol], 37)
    diff(data) / head(lag(data), -1)
}

# Calculates the beta for a stock
calcBeta = function(historical, bseReturns, nyseReturns, symbol) {
    # Calculate stock returns for the last 37 months
    stockReturns <- calcReturns(historical = historical, symbol = symbol)

    # Determine which market returns to use
    if (grepl(".NS$", toupper(symbol))) {
        marketReturns <- bseReturns
    } else {
        marketReturns <- nyseReturns
    }

    # Calculate the slope of the regression line for stock returns as a
    # function of market returns
    lm(stockReturns ~ marketReturns)$coefficients[2]
}

# Parse the arguments
args <- commandArgs(trailingOnly=TRUE)
if (length(args) != 2) {
    stop("Invalid amount of arguments. Usage is Rscript beta.R <input> <output>")
}
input <- args[1]
output <- args[2]

# Read the input
stocks <- read.csv(file = input)

# Read the historical data
historical <- read.csv(file = "../historical.csv")

# Calculate the index change each market
bseReturns <- calcReturns(historical, '^BSESN')
nyseReturns <- calcReturns(historical, '^GSPC')

# Calculate the beta for each stock in the input file and write the CSV file
outputData = data.frame(
    "symbol" = stocks$symbol,
    "beta" =  apply(stocks, 1,
         function(x) calcBeta(historical = historical, bseReturns = bseReturns,
            nyseReturns = nyseReturns, symbol = x[1]))
)
write.csv(outputData, file = output, row.names = FALSE)
