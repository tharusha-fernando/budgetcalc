# Cash plan

Shown on the project view under **Cash plan**.

## Fields

| Label | Meaning |
| --- | --- |
| Cash on hand | Money you already have ready |
| Cash I will load | Money you plan to add |
| Available cash | `cash on hand + cash I will load` |
| I already spent | Money you already spent on unrecovered purchases (see below) |
| Need to save | How much more cash you still need to gather |

## I already spent

Sum of bill item line totals that are:

- **Charge guy** (active) — paid out, not returned, still charged to the guy, **and**
- **I pay** (waived) — paid out by you, not claimed from the guy

**Returned** items are **not** included (that money came back).

Implemented as `Project::spentByMeTotal()` = `chargeableTotal() + waivedTotal()`.

## Formulas

With an agreed amount set:

```
Need to save = max(0, agreed − available cash − I already spent)
Surplus     = max(0, available cash + I already spent − agreed)
```

If you already spent part of the job yourself (and it wasn’t returned), you don’t need to “save” that amount again — it’s deducted from need to save and shown as **I already spent**.

## Still owed to the guy

Separate from cash plan:

```
Still owed = max(0, agreed − sum of payments to guy)
```

Bill item statuses affect **what you claim**; payment records track **what you already paid him in cash**.
