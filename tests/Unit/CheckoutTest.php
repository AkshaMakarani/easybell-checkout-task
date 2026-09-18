<?php
namespace Tests\Unit;

use App\Common\Pricing\MultiPrice;
use App\Common\Pricing\PriceList;
use App\Common\Pricing\UnitPrice;
use App\Common\Services\CheckoutService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{
    private function rules(): PriceList
    {
        return PriceList::fromArray([
            'A' => new MultiPrice(groupSize: 3, groupPrice: 130, unitPrice: 50),
            'B' => new MultiPrice(groupSize: 2, groupPrice: 45, unitPrice: 30),
            'C' => new UnitPrice(20),
            'D' => new UnitPrice(15),
        ]);
    }

    private function price(string $goods): int
    {
        $checkout = new CheckoutService($this->rules());

        foreach (str_split($goods) as $item) {
            $checkout->scan($item);
        }
        return $checkout->total();
    }

    #[DataProvider('totalsProvider')]
    public function test_totals(int $expected, string $goods): void
    {
        $this->assertSame($expected, $this->price($goods));
    }

    public static function totalsProvider(): array
    {
        return [
            [0, ''], [50, 'A'], [80, 'AB'], [115, 'CDBA'],
            [100, 'AA'], [130, 'AAA'], [180, 'AAAA'], [230, 'AAAAA'], [260, 'AAAAAA'],
            [160, 'AAAB'], [175, 'AAABB'], [190, 'AAABBD'], [190, 'DABABA'],
        ];
    }

    public function test_incremental_scanning(): void
    {
        $checkout = new CheckoutService($this->rules());

        $this->assertSame(0, $checkout->total());

        $checkout->scan('A');
        $this->assertSame(50, $checkout->total());

        $checkout->scan('B');
        $this->assertSame(80, $checkout->total());

        $checkout->scan('A');
        $this->assertSame(130, $checkout->total());

        $checkout->scan('A');
        $this->assertSame(160, $checkout->total());

        $checkout->scan('B');
        $this->assertSame(175, $checkout->total());
    }

    public function test_scanning_an_unknown_item_throws(): void
    {
        $checkout = new CheckoutService($this->rules());
        $this->expectException(InvalidArgumentException::class);
        $checkout->scan('Z');
    }

    public function test_kata_worked_example_scan_b_a_b(): void
    {
        $this->assertSame(95, $this->price('BAB'));
    }

    public function test_voiding_reduces_the_quantity_by_one(): void
    {
        $checkout = new CheckoutService($this->rules());

        $checkout->scan('A');
        $checkout->scan('A');
        $this->assertSame(100, $checkout->total());

        $checkout->void('A');
        $this->assertSame(50, $checkout->total());
    }

    public function test_voiding_the_last_of_an_item_removes_it_entirely(): void
    {
        $checkout = new CheckoutService($this->rules());

        $checkout->scan('A');
        $checkout->void('A');

        $this->assertSame(0, $checkout->total());
    }

    public function test_voiding_an_item_that_was_never_scanned_throws(): void
    {
        $checkout = new CheckoutService($this->rules());
        $this->expectException(InvalidArgumentException::class);
        $checkout->void('A');
    }

    public function test_voiding_more_than_was_scanned_throws(): void
    {
        $checkout = new CheckoutService($this->rules());
        $checkout->scan('A');
        $checkout->void('A');
        $this->expectException(InvalidArgumentException::class);
        $checkout->void('A');
    }

    public function test_voiding_an_unknown_sku_throws(): void
    {
        $checkout = new CheckoutService($this->rules());
        $this->expectException(InvalidArgumentException::class);
        $checkout->void('Z');
    }
}
?>
