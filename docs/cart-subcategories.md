# Cart, checkout and category fixes

The cart stores quantities in the visitor's session. `App\Services\Cart` builds
all cart responses and page totals from current products. Prices and pound
amount discounts round per unit to integer piastres before multiplication.
Missing products and malformed legacy session entries are skipped.

Cart mutations validate complete requests before writing, return the canonical
cart with a session version, and use Laravel session locks. The browser prevents
overlapping mutations and discards reads started before a newer mutation.
Checkout validates active shipping areas and derives customer identity from the
web session. WhatsApp only prepares a URL; the server does not send a message.

`/shop/{category_id}` retains main category links. The new
`/shop/subcategory/{subcategory_id}` scopes the same search and sorting controls
to one subcategory. Pagination has 12 products and preserves query filters.

## Validation

- `php artisan test`: real Laravel routes and order persistence, using the
  migrations with a forced SQLite `:memory:` database in `phpunit.xml`.
- `npm ci && npm test`: JavaScript requests, DOM safety, races and shipping math.
- `php artisan view:cache` and `php artisan route:cache`.
- `npm run build`.

Browser validation should use a local fixture database, never customer orders.
Check add/update/remove, zero quantities, empty checkout, shipping above EGP
1,000, subcategory isolation, retained pagination filters and mobile overflow.

## Test deployment

The existing `test` branch deploys through GitHub Actions and AWS SSM. The
workflow now runs a read-only guard before the server wrapper can reset the app.
The guard verifies the nginx test host and document root, absence of production
virtual hosts, repository, wrapper branch, staging URL, local database and local
mail transport. It checks the current environment and the `/vine-test/env`
candidate before installing it. No credentials are printed. A successful deploy
writes its commit to `/deployment.json` for HTTP verification.

PR #1 remains an open prerequisite; this change is based on its branch. Deploy
by integrating into `test`, retaining its existing infrastructure and runtime
changes. Do not merge or push to `master` as part of this review.
