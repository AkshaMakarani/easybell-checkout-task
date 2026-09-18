<?php
namespace App\Common\Pricing;

interface PricingRule
{
    //Calculate the price for a given quantity of a single SKU.
    public function priceFor(int $quantity): int;
    public function describe(): string;
}
?>