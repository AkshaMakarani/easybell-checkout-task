<?php
namespace App\Common\Pricing;

final class UnitPrice implements PricingRule
{
    public function __construct(private readonly int $unitPrice)
    {
    }
    //Simple multiplication: quantity times the unit price.
    public function priceFor(int $quantity): int
    {
        return $quantity * $this->unitPrice;
    }

    public function describe(): string
    {
        return "{$this->unitPrice}c";
    }
}
?>
