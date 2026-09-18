<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Easybell Checkout Task</title>
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
</head>
<body data-void-url="{{ route('checkout.void') }}">
    <div class="card">
        <h1>Checkout</h1>
        <p class="rules">
            @foreach ($descriptions as $sku => $description)
                <b>{{ $sku }}</b>: {{ $description }}{{ ! $loop->last ? ' | ' : '' }}
            @endforeach
        </p>

        <div class="error" id="error">{{ $errors->first() }}</div>

        <div class="scan-buttons">
            @foreach ($skus as $sku)
                <form action="{{ route('checkout.scan') }}" method="POST" class="ajax-form">
                    @csrf
                    <input type="hidden" name="sku" value="{{ $sku }}">
                    <button type="submit" class="scan-btn">{{ $sku }}</button>
                </form>
            @endforeach
        </div>

        <div class="receipt" id="receipt">
            @include('checkout.partials.receipt', ['items' => $items])
        </div>

        <div class="total-row">
            <span class="label">Total</span>
            <span class="amount" id="total">{{ number_format($total / 100, 2) }}</span>
        </div>

        <div class="actions">
            <form action="{{ route('checkout.reset') }}" method="POST" class="ajax-form">
                @csrf
                <button type="submit" class="reset-btn">Reset</button>
            </form>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <script src="{{ asset('js/checkout.js') }}"></script>
</body>
</html>
