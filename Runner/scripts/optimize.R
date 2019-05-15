# This script optimizes a portfolio
#
# Usage is: Rscript optimize.R <input> <output> <max_beta>

library(lpSolve)

# Determine which market a symbol is in
whichMarket =  function(symbol) {
    if (grepl(".NS$", symbol)) {
        'BSE'
    } else {
        'NYSE'
    }
}

# Calculates the geometric mean of a vector
calcMean = function(x, na.rm=TRUE){
  exp(sum(log(x[x > 0]), na.rm=na.rm) / length(x))
}

# Calculates the 36 month returns for a stock or index
calcReturns = function(historical, symbol) {
    data <- tail(historical$close[historical$symbol == symbol], 37)
    diff(data) / head(data, 36)
}

# Calculates the beta for a stock
calcBeta = function(historical, marketReturns, symbol, stockReturns) {
    # Calculate the slope of the regression line for stock returns as a
    # function of market returns
    lm(stockReturns ~ marketReturns)$coefficients[2]
}

# Parse the arguments
args <- commandArgs(trailingOnly=TRUE)
if (length(args) != 2) {
    stop("Invalid amount of arguments. Usage is Rscript optimize.R <input> <output>")
}
input <- args[1]
output <- args[2]

# Read the input
stocks <- read.csv(file = input, stringsAsFactors=F)

# Read the historical data
historical <- read.csv(file = "../historical.csv")

# Calculate the index change each market
bseReturns <- calcReturns(historical, '^BSESN')
nyseReturns <- calcReturns(historical, '^GSPC')

# Calculate the returns, beta, and market for each stock in the portfolio
ledger <- NULL
for (i in 1:nrow(stocks)) {
    stock = stocks[i, ]
    symbol = toupper(stock$symbol)
    market = whichMarket(symbol)
    if (market == 'BSE') {
        marketReturns = bseReturns
    } else {
        marketReturns = nyseReturns
    }
    stockReturns = calcReturns(historical = historical, symbol = symbol)
    meanReturn = calcMean(stockReturns)
    beta = calcBeta(historical = historical, marketReturns = marketReturns,
        symbol = symbol, stockReturns = stockReturns)
    ledger <- rbind(ledger, data.frame(
        "symbol"         = symbol,
        "market"         = market,
        "startingShares" = stock$shares,
        "price"          = stock$price,
        "meanReturn"     = meanReturn,
        "beta"           = beta)
    )
}

# Our risk metric is a beta normalized to a number between 1 and 10
ledger$risk <- (ledger$beta - min(ledger$beta)) / (max(ledger$beta) - min(ledger$beta)) * 9 + 1

# Quality is returns / risk
ledger$quality <- ledger$meanReturn / ledger$risk / ledger$price

# Drop the automatically added rownames
rownames(ledger) <- c()

if ((! "NYSE" %in% ledger$market) | (! "BSE" %in% ledger$market)) {
    stop("Portfolio does not contain at least one stock from each market.")
}

value <- sum(ledger$price * ledger$startingShares)

# Objective Function: Maximize the quality of the portfolio
f.obj <- ledger$quality

# Constraints
# bse stocks must be > 25% and < 35%
cons1 <- ledger$price
cons1[ledger$market=='NYSE'] <- 0
# nyse stocks must be > 65% and < 75%
cons2 <- ledger$price
cons2[ledger$market=='BSE'] <- 0
# new portfolio value > 95% of starting value and <= starting_value
cons3 <- ledger$price
# keep at least ONE of each stock
# this tacks on an identity matrix to make sure each coefficient is > 0
size <- length(ledger$startingShares)
cons4 <- diag(size)

f.con <- rbind(cons1, cons1, cons2, cons2, cons3, cons3, cons4)
f.dir <- c(">", "<", ">", "<", ">", "<=", rep(">=", size))
f.rhs <- c(0.25*value, 0.35*value, 0.65*value, 0.75*value, 0.95*value, value, rep(1, size))

# Solve it
result <- lp("max", f.obj, f.con, f.dir, f.rhs, int.vec=1:size)

if (is.null(result)) {
    stop("Unable to optimize portfolio.")
}

# Print out the ledger used
ledger$endingShares <- result$solution
print(ledger)

# Create the order file
orders = data.frame(
    "difference" = ledger$endingShares - ledger$startingShares,
    "action" = rep("NONE", size),
    "ticker" = ledger$symbol,
    "nshares" = rep(0, size),
    stringsAsFactors = FALSE
)
orders$action[orders$difference > 0] <- "BUY" 
orders$action[orders$difference < 0] <- "SELL" 
orders$nshares <- abs(orders$difference)

# Drop the difference column
orders <- orders[c("action", "ticker", "nshares")]
# Drop the NONE rows and SELL before we BUY (so we have money)
orders <- rbind(orders[orders$action == "SELL", ],
    orders[orders$action == "BUY", ])
# Write the CSV
write.csv(orders, file = output, row.names = FALSE)
