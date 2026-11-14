(function () {
  'use strict';

  const root = document.querySelector('[data-market-control]');
  if (!root) return;

  const STORAGE_KEY = 'admin.market.selectedPair.v1';
  const DEFAULT_VISIBLE_CANDLES = 48;
  const RIGHT_OFFSET = 4;
  const pairs = Array.isArray(window.ADMIN_MARKET_PAIRS) ? window.ADMIN_MARKET_PAIRS : [];
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const els = {
    alert: document.getElementById('market-control-alert'),
    refreshState: document.getElementById('market-refresh-state'),
    refreshButton: document.getElementById('market-refresh-button'),
    selectedPair: document.getElementById('selected-pair-select'),
    selectedPairTitle: document.getElementById('selected-pair-title'),
    chartPairTitle: document.getElementById('chart-pair-title'),
    chartStatePill: document.getElementById('chart-state-pill'),
    chart: document.getElementById('market-mini-chart'),
    price: document.getElementById('current-price-value'),
    currentCandle: document.getElementById('current-candle-value'),
    nextCandle: document.getElementById('next-candle-value'),
    nextIn: document.getElementById('next-in-value'),
    effectiveControl: document.getElementById('effective-control-value'),
    globalMarketLabel: document.getElementById('global-market-label'),
    globalMode: document.getElementById('global-market-mode'),
    applyGlobal: document.getElementById('apply-global-market-button'),
    tradingLabel: document.getElementById('global-trading-label'),
    tradingPill: document.getElementById('global-status-pill'),
    pauseButton: document.getElementById('pause-trading-button'),
    resumeButton: document.getElementById('resume-trading-button'),
    pairOverridePair: document.getElementById('pair-override-pair'),
    pairOverrideCurrent: document.getElementById('pair-override-current'),
    pairOverrideSelect: document.getElementById('pair-override-select'),
    pairOverrideApply: document.getElementById('apply-pair-override'),
  };

  const state = {
    global: null,
    pair: null,
    loading: false,
    nextRefreshAt: 0,
    chart: null,
    series: null,
    initializedRange: false,
  };

  function request(url, options = {}) {
    return fetch(url, {
      ...options,
      headers: {
        Accept: 'application/json',
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
        ...(options.headers || {}),
      },
    }).then(async response => {
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) {
        const error = new Error(payload.message || payload.error || 'Request failed');
        error.status = response.status;
        throw error;
      }
      return payload;
    });
  }

  function showAlert(message, type = 'success') {
    if (!els.alert) return;
    els.alert.textContent = message;
    els.alert.className = `market-alert is-${type}`;
    els.alert.hidden = false;
    clearTimeout(showAlert.timer);
    showAlert.timer = setTimeout(() => { els.alert.hidden = true; }, 4500);
  }

  function setBusy(isBusy) {
    [
      els.refreshButton,
      els.selectedPair,
      els.applyGlobal,
      els.pauseButton,
      els.resumeButton,
      els.pairOverrideApply,
    ].forEach(control => {
      if (control) control.disabled = isBusy;
    });
  }

  function selectedPairIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const pairId = params.get('pair_id') || params.get('asset');
    if (pairId && pairs.some(pair => Number(pair.id) === Number(pairId))) return String(pairId);
    const symbol = params.get('symbol') || params.get('pair');
    if (symbol) {
      const found = pairs.find(pair => String(pair.symbol).toLowerCase() === symbol.toLowerCase());
      if (found) return String(found.id);
    }
    return null;
  }

  function readSavedPairId() {
    try {
      const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
      if (saved && pairs.some(pair => Number(pair.id) === Number(saved.id))) return String(saved.id);
      localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
      localStorage.removeItem(STORAGE_KEY);
    }
    return null;
  }

  function resolveInitialPairId() {
    return selectedPairIdFromUrl() || readSavedPairId() || (pairs[0] ? String(pairs[0].id) : '');
  }

  function persistPairId(pairId) {
    const pair = pairs.find(item => Number(item.id) === Number(pairId));
    if (!pair) return;
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ id: Number(pair.id), symbol: pair.symbol }));
    } catch (e) {}
  }

  function formatStatus(value) {
    return String(value || 'normal').replace(/_/g, ' ').replace(/\b\w/g, ch => ch.toUpperCase());
  }

  function formatNumber(value, pair = state.pair) {
    const number = Number(value);
    if (!Number.isFinite(number)) return '-';
    const digits = Number(pair?.digits);
    const maxDigits = Number.isFinite(digits) && digits > 0 ? Math.min(digits, 6) : (number >= 10 ? 2 : 5);
    return number.toLocaleString('en-US', {
      minimumFractionDigits: Math.min(maxDigits, number >= 10 ? 2 : 4),
      maximumFractionDigits: maxDigits,
    });
  }

  function directionLabel(value) {
    const normalized = String(value || '').toLowerCase();
    if (normalized === 'up') return 'UP';
    if (normalized === 'down') return 'DOWN';
    return 'N/A';
  }

  function directionClass(value) {
    const normalized = String(value || '').toLowerCase();
    if (normalized === 'up') return 'market-direction-up';
    if (normalized === 'down') return 'market-direction-down';
    return '';
  }

  function controlValue(pair) {
    const control = pair?.pair_control || {};
    if (control.win_side && control.win_side !== 'none') return `win_side:${control.win_side}`;
    return control.control_mode || 'auto';
  }

  function controlLabel(pair) {
    const value = controlValue(pair);
    if (value === 'auto') return 'None';
    if (value.startsWith('win_side:')) return `Win Side: ${formatStatus(value.replace('win_side:', ''))}`;
    return formatStatus(value);
  }

  function effectiveLabel(pair) {
    return pair?.effective_control?.label || (controlValue(pair) === 'auto' ? 'GLOBAL AUTO' : `PAIR ${controlLabel(pair).toUpperCase()}`);
  }

  function renderGlobal(status) {
    const globalStatus = String(status || state.global?.mode || 'normal').toLowerCase();
    if (els.globalMarketLabel) els.globalMarketLabel.textContent = 'Auto';
    if (els.globalMode) els.globalMode.value = 'normal';
    if (els.tradingLabel) els.tradingLabel.textContent = globalStatus === 'trading_paused' ? 'Paused' : 'Enabled';
    if (els.tradingPill) {
      els.tradingPill.textContent = globalStatus === 'trading_paused' ? 'New trades blocked' : 'New trades allowed';
      els.tradingPill.className = `market-pill ${globalStatus === 'trading_paused' ? 'is-paused' : 'is-normal'}`;
    }
  }

  function renderPair(pair) {
    state.pair = pair;
    const currentDirection = directionLabel(pair.current_candle);
    const nextDirection = directionLabel(pair.next_calculated_candle);

    if (els.selectedPairTitle) els.selectedPairTitle.textContent = pair.symbol;
    if (els.chartPairTitle) els.chartPairTitle.textContent = pair.symbol;
    if (els.price) els.price.textContent = formatNumber(pair.current_price, pair);
    if (els.currentCandle) {
      els.currentCandle.textContent = currentDirection;
      els.currentCandle.className = directionClass(pair.current_candle);
    }
    if (els.nextCandle) {
      els.nextCandle.textContent = nextDirection;
      els.nextCandle.className = directionClass(pair.next_calculated_candle);
    }
    if (els.nextIn) {
      els.nextIn.textContent = formatSeconds(pair.time_remaining);
      els.nextIn.dataset.seconds = String(Number(pair.time_remaining) || 0);
    }
    if (els.effectiveControl) els.effectiveControl.textContent = effectiveLabel(pair);
    if (els.pairOverridePair) els.pairOverridePair.textContent = pair.symbol;
    if (els.pairOverrideCurrent) els.pairOverrideCurrent.textContent = controlLabel(pair);
    if (els.pairOverrideSelect) els.pairOverrideSelect.value = controlValue(pair);

    renderChart(pair.candles || [], false);
  }

  function formatSeconds(seconds) {
    const safeSeconds = Math.max(0, Number(seconds) || 0);
    return `${safeSeconds}s`;
  }

  function initChart() {
    if (!els.chart || !window.LightweightCharts || state.chart) return;
    state.chart = LightweightCharts.createChart(els.chart, {
      layout: {
        background: { color: '#101625' },
        textColor: '#7a8aaa',
        fontFamily: "'Inter', sans-serif",
      },
      grid: {
        vertLines: { color: '#1e2d47' },
        horzLines: { color: '#1e2d47' },
      },
      rightPriceScale: {
        borderColor: '#1e2d47',
        autoScale: true,
        scaleMargins: { top: 0.18, bottom: 0.16 },
      },
      timeScale: {
        borderColor: '#1e2d47',
        timeVisible: true,
        secondsVisible: true,
      },
      crosshair: {
        mode: LightweightCharts.CrosshairMode.Normal,
      },
      handleScroll: true,
      handleScale: true,
    });
    state.series = state.chart.addCandlestickSeries({
      upColor: '#00d17a',
      downColor: '#ff4c6a',
      borderUpColor: '#00d17a',
      borderDownColor: '#ff4c6a',
      wickUpColor: '#00d17a',
      wickDownColor: '#ff4c6a',
    });

    const resizeObserver = new ResizeObserver(() => {
      if (!state.chart || !els.chart) return;
      state.chart.applyOptions({
        width: els.chart.clientWidth,
        height: els.chart.clientHeight,
      });
    });
    resizeObserver.observe(els.chart);
  }

  function applyDefaultVisibleRange(candles) {
    if (!state.chart || !candles.length) return;
    const lastIndex = candles.length - 1;
    const visible = Math.min(DEFAULT_VISIBLE_CANDLES, candles.length);
    try {
      state.chart.timeScale().setVisibleLogicalRange({
        from: Math.max(0, lastIndex - visible + 1),
        to: lastIndex + RIGHT_OFFSET,
      });
    } catch (e) {}
  }

  function renderChart(candles, resetRange) {
    initChart();
    if (!state.series) return;
    const mapped = candles.map(candle => ({
      time: candle.time,
      open: Number(candle.open),
      high: Number(candle.high),
      low: Number(candle.low),
      close: Number(candle.close),
    }));
    state.series.setData(mapped);
    if (resetRange || !state.initializedRange) {
      applyDefaultVisibleRange(mapped);
      state.initializedRange = true;
    }
  }

  async function loadMonitor(options = {}) {
    if (state.loading) return;
    const pairId = els.selectedPair?.value || resolveInitialPairId();
    if (!pairId) return;

    state.loading = true;
    setBusy(true);
    try {
      const [globalPayload, monitorPayload] = await Promise.all([
        request('/admin/global-control'),
        request(`/admin/pair-control/monitor?pair_id=${encodeURIComponent(pairId)}`),
      ]);
      state.global = globalPayload.control || null;
      renderGlobal(monitorPayload.global_status || state.global?.mode);
      renderPair(monitorPayload.pair);
      if (options.resetRange) {
        state.initializedRange = false;
        renderChart(monitorPayload.pair.candles || [], true);
      }
      state.nextRefreshAt = Date.now() + 5000;
      if (els.refreshState) els.refreshState.textContent = 'Updated now';
      if (els.chartStatePill) els.chartStatePill.textContent = 'Live';
    } catch (error) {
      showAlert(error.message || 'Market data temporarily unavailable.', 'error');
      if (els.refreshState) els.refreshState.textContent = 'Update failed';
      if (els.chartStatePill) els.chartStatePill.textContent = 'Stale';
    } finally {
      state.loading = false;
      setBusy(false);
    }
  }

  async function applyGlobalMarket() {
    if (!window.confirm('Apply Global Market mode AUTO? This does not change trading pause status.')) return;
    setBusy(true);
    try {
      const globalPayload = await request('/admin/global-control');
      const current = globalPayload.control || null;
      const currentlyPaused = current?.enabled && current?.mode === 'trading_paused';
      await request('/admin/global-control', {
        method: 'POST',
        body: JSON.stringify({
          enabled: currentlyPaused,
          mode: currentlyPaused ? 'trading_paused' : 'normal',
          scope: currentlyPaused ? 'entry' : 'all',
          version: current?.version,
          reason: 'Set global market mode to auto from Market Control',
        }),
      });
      showAlert('Global market mode confirmed as Auto.');
      await loadMonitor();
    } catch (error) {
      if (error.status === 409) {
        showAlert('Market control was changed elsewhere. Refreshing current state.', 'error');
        await loadMonitor();
      } else {
        showAlert(error.message || 'Unable to update market control.', 'error');
      }
    } finally {
      setBusy(false);
    }
  }

  async function updateTradingPause(paused) {
    const message = paused
      ? 'Pause new trade entries? Existing settlements and backend market generation remain unchanged.'
      : 'Resume new trade entries?';
    if (!window.confirm(message)) return;

    setBusy(true);
    try {
      const globalPayload = await request('/admin/global-control');
      const current = globalPayload.control || null;
      await request('/admin/global-control', {
        method: 'POST',
        body: JSON.stringify({
          enabled: paused,
          mode: paused ? 'trading_paused' : 'normal',
          scope: paused ? 'entry' : 'all',
          version: current?.version,
          reason: paused ? 'Paused from Market Control monitor' : 'Resumed from Market Control monitor',
        }),
      });
      showAlert(paused ? 'New trade entries are paused.' : 'Trading is enabled.');
      await loadMonitor();
    } catch (error) {
      if (error.status === 409) {
        showAlert('Market control was changed elsewhere. Refreshing current state.', 'error');
        await loadMonitor();
      } else {
        showAlert(error.message || 'Unable to update market control.', 'error');
      }
    } finally {
      setBusy(false);
    }
  }

  async function applyPairOverride() {
    const pairId = els.selectedPair?.value;
    const pair = pairs.find(item => Number(item.id) === Number(pairId));
    const rawValue = els.pairOverrideSelect?.value || 'auto';
    if (!pair) return;

    const parsed = parsePairControl(rawValue);
    const modeLabel = rawValue === 'auto' ? 'None / Auto' : formatStatus(rawValue.replace('win_side:', 'win side '));
    if (!window.confirm(`Change pair override?\n\nPair: ${pair.symbol}\nMode: ${modeLabel}`)) return;

    setBusy(true);
    try {
      if (parsed.mode === 'auto' && parsed.winSide === 'none') {
        await request(`/admin/pair-control/${pair.id}/reset`, { method: 'POST' });
      } else {
        await request(`/admin/pair-control/${pair.id}/set-mode`, {
          method: 'POST',
          body: JSON.stringify({
            control_mode: parsed.mode,
            win_side: parsed.winSide,
            duration: parsed.duration,
          }),
        });
      }
      showAlert('Pair override confirmed from backend state.');
      await loadMonitor();
    } catch (error) {
      showAlert(error.message || 'Unable to update market control.', 'error');
      await loadMonitor();
    } finally {
      setBusy(false);
    }
  }

  function parsePairControl(rawValue) {
    if (rawValue.startsWith('win_side:')) {
      return { mode: 'auto', winSide: rawValue.replace('win_side:', ''), duration: 30 };
    }
    return { mode: rawValue, winSide: 'none', duration: rawValue === 'auto' ? 0 : 15 };
  }

  function tickCountdown() {
    if (!els.nextIn) return;
    const next = Math.max(0, (Number(els.nextIn.dataset.seconds) || 0) - 1);
    els.nextIn.dataset.seconds = String(next);
    els.nextIn.textContent = formatSeconds(next);

    const secondsUntilRefresh = Math.max(0, Math.ceil((state.nextRefreshAt - Date.now()) / 1000));
    if (els.refreshState && !state.loading) {
      els.refreshState.textContent = secondsUntilRefresh > 0 ? `Refresh in ${secondsUntilRefresh}s` : 'Refreshing...';
    }
    if ((next <= 0 || secondsUntilRefresh <= 0) && !state.loading) loadMonitor();
  }

  function initializeSelection() {
    const initial = resolveInitialPairId();
    if (els.selectedPair && initial) {
      els.selectedPair.value = initial;
      persistPairId(initial);
    }
  }

  els.refreshButton?.addEventListener('click', () => loadMonitor());
  els.selectedPair?.addEventListener('change', () => {
    persistPairId(els.selectedPair.value);
    state.initializedRange = false;
    loadMonitor({ resetRange: true });
  });
  els.applyGlobal?.addEventListener('click', applyGlobalMarket);
  els.pauseButton?.addEventListener('click', () => updateTradingPause(true));
  els.resumeButton?.addEventListener('click', () => updateTradingPause(false));
  els.pairOverrideApply?.addEventListener('click', applyPairOverride);

  initializeSelection();
  setInterval(tickCountdown, 1000);
  loadMonitor({ resetRange: true });
})();
