<?php
namespace App\Common\Pricing;

final class MultiPrice implements PricingRule
{
    public function __construct(
        private readonly int $groupSize,
        private readonly int $groupPrice,
        private readonly int $unitPrice,
    ){ }
    //Splits the quantity into complete groups plus whatever's left over then prices each part separately and adds them together.
    public function priceFor(int $quantity): int
    {
        $groups = intdiv($quantity, $this->groupSize); //How many complete groups fit into this quantity.
        $remainder = $quantity % $this->groupSize; //Whatever doesn't fit into a full group, priced at the regular rate.

        return ($groups*$this->groupPrice) + ($remainder*$this->unitPrice);
    }
    //Returns a string describing the pricing rule.
    public function describe(): string
    {
        return "{$this->unitPrice}c ({$this->groupSize} for {$this->groupPrice}c)";
    }
}
?>
