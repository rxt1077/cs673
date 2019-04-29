args = commandArgs(trailingOnly=TRUE)
input = args[1]
output = args[2]

# Read the input, create the value column, and write the output
stocks = read.csv(file=input)
stocks$value = stocks$price * stocks$shares
write.csv(stocks, file=output)
