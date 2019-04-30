# This script creates an orders file that will balance a portfolio

library(lpSolve)

args = commandArgs(trailingOnly=TRUE)
input = args[1]
output = args[2]

# Read the input
stocks = read.csv(file=input)

# Determine the market for each stock
stocks$market = ifelse(endsWith(toupper(stocks$symbol), ".NS"), "NSE", "DOW")
if ((! "DOW" %in% stocks$market) | (! "NSE" %in% stocks$market)) {
    stop("Portfolio does not contain at least one stock from each market.")
}

# Calculate the values
stocks$value = stocks$shares * stocks$price

# Calculate the total values and percentages
totalValue = sum(stocks$value)
nseTotalValue = sum(stocks$value[stocks$market == "NSE"])
dowTotalValue = sum(stocks$value[stocks$market == "DOW"])
nsePercentage = (nseTotalValue / totalValue) * 100
dowPercentage = (dowTotalValue / totalValue) * 100

print("Current Portfolio")
print(sprintf("Total Value: $%0.2f", totalValue))
print(sprintf("%0.2f%% DOW and %0.2f%% NSE", dowPercentage, nsePercentage))
print(stocks)

# Balancing Variables
nse = 0.30
dow = 0.70
thresh = 0.05 # The percentage rule is solved within +- this value

# NOT YET IMPLEMENTED
change = 0.10 # The factor by which the amount of stock shares can change

# Objective function

# Maximize the value of the portfolio
f.obj = stocks$price

# Constraints

# the portfolio value cannot be great than its current value
cons1 = stocks$price

# make vectors of the NSE / DOW coefficients to meet percentages
cons2 = stocks$price
cons2[stocks$market == "DOW"] = 0 
cons3 = stocks$price
cons3[stocks$market == "NSE"] = 0

# keep at least ONE of each stock
# this tacks on an identity matrix to make sure each coefficient is > 0
size = length(stocks$shares)
cons4 = diag(size)

f.con = rbind(cons1, cons2, cons2, cons3, cons3, cons4)
f.dir = c("<=", ">", "<", ">", "<", rep(">=", size))

# Keep trying with smaller and smaller thresholds to find the best solution
previous = NULL
while (thresh > 0.00) {
    print(sprintf("Trying to solve with a threshold of %0.2f", thresh))
    f.rhs = c(
        totalValue,
        (nse - thresh) * totalValue,
        (nse + thresh) * totalValue,
        (dow - thresh) * totalValue,
        (dow + thresh) * totalValue,
        rep(1, size)
    )

    result = lp("max", f.obj, f.con, f.dir, f.rhs, int.vec=1:size)
    if (result$status != 0) {
        break
    }
    previous = result
    thresh = thresh - 0.01 
}

if (is.null(previous)) {
    stop("Unable to balance portfolio.")
}

# Set the shares to the last solved coefficients
stocks$shares = previous$solution

# Recalculate the values
stocks$value = stocks$shares * stocks$price
totalValue = sum(stocks$value)
nseTotalValue = sum(stocks$value[stocks$market == "NSE"])
dowTotalValue = sum(stocks$value[stocks$market == "DOW"])
nsePercentage = (nseTotalValue / totalValue) * 100
dowPercentage = (dowTotalValue / totalValue) * 100

print("Balanced Portfolio")
print(sprintf("Total Value: $%0.2f", totalValue))
print(sprintf("%0.2f%% DOW and %0.2f%% NSE", dowPercentage, nsePercentage))
print(stocks)
