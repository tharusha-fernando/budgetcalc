# What we built (session notes)

Summary of product work landed in commit `9c8ffec` (and the app foundation in `66050ca`).

## Cash plan

- **Total covered** = paid to guy + parts spent + cash on hand + loan.
- **Need to save** (red) when covered &lt; agreed; **Surplus** (green) when covered &gt; agreed.
- **Tests:** `tests/Feature/ProjectCashPlanTest.php`

## Payment attachments + camera-friendly uploads

- Payments to guy can store multiple photos/PDFs (`attachments` JSON column).
- Shared `AttachmentsUpload` used for bills and payments; phone can Take Photo while still allowing multiple files.
- **Tests:** `tests/Feature/Models/RestorationPaymentTest.php`

## Confirm before status change

- **Mark returned** and **I pay** (project items, bill items, Clearance) show **Are you sure?** before applying.
- **Tests:** confirmation cases in `tests/Feature/BillItemTotalsTest.php`

## Earlier foundation (already in repo)

- Projects, bills, bill items, Clearance page
- Direct payments to guy + payment progress chart
- Bill attachments, budget overview cards, Filament theme
