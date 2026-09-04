# storage/

Uploaded and generated files live here: `medical_records` attachments, the planned PDF lab
results, and X-ray/dental images. Borrowed from the Laravel `storage/` convention.

Rules:
- The **database stores a path/reference**, never the binary. The bytes live on disk here.
- This folder is **not web-reachable** (`.htaccess` denies direct access). Files are served to
  the browser through an authenticated PHP endpoint, so access respects login + RBAC:
  `backend/api/xray_image.php` does this for X-ray images.
- Real uploaded files stay out of git. `storage/xray/` and `storage/signatures/` are already
  in `.gitignore`; add a line for any new upload directory.
