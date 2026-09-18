<?php
namespace App\Http\Controllers;

use App\Common\Pricing\PriceList;
use App\Common\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    private const SESSION_KEY = 'checkout.scanned';

    public function __construct(private readonly PriceList $priceList)
    {
    }
    //Displays the checkout page with the current state of scanned items and total price.
    public function index(Request $request): View
    {
        $scanned = $this->scannedFromSession($request);//$request->session()->get(self::SESSION_KEY, []);
        $skus = $this->priceList->skus();
        return view('checkout.index', [
            ...$this->stateFor($scanned),
            'skus' => $skus,
            'descriptions' => array_combine(
                $skus,
                array_map(fn ($sku) => $this->priceList->ruleFor($sku)->describe(), $skus)
            ),
        ]);
    }
    //Adds one instance of the given SKU to the scanned items, throwing an exception if the SKU is unknown.
    public function scan(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string'],
        ]);

        if (! $this->priceList->has($validated['sku'])) {
            return $this->error($request, "Unknown item [{$validated['sku']}].");
        }

        $scanned = $this->scannedFromSession($request);//$request->session()->get(self::SESSION_KEY, []);
        $scanned[] = $validated['sku'];
        $request->session()->put(self::SESSION_KEY, $scanned);

        return $this->respond($request, $scanned);
    }
    //Removes one instance of the given SKU from the scanned items, throwing an exception if the SKU is unknown or hasn't been scanned yet.
    public function void(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string'],
        ]);

        $scanned = $this->scannedFromSession($request);//$request->session()->get(self::SESSION_KEY, []);

        $checkout = new CheckoutService($this->priceList);
        foreach ($scanned as $sku) {
            $checkout->scan($sku);
        }

        try {
            $checkout->void($validated['sku']);
        } catch (InvalidArgumentException $e) {
            return $this->error($request, $e->getMessage());
        }

        $position = array_search($validated['sku'], $scanned, true);
        unset($scanned[$position]);
        $scanned = array_values($scanned);

        $request->session()->put(self::SESSION_KEY, $scanned);

        return $this->respond($request, $scanned);
    }
    //Resets the checkout by clearing the scanned items from the session.
    public function reset(Request $request): RedirectResponse|JsonResponse
    {
        $request->session()->forget(self::SESSION_KEY);
        return $this->respond($request, []);
    }
    //Returns an error response, either as JSON or a redirect back to the previous page with an error message.
    private function error(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }
        return back()->withErrors(['sku' => $message]);
    }
    //Returns a response containing the current state of the checkout, either as JSON or a redirect back to the previous page.
    private function respond(Request $request, array $scanned): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json($this->stateFor($scanned));
        }
        return back();
    }
    //Returns the current state of the checkout, including scanned items and total price.
    private function stateFor(array $scanned): array
    {
        $tally = array_count_values($scanned);

        $items = [];
        foreach ($tally as $sku => $quantity) {
            $items[] = [
                'sku' => $sku,
                'quantity' => $quantity,
                'subtotal' => $this->priceList->ruleFor($sku)->priceFor($quantity),
            ];
        }
        return [
            'items' => $items,
            'total' => array_sum(array_column($items, 'subtotal')),
        ];
    }
    //Retrieves the scanned items from the session, filtering out any SKUs that don't have a pricing rule defined.
    private function scannedFromSession(Request $request): array
    {
        $scanned = $request->session()->get(self::SESSION_KEY, []);

        return array_values(array_filter(
            $scanned,
            fn ($sku) => $this->priceList->has($sku)
        ));
    }
}
?>