<?php
namespace Tests\Unit;

use App\Common\Pricing\PriceList;
use PHPUnit\Framework\TestCase;

class PriceListTest extends TestCase
{
    public function test_builds_rules_from_a_json_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pricing');
        file_put_contents($path, json_encode([
            'A' => ['unit_price' => 50, 'special_qty' => 3, 'special_price' => 130],
            'C' => ['unit_price' => 20],
        ]));

        $priceList = PriceList::fromJsonFile($path);
        unlink($path);

        $this->assertSame(['A', 'C'], $priceList->skus());
        $this->assertSame(130, $priceList->ruleFor('A')->priceFor(3));
        $this->assertSame(60, $priceList->ruleFor('C')->priceFor(3));
    }

    public function test_editing_the_json_data_changes_pricing_with_no_code_change(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pricing');

        file_put_contents($path, json_encode([
            'A' => ['unit_price' => 50, 'special_qty' => 3, 'special_price' => 130],
        ]));
        $threeForOnethirty = PriceList::fromJsonFile($path)->ruleFor('A')->priceFor(4);

        file_put_contents($path, json_encode([
            'A' => ['unit_price' => 50, 'special_qty' => 2, 'special_price' => 50],
        ]));
        $buyOneGetOneFree = PriceList::fromJsonFile($path)->ruleFor('A')->priceFor(4);

        unlink($path);

        $this->assertSame(180, $threeForOnethirty);
        $this->assertSame(100, $buyOneGetOneFree);
    }
}
?>