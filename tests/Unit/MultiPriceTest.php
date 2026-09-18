<?php
namespace Tests\Unit;

use App\Common\Pricing\MultiPrice;
use PHPUnit\Framework\TestCase;

class MultiPriceTest extends TestCase
{
    public function test_prices_whole_groups_and_remainder_separately(): void
    {
        $rule = new MultiPrice(groupSize: 3, groupPrice: 130, unitPrice: 50);

        $this->assertSame(0, $rule->priceFor(0));
        $this->assertSame(50, $rule->priceFor(1));
        $this->assertSame(100, $rule->priceFor(2));
        $this->assertSame(130, $rule->priceFor(3));
        $this->assertSame(180, $rule->priceFor(4));
        $this->assertSame(260, $rule->priceFor(6));
    }

    public function test_describes_itself(): void
    {
        $rule = new MultiPrice(groupSize: 3, groupPrice: 130, unitPrice: 50);
        $this->assertSame('50c (3 for 130c)', $rule->describe());
    }
}
?>