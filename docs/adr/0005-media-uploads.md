# 0005 — Uploads limited to re-encoded images and checked PDFs

Status: accepted (2026-10-25)

## Context

Uploads are the riskiest input of a content site: files can carry scripts (SVG, HTML), exploit image decoders, leak location data in photo metadata, or simply be too large for visitors' phones.

## Decision

- Accepted types are decided by the detected content type, never the file name: JPEG, PNG and WebP images, and PDF documents (whose signature is checked). SVG, GIF, HTML, Office files and anything else are refused.
- Images are checked against a pixel and edge limit before decoding, then decoded and re-encoded with GD. That removes metadata (including GPS position) and anything appended to the file. WebP versions of 480, 960 and 1600 pixels are generated for responsive `srcset` use.
- Files are stored through Laravel's storage abstraction (`MEDIA_DISK`) under generated names (`media/{year}/{month}/{ulid}.{ext}`); the original name is kept only as a label.
- Only users with the upload permission can upload; uploads are rate limited; every upload, edit and deletion is audited. Images carry alternative text per language; media still used by content cannot be deleted.

## Consequences

- Uploaded files cannot run code in visitors' browsers or on the server, and photos do not reveal where they were taken.
- Visitors download images sized for their screen.
- Animated GIFs and vector logos cannot be uploaded; the logo is a raster file chosen in Settings.
- Processing large photos takes memory; limits are in `config/wasmsoft.php`.
