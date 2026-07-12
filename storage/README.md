# storage/

Uploaded and generated files live here — `medical_records` attachments, the planned **PDF lab
results** (FR-16), and X-ray/dental images. Borrowed from the Laravel `storage/` convention
(see [docs/architecture.md](../docs/architecture.md)).

Rules:
- The **database stores a path/reference**, never the binary. The bytes live on disk here.
- This folder is **not web-reachable** (`.htaccess` denies direct access). Files are served to
  the browser through an **authenticated PHP download endpoint** (to be added in `backend/`),
  so access respects login + RBAC.
- Keep real uploaded files out of git (add patterns to `.gitignore` when the upload feature
  lands).
