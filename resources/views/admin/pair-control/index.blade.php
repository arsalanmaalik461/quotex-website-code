@extends('admin.layouts.app')
@section('title', 'Market Control')
@section('page-title', 'Market Control')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-market-control.css') }}?v={{ file_exists(public_path('css/admin-market-control.css')) ? filemtime(public_path('css/admin-market-control.css')) : time() }}">
@endpush

@section('content')
<section class="market-control-shell" data-market-control>
    <header class="market-control-header">
        <div>
            <h2>Global Market Control</h2>
            <p>Central market monitor, trading availability, and selected-pair override control.</p>
        </div>
        <div class="market-control-refresh">
            <span id="market-refresh-state">Loading...</span>
            <button type="button" class="market-icon-button" id="market-refresh-button" title="Refresh market monitor" aria-label="Refresh market monitor">
                &#8635;
            </button>
        </div>
    </header>

    <div class="market-alert" id="market-control-alert" hidden></div>

    <section class="market-panel">
        <div class="market-section-title">
            <div>
                <span class="market-label">Selected Pair</span>
                <h3 id="selected-pair-title">Loading</h3>
            </div>
            <select class="market-select market-pair-select" id="selected-pair-select">
                @foreach($marketPairs as $pair)
                    <option value="{{ $pair->id }}">{{ $pair->symbol }}</option>
                @endforeach
            </select>
        </div>
    </section>

    <section class="market-preview-grid">
        <div class="market-panel market-chart-panel">
            <div class="market-section-title">
                <div>
                    <span class="market-label">Live Market Preview</span>
                    <h3 id="chart-pair-title">Backend Market</h3>
                </div>
                <span class="market-pill" id="chart-state-pill">Authoritative</span>
            </div>
            <div class="market-mini-chart" id="market-mini-chart"></div>
        </div>

        <div class="market-panel market-state-panel">
            <div class="market-state-grid">
                <div class="market-state-item">
                    <span>Current Price</span>
                    <strong id="current-price-value">-</strong>
                </div>
                <div class="market-state-item">
                    <span>Current Candle</span>
                    <strong id="current-candle-value">-</strong>
                </div>
                <div class="market-state-item">
                    <span>Next Calculated Candle</span>
                    <strong id="next-candle-value">-</strong>
                </div>
                <div class="market-state-item">
                    <span>Next Candle In</span>
                    <strong id="next-in-value" class="market-countdown">-</strong>
                </div>
                <div class="market-state-item market-state-wide">
                    <span>Effective Control</span>
                    <strong id="effective-control-value">-</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="market-control-grid">
        <div class="market-panel">
            <div class="market-section-title">
                <div>
                    <span class="market-label">Global Market</span>
                    <h3 id="global-market-label">Auto</h3>
                </div>
                <span class="market-pill is-normal">Market Auto</span>
            </div>
            <div class="market-action-row">
                <select class="market-select" id="global-market-mode">
                    <option value="normal">Auto</option>
                </select>
                <button type="button" class="market-button market-button-neutral" id="apply-global-market-button">Apply</button>
            </div>
        </div>

        <div class="market-panel">
            <div class="market-section-title">
                <div>
                    <span class="market-label">Trading Status</span>
                    <h3 id="global-trading-label">Checking</h3>
                </div>
                <span class="market-pill" id="global-status-pill">Checking</span>
            </div>
            <div class="market-action-row">
                <button type="button" class="market-button market-button-danger" id="pause-trading-button">Pause New Trades</button>
                <button type="button" class="market-button market-button-neutral" id="resume-trading-button">Resume Trading</button>
            </div>
        </div>

        <div class="market-panel">
            <div class="market-section-title">
                <div>
                    <span class="market-label">Pair Override</span>
                    <h3 id="pair-override-current">None</h3>
                </div>
                <span class="market-pill" id="pair-override-pair">Selected Pair</span>
            </div>
            <div class="market-action-row">
                <select class="market-select" id="pair-override-select">
                    <option value="auto">None / Auto</option>
                    <option value="pump">Pump</option>
                    <option value="crash">Crash</option>
                    <option value="force_win">Force Win</option>
                    <option value="force_loss">Force Loss</option>
                    <option value="win_side:majority_investment">Win Side: Majority</option>
                    <option value="win_side:minority_investment">Win Side: Minority</option>
                </select>
                <button type="button" class="market-button market-button-neutral" id="apply-pair-override">Apply</button>
            </div>
        </div>
    </section>
</section>
@endsection

@push('scripts')
    <script>
        window.ADMIN_MARKET_PAIRS = @json($marketPairOptions);
    </script>
    <script src="https://unpkg.com/lightweight-charts@4.1.1/dist/lightweight-charts.standalone.production.js"></script>
    <script src="{{ asset('js/admin-market-control.js') }}?v={{ file_exists(public_path('js/admin-market-control.js')) ? filemtime(public_path('js/admin-market-control.js')) : time() }}" defer></script>
@endpush
