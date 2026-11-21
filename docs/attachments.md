# Attachments

Multiple file uploads are supported on:

- **Bills** (bill photos / scans / PDFs)
- **Payments to guy** (payment proof)

Shared components:

- `App\Filament\Forms\Components\AttachmentsUpload`
- `App\Filament\Infolists\Components\AttachmentsEntry`

Bill-specific wrappers still exist (`BillAttachmentsUpload` / `BillAttachmentsEntry`) and point at the shared components.

## Storage

| Context | Disk | Directory |
| --- | --- | --- |
| Bills | `public` | `bills/attachments` |
| Payments to guy | `public` | `payments/attachments` |

Paths are stored as a JSON array on the model (`attachments` cast to `array`).

Ensure `php artisan storage:link` has been run so public URLs work.

## Phone camera

Uploads accept images (including `image/*` / HEIC) and PDFs, and allow **multiple** files.

On a phone, tap the upload control → the system sheet usually offers **Take Photo** and gallery. We do **not** force the HTML `capture` attribute, because on iOS that breaks multi-select.

Helper text on the fields reminds you: browse → Take Photo (or gallery), add as many as you need.

## Viewing

- Forms: FilePond grid preview, open, download
- Infolists: image thumbnails with View / Download links
- Tables: **Files** column shows attachment count
