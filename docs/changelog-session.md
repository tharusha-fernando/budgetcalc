# What we built (session notes)

Summary of product work landed in commit `9c8ffec` (and the app foundation in `66050ca`).

## Cash plan fix

- **Problem:** Need to save ignored money already spent on parts/labor that wasn’t returned (or marked I pay).
- **Change:** `spentByMeTotal()` = charge guy + I pay; need to save = agreed − available cash − spent by me.
- **UI:** Project view shows **I already spent** next to Need to save.
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
