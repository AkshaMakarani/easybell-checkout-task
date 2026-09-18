<?php
namespace App\Common\Services;

use App\Common\Pricing\PriceList;
use InvalidArgumentException;

class CheckoutService
{
    private array $items = [];

    public function __construct(private readonly PriceList $priceList)
    {
    }
    //Adds one instance of the given SKU to the scanned items, throwing an exception if the SKU is unknown.
    public function scan(string $sku): void
    {
        if (! $this->priceList->has($sku)) {
            throw new InvalidArgumentException("Cannot scan unknown item [{$sku}].");
        }
        $this->items[$sku] = ($this->items[$sku] ?? 0) + 1;
    }
    //Removes one instance of the given SKU from the scanned items, throwing an exception if the SKU is unknown or hasn't been scanned yet.
    public function void(string $sku): void
    {
        if (! $this->priceList->has($sku)) {
            throw new InvalidArgumentException("Cannot void unknown item [{$sku}].");
        }

        if (($this->items[$sku] ?? 0) === 0) {
            throw new InvalidArgumentException("Cannot void item [{$sku}] that hasn't been scanned.");
        }

        $this->items[$sku]--;    
        if ($this->items[$sku] === 0) {
            unset($this->items[$sku]);
        }
    }
    //Returns the total price for all scanned items, using the pricing rules defined in the PriceList.
    public function total(): int
    {
        $total = 0;
        foreach ($this->items as $sku => $quantity) {
            $total += $this->priceList->ruleFor($sku)->priceFor($quantity);
        }
        return $total;
    }
}
