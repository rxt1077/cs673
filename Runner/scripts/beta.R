# This script calculates the beta for a list of stocks
#
# Usage is: Rscript beta.R <input> <output>

# Calculate the percent change between rows in data
percentChange = function(data) {
    difference <- diff(data)
    previous <- head(lag(data), -1)
    difference / previous * 100
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

# Remove dates when both markets weren't open from the list
stocksPerDate <- aggregate(historical$symbol, by = list(historical$date), FUN = length)
invalidDates <- stocksPerDate$Group.1[stocksPerDate$x != 80]
historical <- historical[! historical$date %in% invalidDates, ]

# Calculate the daily percent change for the whole market
marketClose <- aggregate(historical$close, by = list(historical$date), FUN = sum)
marketChange <- percentChange(marketClose$x)

# Calculate the beta for each stock in the input file
for (stock in stocks$symbol) {
    stockChange <- percentChange(historical$close[historical$symbol == stock])
    print(stock)
    print(cov(stockChange, marketChange) / var(marketChange))
}
