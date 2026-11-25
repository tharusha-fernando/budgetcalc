# Cash plan

Shown on the project view under **Cash plan**.

## Your mental model

```
Total covered = paid to guy + parts/labor spent + cash on hand + loan / cash to load
```

Compare that to the **agreed amount**:

- If covered **&lt;** agreed → **Need to save** (red)
- If covered **&gt;** agreed → **Surplus** (clear green)

## Fields

| Label | Meaning |
| --- | --- |
| Cash on hand | Money in your account now |
| Cash I will load | Loan / cash you will add |
| I already spent | Parts / labor you paid that are not returned |
| Paid to guy | Direct payments already made to the restoration guy |
| Total covered | Sum of the four pieces above |
| Need to save | Shown only when covered is short of agreed (red) |
| Surplus | Shown only when covered exceeds agreed (bold green) |

## Formulas

```
Total covered = paid to guy + I already spent + cash on hand + cash I will load
Need to save  = max(0, agreed − total covered)
Surplus       = max(0, total covered − agreed)
```

## Example (Tata Sumo)

| Piece | Amount |
| --- | ---: |
| Paid to guy | ₹560,000 |
| Parts spent | ₹160,160 |
| Cash on hand | ₹600,000 |
| Loan / to load | ₹1,300,000 |
| **Total covered** | **₹2,620,160** |
| Agreed | ₹2,400,000 |
| **Surplus** | **₹220,160** (green) |

No “need to save” in this case — you already cover more than the agreed budget.

## I already spent

Sum of bill item line totals that are:

- **Charge guy** (active) — paid out, not returned
- **I pay** (waived) — you cover it yourself

**Returned** items are not included (that money came back).
