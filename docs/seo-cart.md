# SEO and cart only

This branch starts at staging rollback `767392e`. It does not depend on PR #1 or
PR #3 and does not reintroduce catalog filters, subcategory routes, pagination,
shipping changes or order submission changes.

SEO provides page titles/descriptions, canonical URLs, social tags, safe JSON-LD
for Organization/Product/Breadcrumb, Arabic document language, product headings
and image attributes, a product sitemap and environment-aware robots rules.
Staging stays `noindex,nofollow` with X-Robots-Tag and disallowed robots crawling.
Production public pages can be indexed; cart/auth/private pages cannot.
Canonical tags consolidate `/home`, tracking URLs and existing category tabs.
Public URLs and the catalog's tabs are preserved.

The cart uses validated session quantities and integer-piastre per-unit pricing.
The offer remains a pound amount, capped at the price. Cart counts/totals are
server-derived, image selection uses product_image_id with stable fallbacks,
and writes reject mismatched lists or quantities outside 1–99 (zero removes an
item during bulk update). Requests return the accepted state. Browser code
prevents overlapping writes, discards stale responses, displays errors and
renders product names as text. Cart forms have the correct update action.

Validation uses `php artisan test` with forced SQLite :memory:, `npm test` with
DOM/request simulations, `php artisan view:cache`, `php artisan route:cache`,
`npm run build`, and Chromium with a separate local fixture database.
No order or messaging endpoints are exercised by browser checks.

Checkout templates, order/WhatsApp controller methods, Order model, catalog
controller/template, database migrations, Composer dependencies and deployment
configuration are unchanged from the rollback base. The shared cart snapshot is
still consumed by existing callers. Deployment remains test-only through the
existing guarded GitHub Actions → SSM flow.
