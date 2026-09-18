@forelse ($items as $item)
    <div class="line-item" data-sku="{{ $item['sku'] }}">
        <span class="sku">{{ $item['sku'] }}</span>
        <span class="qty">&times;{{ $item['quantity'] }}</span>
        <span class="subtotal">{{ number_format($item['subtotal'] / 100, 2) }}</span>
        <form action="{{ route('checkout.void') }}" method="POST" class="ajax-form void-form">
            @csrf
            <input type="hidden" name="sku" value="{{ $item['sku'] }}">
            <button type="submit" class="void-btn" title="Void one {{ $item['sku'] }}">&times;</button>
        </form>
    </div>
@empty
    <div class="empty">No items scanned yet.</div>
@endforelse
