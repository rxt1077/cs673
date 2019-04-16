# Solving the Investment Problem with Linear Programming in R

## Decision Variables
$$
A, D, E, M, O, S
$$
Represents the percentage of stock in Arihant Electricals, Dyn Pro Pvt Ltd, Eagle Enterprises, MicroModeling, OptiPro, and Sabre Systems respectively.

## Objective Function
$$
return = (0.0865)A+(0.095)D+(0.10)E+(0.087)M+(0.0925)O+(0.09)S
$$
The goal is to maximize the return.

## Constraints
$A \leq 25, D \leq 25, E \leq 25, M \leq 25, O \leq 25, S \leq 25$

$A + D + M \geq 50$

$D + E + O \leq 35$
 
$A + D + E + M + O + S = 100$

* No more than 25% should be in any one investment.
* At least 50% of the money should be in long-term investments ($A, D, M$).
* No more than 35% should be in high-risk investments ($D, E, O$).
* The sum of all the percentages should be 100.

## Non-negativity Restriction
$$
A \geq 0, D \geq 0, E \geq 0, M \geq 0, O \geq 0, S \geq 0
$$

You cannot purchase a negative amount of an investment. This is enforced by the linear programming solver.

## Solution in R
```R
library(lpSolve)

# obj:  (0.0865)A + (0.095)D + (0.10)E + (0.087)M + (0.0925)O + (0.09)S
# con1:      (1)A +     (0)D +    (0)E +     (0)M +      (0)O +    (0)S <= 25
# con2:      (0)A +     (1)D +    (0)E +     (0)M +      (0)O +    (0)S <= 25
# con3:      (0)A +     (0)D +    (1)E +     (0)M +      (0)O +    (0)S <= 25
# con4:      (0)A +     (0)D +    (0)E +     (1)M +      (0)O +    (0)S <= 25
# con5:      (0)A +     (0)D +    (0)E +     (0)M +      (1)O +    (0)S <= 25
# con6:      (0)A +     (0)D +    (0)E +     (0)M +      (0)O +    (1)S <= 25
# con7:      (1)A +     (1)D +    (0)E +     (1)M +      (0)O +    (0)S >= 50
# con8:      (0)A +     (1)D +    (1)E +     (0)M +      (1)O +    (0)S <= 35
# con9:      (1)A +     (1)D +    (1)E +     (1)M +      (1)O +    (1)S =  100

f.obj = c(0.0865, 0.095, 0.10, 0.087, 0.0925, 0.09)
f.con = rbind(
    c(1, 0, 0, 0, 0, 0),
    c(0, 1, 0, 0, 0, 0),
    c(0, 0, 1, 0, 0, 0),
    c(0, 0, 0, 1, 0, 0),
    c(0, 0, 0, 0, 1, 0),
    c(0, 0, 0, 0, 0, 1),
    c(1, 1, 0, 1, 0, 0),
    c(0, 1, 1, 0, 1, 0),
    c(1, 1, 1, 1, 1, 1))
f.dir = c("<=", "<=", "<=", "<=", "<=", "<=", ">=", "<=", "=")
f.rhs = c(25, 25, 25, 25, 25, 25, 50, 35, 100)
lp("max", f.obj, f.con, f.dir, f.rhs)$solution
```
Output:
```
[1] 15 10 25 25  0 25
```

## Conclusion
To maximize the return, Sayani Patel should invest:

Investment | Percentage | Amount
--- | --- | --- |
Arihant Electricals | 15 | $112,500
Dyn Pro Pvt Ltd | 10 | $75,000
Eagle Enterprises | 25 | $187,500
MicroModeling | 25 | $187,500
OptiPro | 0 | $0
Sabre Systems | 25 | $187,500

In the future it may be possible to improve the objective function by taking into account NPV.

## Resources Used
* [Introductory guide on Linear Programming for (aspiring) data scientists](https://www.analyticsvidhya.com/blog/2017/02/lintroductory-guide-on-linear-programming-explained-in-simple-english/)
* [Linear Programming with R](https://www.kaggle.com/arijit75/using-linear-programming-to-maximize-sales-profit)
* [Interface to 'Lp_solve' v. 5.5 to Solve Linear/Integer Programs](https://cran.r-project.org/web/packages/lpSolve/lpSolve.pdf)

 
 
