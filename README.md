# Kata09: Back to the Checkout

The kata isn't really about the math. Its actual objective is decoupling: the checkout should never know which items exist or how each one is priced, and it should be possible to add a brand new pricing style without touching the checkout itself. Everything below is built around that.

## How it works
$ scan A, A, A
$ total -> 130

`CheckoutService::scan($sku)` tallies scanned SKUs. No pricing math happens here - that's why the order items are scanned in doesn't affect the final total.
`CheckoutService::void($sku)` undoes one scan (e.g. a cashier's mistake).
`CheckoutService::total()` asks a `PriceList` for each SKU's rule and sums the results.
`PriceList` maps SKU -> `PricingRule`. It's the only place that knows which SKUs exist.
`PricingRule` is an interface with two implementations: `UnitPrice` (plain per-item cost) and `MultiPrice` ("buy N for Y", with change left over priced individually). Adding a new pricing style is one new class `CheckoutService` and `PriceList` never change.

## Where the prices or data live

`storage/pricing.json` - plain data, we can later implement product panel or datatable for the dynamic product and price data:

json
{
    "A": {"unit_price": 50, "special_qty": 3, "special_price": 130},
    "B": {"unit_price": 30, "special_qty": 2, "special_price": 45},
    "C": {"unit_price": 20},
    "D": {"unit_price": 15}
}


Change a price, change a discount quantity, or add a brand new item all by editing this file. Nothing else needs to change, and the app picks it up on the next page load with no restart needed.

## How scanned items are remembered

The checkout currently uses the **Laravel session** to remember what's been scanned across page loads, the same way a real shopping cart survives an accidental refresh. It's cleared by clicking **Reset**, not by reloading the page.

One consequence of this: if `storage/pricing.json` changes (an item gets removed or renamed) while a SKU from that item is still sitting in an active session, the next page load will throw an error trying to price something that no longer exists. Clearing cookies for the site (or a private/incognito window) resets it.

## Project structure
app/Common/Pricing/
    PricingRule.php        - the interface every pricing scheme implements
    UnitPrice.php          - plain per-unit pricing
    MultiPrice.php         - "buy N for Y" specials
    PriceList.php          - SKU -> PricingRule lookup, built from pricing.json

app/Common/Services/
    CheckoutService.php    - scan/void/total, the actual kata logic

app/Http/Controllers/
    CheckoutController.php - web glue: session, JSON API, view data

resources/views/checkout/
    index.blade.php        - the checkout page markup
    partials/receipt.blade.php

public/css/checkout.css    - styling, separate from the view
public/js/checkout.js      - interactive behavior (AJAX scan/void/reset), separate from the view

storage/pricing.json       - this week's prices

## Running it

Run this laravel project click a letter to scan it, click the small `x` on a scanned item to remove one, or Reset to start over. Scanning/voiding happens via AJAX the page still works with JavaScript disabled since every button is a real form.
