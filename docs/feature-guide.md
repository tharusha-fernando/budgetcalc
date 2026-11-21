# Feature guide

## Core ideas

- A **project** is one vehicle restoration job (name, vehicle, restoration guy, agreed amount, cash on hand / to load).
- A **bill** belongs to a project and can have multiple photo/PDF attachments.
- A **bill item** is a line (parts, labor, etc.) with a status that drives settlement:
  - **Charge guy** (`active`) — still owed / claimed from the restoration guy
  - **Returned** — money came back (store or guy); enter who returned it and amount
  - **I pay** (`waived`) — you cover it yourself; not claimed from the guy
- **Payments to guy** are direct cash/UPI/bank payments against the agreed amount (separate from bill items).

## Where to work in Filament

| Area | Use it for |
| --- | --- |
| **Projects** | Create/view project, cash plan, bills tab, items tab, payments to guy tab |
| **Bills** | Create/edit bills and line items; project budget cards on bill view |
| **Clearance** | Project-focused settlement screen (Returned / I pay / Charge / Delete) |

## Item status actions

On project items, bill items, and Clearance:

1. **Mark returned** → confirmation **Are you sure?** → form (who returned, amount, date)
2. **I pay** → confirmation **Are you sure?** → marks waived
3. **Charge guy** → confirmation → puts item back to active

Nothing changes until you confirm.

## Payments to guy

On the project **Payments to guy** tab:

- Record amount, method (Cash / UPI / Bank / Other), date, notes
- Attach multiple proof photos or PDFs
- Totals feed **paid to guy** and **still owed** vs agreed amount
- Payment progress chart on project view

## Clearance page

Pick a project, then work through items with quick actions and live totals (gross, returned, I pay, chargeable, paid to guy, still owed).
