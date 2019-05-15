# This script creates an orders file that will balance a portfolio
# It uses lpSolve repeatedly with smaller and smaller thresholds to find a
# solution that creates a portfolio value closest to the original value. It
# WILL NOT sell all of a stock.
#
# Usage is: Rscript balance.R <input> <output>

library(lpSolve)

# Adds values to a portfolio dataframe
addValues <- function(stocks) {
    stocks$value <- stocks$shares * stocks$price
    stocks
}

# Calculates the totals for a portfolio dataframe
calcTotals <- function(stocks) {
    totals <- list(    
        "all" = sum(stocks$value),
        "nse" = sum(stocks$value[stocks$market == "NSE"]),
        "dow" = sum(stocks$value[stocks$market == "DOW"]),
        "nseRatio" = 0.00, 
        "dowRatio" = 0.00
    )
    totals$nseRatio <- totals$nse / totals$all
    totals$dowRatio <- totals$dow / totals$all
    totals
}

# Prints out the portfolio
printStocks <- function(stocks, totals, title) {
    print(title)
    print(sprintf("Total Value: $%0.2f", totals$all))
    print(sprintf("Ratios: %0.2f%% DOW and %0.2f%% NSE", totals$dowRatio, totals$nseRatio))
    print(stocks)
}

# Parse the arguments
args <- commandArgs(trailingOnly=TRUE)
if (length(args) != 2) {
    stop("Invalid amount of arguments. Usage is Rscript balance.R <input> <output>")
}
input <- args[1]
output <- args[2]

# Read the input
stocks <- read.csv(file=input)

# Determine the market for each stock
stocks$market <- ifelse(grepl(".NS$", toupper(stocks$symbol)), "NSE", "DOW")
if ((! "DOW" %in% stocks$market) | (! "NSE" %in% stocks$market)) {
    stop("Portfolio does not contain at least one stock from each market.")
}

# Add the values
stocks <- addValues(stocks)

# Calculate the totals
totals <- calcTotals(stocks)

printStocks(stocks, totals, "Input Portfolio")

# Balancing Variables
nse <- 0.30
dow <- 0.70
thresh <- 0.05 # The percentage rule is solved within +- this value

# Objective function

# Maximize the value of the portfolio
f.obj <- stocks$price

# Constraints

# the portfolio value cannot be greater than its current value
cons1 <- stocks$price

# make vectors of the NSE / DOW coefficients to meet percentages
cons2 <- stocks$price
cons2[stocks$market == "DOW"] <- 0 
cons3 <- stocks$price
cons3[stocks$market == "NSE"] <- 0

# keep at least ONE of each stock
# this tacks on an identity matrix to make sure each coefficient is > 0
size <- length(stocks$shares)
cons4 <- diag(size)

f.con <- rbind(cons1, cons2, cons2, cons3, cons3, cons4)
f.dir <- c("<=", ">", "<", ">", "<", rep(">=", size))

# Keep trying with smaller and smaller thresholds to find the best solution
previous <- NULL
while (thresh > 0.00) {
    print(sprintf("Trying to solve with a threshold of %0.2f", thresh))
    f.rhs <- c(
        totals$all,
        (nse - thresh) * totals$all,
        (nse + thresh) * totals$all,
        (dow - thresh) * totals$all,
        (dow + thresh) * totals$all,
        rep(1, size)
    )

    result <- lp("max", f.obj, f.con, f.dir, f.rhs, int.vec=1:size)
    if (result$status != 0) {
        break
    }
    previous <- result
    thresh <- thresh - 0.01 
}

if (is.null(previous)) {
    stop("Unable to balance portfolio.")
}

# Setup the new portfolio
newStocks <- stocks
newStocks$shares <- previous$solution
newStocks <- addValues(newStocks)
newTotals <- calcTotals(newStocks)

printStocks(newStocks, newTotals, "Output Portfolio")

# Create the order file
orders = data.frame(
    "difference" = newStocks$shares - stocks$shares,
    "action" = rep("NONE", size),
    "ticker" = newStocks$symbol,
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
