<?php
namespace App\Common\Pricing;
use InvalidArgumentException;
final class PriceList
{
    public function __construct(private readonly array $rules)
    {
    }

    public static function fromArray(array $rules): self
    {
        return new self($rules);
    }
    //Creates a PriceList instance from a JSON file.
    public static function fromJsonFile(string $path): self
    {
        $data = json_decode(file_get_contents($path), associative: true, flags: JSON_THROW_ON_ERROR);

        $rules = [];
        //For every SKU in the file, decide which PricingRule shape it needs.
        foreach ($data as $sku => $attributes) {
            $rules[$sku] = isset($attributes['special_qty'], $attributes['special_price'])
                ? new MultiPrice(
                    groupSize: $attributes['special_qty'],
                    groupPrice: $attributes['special_price'],
                    unitPrice: $attributes['unit_price'],
                )
                : new UnitPrice($attributes['unit_price']);
        }
        return new self($rules);
    }
    //Checks whether a pricing rule exists for the given SKU.
    public function has(string $sku): bool
    {
        return isset($this->rules[$sku]);
    }
    //Returns an array of all SKUs for which pricing rules are defined.
    public function skus(): array
    {
        return array_keys($this->rules);
    }
    //Returns the pricing rule for the given SKU.
    public function ruleFor(string $sku): PricingRule
    {
        if (! $this->has($sku)) {
            throw new InvalidArgumentException("No pricing rule defined for SKU [{$sku}].");
        }
        return $this->rules[$sku];
    }
}
?>