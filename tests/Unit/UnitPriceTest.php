<?php
namespace Tests\Unit;

use App\Common\Pricing\UnitPrice;
use PHPUnit\Framework\TestCase;

class UnitPriceTest extends TestCase
{
    public function test_multiplies_quantity_by_unit_price(): void
    {
        $rule = new UnitPrice(20);

        $this->assertSame(0, $rule->priceFor(0));
        $this->assertSame(20, $rule->priceFor(1));
        $this->assertSame(100, $rule->priceFor(5));
    }

    public function test_describes_itself(): void
    {
        $this->assertSame('20c', (new UnitPrice(20))->describe());
    }
}
?>