// ===== QUOTEX CLONE - COMPREHENSIVE TRADING ENGINE (TRADE.JS) =====
(function () {
  'use strict';

  // --- Sound Effects via Web Audio API ---
  const soundContext = {
    ctx: null,
    enabled: true,
    init() {
      if (!this.ctx && (window.AudioContext || window.webkitAudioContext)) {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        this.ctx = new AudioCtx();
      }
    },
    playBeep(freq = 600, duration = 0.08, type = 'sine') {
      if (!this.enabled) return;
      try {
        this.init();
        if (this.ctx.state === 'suspended') this.ctx.resume();
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = type;
        osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
        gain.gain.setValueAtTime(0.15, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + duration);
        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.start();
        osc.stop(this.ctx.currentTime + duration);
      } catch (e) {}
    },
    playTradePlaced() {
      this.playBeep(880, 0.06);
      setTimeout(() => this.playBeep(1100, 0.09), 70);
    },
    playWin() {
      [523.25, 659.25, 783.99, 1046.50].forEach((freq, i) => {
        setTimeout(() => this.playBeep(freq, 0.18, 'triangle'), i * 80);
      });
    },
    playLoss() {
      this.playBeep(330, 0.12, 'sawtooth');
      setTimeout(() => this.playBeep(260, 0.18, 'sawtooth'), 120);
    }
  };

  // --- Quotex Currency to Country Flag Mapping ---
  const CURRENCY_COUNTRY_MAP = {
    USD: 'us', EUR: 'eu', GBP: 'gb', JPY: 'jp', AUD: 'au',
    CHF: 'ch', CAD: 'ca', NZD: 'nz', BDT: 'bd', IDR: 'id',
    BRL: 'br', INR: 'in', PKR: 'pk', NGN: 'ng', DZD: 'dz',
    TRY: 'tr', RUB: 'ru', MXN: 'mx', ZAR: 'za', CNY: 'cn',
    SGD: 'sg', AED: 'ae', COP: 'co', PHP: 'ph', MYR: 'my', EGP: 'eg',
    ARS: 'ar', CLP: 'cl', NOK: 'no', SEK: 'se'
  };

  function renderAssetFlagsHtml(symbol, fallbackFlag = '🌐', type = 'forex') {
    if (!symbol) return `<span class="qx-flag-circle-generic">${fallbackFlag || '🌐'}</span>`;
    const cleanSym = symbol.replace(/\(.*?\)/g, '').trim();
    const parts = cleanSym.split('/');
    if (parts.length === 2) {
      const c1 = parts[0].trim().toUpperCase();
      const c2 = parts[1].trim().toUpperCase();
      const f1 = CURRENCY_COUNTRY_MAP[c1];
      const f2 = CURRENCY_COUNTRY_MAP[c2];
      if (f1 && f2) {
        return `<span class="qx-dual-flags">
          <img src="/images/flags/${f1}.svg" class="qx-flag-circle qx-flag-base" alt="${c1}">
          <img src="/images/flags/${f2}.svg" class="qx-flag-circle qx-flag-quote" alt="${c2}">
        </span>`;
      }
    }
    if (/BTC|Bitcoin/i.test(symbol)) {
      return `<span class="qx-dual-flags">
        <span class="qx-flag-circle qx-crypto-circle btc">₿</span>
        <img src="/images/flags/us.svg" class="qx-flag-circle qx-flag-quote" alt="USD">
      </span>`;
    }
    if (/ETH|Ethereum/i.test(symbol)) {
      return `<span class="qx-dual-flags">
        <span class="qx-flag-circle qx-crypto-circle eth">⟠</span>
        <img src="/images/flags/us.svg" class="qx-flag-circle qx-flag-quote" alt="USD">
      </span>`;
    }
    if (/XAU|Gold/i.test(symbol)) {
      return `<span class="qx-dual-flags">
        <span class="qx-flag-circle qx-commodity-circle gold">🥇</span>
        <img src="/images/flags/us.svg" class="qx-flag-circle qx-flag-quote" alt="USD">
      </span>`;
    }
    return `<span class="qx-flag-circle-generic">${fallbackFlag || '🌐'}</span>`;
  }

  // --- Global Trading State ---
  const SELECTED_PAIR_STORAGE_KEY = 'trade.selectedPair.v1';
  const DEFAULT_VISIBLE_CANDLE_WIDTH = 9;
  const DEFAULT_VISIBLE_CANDLES_MIN = 36;
  const DEFAULT_VISIBLE_CANDLES_MAX = 96;
  const DEFAULT_RIGHT_OFFSET_CANDLES = 5;

  const state = {
    selectedAsset: null,
    openTabs: [],
    assetPanelMode: 'replace', // 'replace' (replace active tab) or 'add' (add new tab via '+')
    durationSeconds: 60,
    timeMode: 'duration', // 'duration' or 'clock'
    investment: 100,
    investMode: 'fixed', // 'fixed' ($) or 'percent' (%)
    currentPrice: 0,
    candles: [],
    chart: null,
    series: null,
    chartType: 'candles', // 'candles' or 'area'
    timeframeSec: 60,
    priceTimer: null,
    marketSyncTimer: null,
    marketRequestSeq: 0,
    activeTrades: [], // Array of running trades
    accountType: 'demo',
    balance: 10000,
    oneClick: true,
    indicators: { sma: false, ema: false, bollinger: false },
    smaSeries: null,
    emaSeries: null,
    bbUpperSeries: null,
    bbLowerSeries: null,
    chartMarkers: [],
    pendingPriceLine: null,
  };

  // --- DOM Cache ---
  const el = {
    assetSymbol: document.getElementById('asset-symbol'),
    assetPayout: document.getElementById('asset-payout'),
    assetFlag: document.getElementById('asset-flag'),
    panelSymbol: document.getElementById('panel-symbol'),
    panelPayout: document.getElementById('panel-payout'),
    panelFlag: document.getElementById('panel-flag'),
    timeDisplay: document.getElementById('time-display'),
    timeModeLabel: document.getElementById('time-mode-label'),
    investmentInput: document.getElementById('investment-input'),
    investModeLabel: document.getElementById('invest-mode-label'),
    investPrefix: document.getElementById('invest-prefix'),
    payoutDisplay: document.getElementById('payout-display'),
    yourPayoutDisplay: document.getElementById('your-payout-display'),
    balanceDisplay: document.getElementById('account-balance'),
    accountTypeLabel: document.getElementById('account-type-label'),
    btnUp: document.getElementById('btn-up'),
    btnDown: document.getElementById('btn-down'),
    activeTradesCount: document.getElementById('active-trades-count'),
    activeTradesContainer: document.getElementById('active-trades-container'),
    emptyActiveMsg: document.getElementById('empty-active-msg'),
    closedTradesContainer: document.getElementById('closed-trades-container'),
    tradeList: document.getElementById('trade-list'),
    toast: document.getElementById('trade-toast'),
    assetPanel: document.getElementById('asset-panel'),
    assetSearchInput: document.getElementById('asset-search'),
    accountDropdown: document.getElementById('account-dropdown'),
    tabsBar: document.getElementById('qx-open-tabs-bar'),
    signalsDrawer: document.getElementById('signals-drawer'),
    leaderboardModal: document.getElementById('leaderboard-modal'),
    settingsModal: document.getElementById('settings-modal'),
  };

  function normalizeAsset(asset) {
    if (!asset) return null;
    const normalized = {
      id: Number(asset.id),
      symbol: String(asset.symbol || ''),
      name: asset.name || asset.symbol || '',
      payout: Number(asset.payout || 85),
      flag: asset.flag || '',
      base: Number(asset.base || 1),
      digits: Number(asset.digits || 5),
      type: asset.type || '',
    };
    const base = Number(normalized.base || 0);
    const sym = normalized.symbol.toUpperCase();
    if (base >= 500 || sym.includes('BTC') || sym.includes('ETH') || sym.includes('GOLD') || sym.includes('XAU')) {
      normalized.digits = 2;
    } else if (base >= 50 || sym.includes('JPY')) {
      normalized.digits = 3;
    }
    return normalized;
  }

  function readServerSelectedAsset() {
    return normalizeAsset({
      id: document.getElementById('asset-id-val')?.value || 1,
      symbol: el.assetSymbol ? el.assetSymbol.textContent.trim() : 'USD/NGN',
      payout: document.getElementById('asset-payout-val')?.value || 85,
      flag: el.assetFlag ? el.assetFlag.textContent.trim() : '',
      base: document.getElementById('base-price')?.value || 1,
      digits: document.getElementById('asset-digits-val')?.value || 5,
    });
  }

  function assetFromRow(row) {
    if (!row) return null;
    return normalizeAsset({
      id: row.dataset.id,
      symbol: row.dataset.symbol,
      name: row.dataset.name,
      payout: row.dataset.payout,
      flag: row.dataset.flag,
      base: row.dataset.base,
      digits: row.dataset.digits,
      type: row.dataset.cat,
    });
  }

  function getAvailableAssets() {
    return Array.from(document.querySelectorAll('.asset-list-item'))
      .map(assetFromRow)
      .filter(asset => asset && asset.id && asset.symbol);
  }

  function sameAsset(a, b) {
    return !!(a && b && ((Number(a.id) && Number(a.id) === Number(b.id)) || a.symbol === b.symbol));
  }

  function explicitAssetRequested() {
    const params = new URLSearchParams(window.location.search);
    return params.has('asset') || params.has('symbol') || params.has('pair');
  }

  function explicitRequestedAsset() {
    const params = new URLSearchParams(window.location.search);
    const assets = getAvailableAssets();
    const assetId = params.get('asset');
    const symbol = params.get('symbol') || params.get('pair');
    if (assetId) {
      const byId = assets.find(asset => Number(asset.id) === Number(assetId));
      if (byId) return byId;
    }
    if (symbol) {
      const normalizedSymbol = symbol.trim().toLowerCase();
      const bySymbol = assets.find(asset => asset.symbol.toLowerCase() === normalizedSymbol);
      if (bySymbol) return bySymbol;
    }
    return null;
  }

  function readSavedAsset() {
    try {
      const saved = JSON.parse(localStorage.getItem(SELECTED_PAIR_STORAGE_KEY) || 'null');
      if (!saved) return null;
      const assets = getAvailableAssets();
      const match = assets.find(asset => sameAsset(asset, saved)) || null;
      if (!match) localStorage.removeItem(SELECTED_PAIR_STORAGE_KEY);
      return match;
    } catch (e) {
      localStorage.removeItem(SELECTED_PAIR_STORAGE_KEY);
      return null;
    }
  }

  function persistSelectedAsset(asset) {
    if (!asset || !asset.id || !asset.symbol) return;
    try {
      localStorage.setItem(SELECTED_PAIR_STORAGE_KEY, JSON.stringify({
        id: Number(asset.id),
        symbol: asset.symbol,
      }));
    } catch (e) {}
  }

  function resolveInitialAsset(serverAsset) {
    const assets = getAvailableAssets();
    const serverValid = assets.find(asset => sameAsset(asset, serverAsset)) || serverAsset;
    if (explicitAssetRequested()) return explicitRequestedAsset() || serverValid;
    const saved = readSavedAsset();
    if (saved) return saved;
    return serverValid || assets[0];
  }

  function syncSelectedAssetDom(asset) {
    if (!asset) return;
    document.getElementById('asset-id-val').value = asset.id;
    document.getElementById('asset-payout-val').value = asset.payout;
    document.getElementById('base-price').value = asset.base;
    const digEl = document.getElementById('asset-digits-val');
    if (digEl) digEl.value = asset.digits;
    const flagHtml = renderAssetFlagsHtml(asset.symbol, asset.flag, asset.type);
    if (el.assetSymbol) el.assetSymbol.textContent = asset.symbol;
    if (el.assetPayout) el.assetPayout.textContent = '+' + asset.payout + '%';
    if (el.assetFlag) el.assetFlag.innerHTML = flagHtml;
    if (el.panelSymbol) el.panelSymbol.textContent = asset.symbol;
    if (el.panelPayout) el.panelPayout.textContent = asset.payout + '%';
    if (el.panelFlag) el.panelFlag.innerHTML = flagHtml;
    document.querySelectorAll('.asset-list-item').forEach(item => {
      const isCurrent = Number(item.dataset.id) === Number(asset.id) || item.dataset.symbol === asset.symbol;
      item.classList.toggle('selected', isCurrent);
      const existing = item.querySelector('.qx-selected-check');
      if (isCurrent && !existing) {
        const idWrap = item.querySelector('.qx-row-identity');
        if (idWrap) {
          const check = document.createElement('span');
          check.className = 'qx-selected-check';
          check.textContent = '✓';
          idWrap.appendChild(check);
        }
      } else if (!isCurrent && existing) {
        existing.remove();
      }
    });
  }

  // --- Initialize App ---
  function init() {
    // Read initial active asset from hidden inputs
    const initialId = parseInt(document.getElementById('asset-id-val')?.value || '1');
    const initialSymbol = el.assetSymbol ? el.assetSymbol.textContent.trim() : 'USD/NGN';
    const initialPayout = parseInt(document.getElementById('asset-payout-val')?.value || '85');
    const initialFlag = el.assetFlag ? el.assetFlag.textContent.trim() : '🇺🇸🇳🇬';
    const initialBase = parseFloat(document.getElementById('base-price')?.value || '1335.50');
    let initialDigits = parseInt(document.getElementById('asset-digits-val')?.value || '5');
    if (initialBase >= 500 || initialSymbol.toUpperCase().includes('BTC') || initialSymbol.toUpperCase().includes('ETH') || initialSymbol.toUpperCase().includes('GOLD') || initialSymbol.toUpperCase().includes('XAU')) {
      initialDigits = 2;
    } else if (initialBase >= 50 || initialSymbol.toUpperCase().includes('JPY')) {
      initialDigits = 3;
    }

    state.selectedAsset = {
      id: initialId,
      symbol: initialSymbol,
      payout: initialPayout,
      flag: initialFlag,
      base: initialBase,
      digits: initialDigits
    };
    if (window.INITIAL_ASSET_TABS && Array.isArray(window.INITIAL_ASSET_TABS) && window.INITIAL_ASSET_TABS.length > 0) {
      state.openTabs = window.INITIAL_ASSET_TABS.map(t => {
        const p = Number(t.base || 0);
        const s = (t.symbol || '').toUpperCase();
        let d = t.digits;
        if (p >= 500 || s.includes('BTC') || s.includes('ETH') || s.includes('GOLD') || s.includes('XAU')) d = 2;
        else if (p >= 50 || s.includes('JPY')) d = 3;
        return { ...t, digits: d };
      });
      const match = state.openTabs.find(t => t.id === state.selectedAsset.id || t.symbol === state.selectedAsset.symbol);
      if (match) {
        state.selectedAsset = match;
      } else {
        state.openTabs.push(state.selectedAsset);
      }
    } else {
      state.openTabs = [
        state.selectedAsset
      ];
    }

    const resolvedInitialAsset = resolveInitialAsset(state.selectedAsset);
    if (resolvedInitialAsset) {
      state.selectedAsset = resolvedInitialAsset;
      if (!state.openTabs.some(t => sameAsset(t, resolvedInitialAsset))) {
        state.openTabs.push(resolvedInitialAsset);
      }
      syncSelectedAssetDom(resolvedInitialAsset);
      if (!explicitAssetRequested()) {
        persistSelectedAsset(resolvedInitialAsset);
      }
    }

    const balEl = document.getElementById('account-balance');
    if (balEl) {
      const parsed = parseFloat(balEl.textContent.replace(/[^0-9.]/g, ''));
      if (!isNaN(parsed)) state.balance = parsed;
    }

    renderTabs();
    initChart();
    loadPriceData();
    bindEvents();
    startPriceEngine();
    connectLiveCryptoStream(state.selectedAsset?.symbol);
    updatePayoutCalculation();
    startActiveTradesMonitor();
    startLiveClock();
    startLiveTickerStream();
    startSentimentEngine();

    // Restore active running trades on page reload
    if (window.INITIAL_ACTIVE_TRADES && Array.isArray(window.INITIAL_ACTIVE_TRADES)) {
      window.INITIAL_ACTIVE_TRADES.forEach(t => {
        const tradeObj = {
          trade_id: t.trade_id,
          asset: t.asset,
          direction: t.direction,
          open_price: t.open_price,
          investment: t.investment,
          payout: t.payout,
          duration: t.duration,
          expires_at: t.expires_at,
          priceLine: null,
          beepedCount: 0,
        };
        state.activeTrades.push(tradeObj);
      });
      renderActiveTrades();
    }
  }

  // --- Render Top Asset Tabs ---
  function renderTabs() {
    if (!el.tabsBar) return;
    el.tabsBar.innerHTML = '';

    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.className = 'btn-add-asset-square';
    addBtn.id = 'btn-add-asset-tab';
    addBtn.textContent = '+';
    addBtn.title = 'Open new asset';
    addBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      state.assetPanelMode = 'add';
      const isOpen = el.assetPanel?.classList.toggle('open');
      addBtn.textContent = isOpen ? '✕' : '+';
    });
    el.tabsBar.appendChild(addBtn);

    state.openTabs.forEach(tab => {
      const isSelected = (tab.id === state.selectedAsset.id || tab.symbol === state.selectedAsset.symbol);
      const tabEl = document.createElement('div');
      tabEl.className = `dJ15T vXMlv qx-tab-pill qx-active-tab ${isSelected ? 'active AmO6b' : ''}`;
      tabEl.id = isSelected ? 'tab-active' : (tab.symbol || '');
      tabEl.dataset.symbol = tab.symbol;
      tabEl.title = tab.name || tab.symbol;
      tabEl.innerHTML = `
        <div class="ZyIJD">
          <div class="H4wMV">
            <div class="qHbv0">
              <span class="flag-icon">${renderAssetFlagsHtml(tab.symbol, tab.flag, tab.type)}</span>
              <div class="WRocw symbol-name">${tab.symbol}</div>
            </div>
            <div class="DyILR">
              <div class="ElyTP payout-pill-pct ${isSelected ? 'text-gold' : 'text-green'}">${tab.payout}%</div>
            </div>
            ${isSelected ? '<span class="tab-chevron">▼</span>' : ''}
            ${state.openTabs.length > 1 ? '<span class="tab-close-icon">✕</span>' : ''}
          </div>
        </div>
      `;

      tabEl.addEventListener('click', (e) => {
        e.stopPropagation();
        if (e.target.classList.contains('tab-close-icon')) {
          closeTab(tab.id);
          return;
        }
        if (!isSelected) {
          selectAsset(tab, false);
          el.assetPanel?.classList.remove('open');
          const ab = document.getElementById('btn-add-asset-tab');
          if (ab) ab.textContent = '+';
          return;
        }
        state.assetPanelMode = 'replace';
        const isOpen = el.assetPanel?.classList.toggle('open');
        const ab = document.getElementById('btn-add-asset-tab');
        if (ab) ab.textContent = isOpen ? '✕' : '+';
      });

      el.tabsBar.appendChild(tabEl);
    });
  }

  function closeTab(tabId) {
    if (state.openTabs.length <= 1) return;
    const closedIdx = state.openTabs.findIndex(t => t.id === tabId);
    state.openTabs = state.openTabs.filter(t => t.id !== tabId);
    if (state.selectedAsset?.id === tabId) {
      const nextTab = state.openTabs[Math.max(0, closedIdx - 1)] || state.openTabs[0];
      selectAsset(nextTab, false);
    } else {
      renderTabs();
    }
  }

  function selectAsset(asset, isNewTab = null) {
    const isExplicitAdd = (isNewTab === true) || (isNewTab === null && state.assetPanelMode === 'add');
    state.assetPanelMode = 'replace';

    const currentSymbol = state.selectedAsset ? state.selectedAsset.symbol : null;
    const currentId = state.selectedAsset ? state.selectedAsset.id : null;
    const existingIdx = state.openTabs.findIndex(t => t.id === asset.id || t.symbol === asset.symbol);
    const currentTabIdx = state.openTabs.findIndex(t => t.id === currentId || t.symbol === currentSymbol);

    let chosenAsset = asset;
    if (isExplicitAdd) {
      if (existingIdx !== -1) {
        chosenAsset = state.openTabs[existingIdx];
      } else {
        state.openTabs.push(asset);
        chosenAsset = asset;
      }
    } else {
      if (existingIdx !== -1 && existingIdx !== currentTabIdx) {
        chosenAsset = state.openTabs[existingIdx];
      } else if (currentTabIdx !== -1) {
        state.openTabs[currentTabIdx] = asset;
        chosenAsset = asset;
      } else {
        state.openTabs = [asset];
        chosenAsset = asset;
      }
    }

    let dig = chosenAsset.digits;
    const chBase = Number(chosenAsset.base || 0);
    const chSym = (chosenAsset.symbol || '').toUpperCase();
    if (chBase >= 500 || chSym.includes('BTC') || chSym.includes('ETH') || chSym.includes('GOLD') || chSym.includes('XAU')) {
      dig = 2;
    } else if (chBase >= 50 || chSym.includes('JPY')) {
      dig = 3;
    }
    chosenAsset.digits = dig;

    state.selectedAsset = chosenAsset;
    persistSelectedAsset(chosenAsset);
    document.getElementById('asset-id-val').value = chosenAsset.id;
    document.getElementById('asset-payout-val').value = chosenAsset.payout;
    document.getElementById('base-price').value = chosenAsset.base;
    const digEl = document.getElementById('asset-digits-val');
    if (digEl && typeof chosenAsset.digits === 'number') {
      digEl.value = chosenAsset.digits;
    }

    const flagHtml = renderAssetFlagsHtml(chosenAsset.symbol, chosenAsset.flag, chosenAsset.type);
    if (el.assetSymbol) el.assetSymbol.textContent = chosenAsset.symbol;
    if (el.assetPayout) el.assetPayout.textContent = '+' + chosenAsset.payout + '%';
    if (el.assetFlag) el.assetFlag.innerHTML = flagHtml;
    if (el.panelSymbol) el.panelSymbol.textContent = chosenAsset.symbol;
    if (el.panelPayout) el.panelPayout.textContent = chosenAsset.payout + '%';
    if (el.panelFlag) el.panelFlag.innerHTML = flagHtml;

    // Update checkmark in modal
    document.querySelectorAll('.asset-list-item').forEach(item => {
      const isCur = (parseInt(item.dataset.id) === chosenAsset.id || item.dataset.symbol === chosenAsset.symbol);
      item.classList.toggle('selected', isCur);
      let chk = item.querySelector('.qx-selected-check');
      if (isCur) {
        if (!chk) {
          const idWrap = item.querySelector('.qx-row-identity');
          if (idWrap) {
            chk = document.createElement('span');
            chk.className = 'qx-selected-check';
            chk.textContent = '✓';
            idWrap.appendChild(chk);
          }
        }
      } else if (chk) {
        chk.remove();
      }
    });

    if (state.pendingPriceLine && state.series) {
      try { state.series.removePriceLine(state.pendingPriceLine); } catch (e) {}
      state.pendingPriceLine = null;
    }

    renderTabs();
    createSeries();
    state.candles = [];
    state.currentPrice = chosenAsset.base;
    loadPriceData();
    connectLiveCryptoStream(chosenAsset.symbol);
    updatePayoutCalculation();
    el.assetPanel?.classList.remove('open');
    const ab = document.getElementById('btn-add-asset-tab');
    if (ab) ab.textContent = '+';
  }

  // --- Lightweight Charts Init ---
  function initChart() {
    const chartEl = document.getElementById('trade-chart');
    if (!chartEl || !window.LightweightCharts) return;

    state.chart = LightweightCharts.createChart(chartEl, {
      layout: {
        background: { color: '#1c1f2d' },
        textColor: '#8e9aa8',
        fontFamily: "'Inter', sans-serif",
        fontSize: 10,
      },
      grid: {
        vertLines: { color: '#242a3c' },
        horzLines: { color: '#242a3c' },
      },
      crosshair: {
        mode: LightweightCharts.CrosshairMode.Normal,
      },
      rightPriceScale: {
        borderColor: '#293246',
        textColor: '#8e9aa8',
        autoScale: true,
        scaleMargins: {
          top: 0.16,
          bottom: 0.16,
        },
        alignLabels: true,
      },
      timeScale: {
        borderColor: '#293246',
        timeVisible: true,
        secondsVisible: state.timeframeSec <= 60,
      },
      watermark: { visible: false },
    });

    createSeries();

    if (state.chart.timeScale) {
      state.chart.timeScale().subscribeVisibleLogicalRangeChange((range) => {
        updateChartTradesOverlay();
        if (range && range.from < 25 && !isLoadingHistory && state.candles.length > 0) {
          const oldestCandle = state.candles[0];
          if (oldestCandle && oldestCandle.time) {
            loadPriceData(true, oldestCandle.time);
          }
        }
      });
    }

    const resizeObserver = new ResizeObserver(() => {
      if (state.chart) {
        state.chart.applyOptions({
          width: chartEl.clientWidth,
          height: chartEl.clientHeight,
        });
        updateChartTradesOverlay();
      }
    });
    resizeObserver.observe(chartEl);
    window.addEventListener('resize', () => updateChartTradesOverlay());
  }

  function createSeries() {
    if (!state.chart) return;
    if (state.series) {
      try { state.chart.removeSeries(state.series); } catch (e) {}
      state.series = null;
    }

    const baseVal = state.selectedAsset ? Number(state.selectedAsset.base) : 1.0;
    const prec = getPrecision(baseVal);
    const minMove = Math.pow(10, -prec);

    const commonSeriesOpts = {
      priceFormat: {
        type: 'price',
        precision: prec,
        minMove: minMove,
      },
      priceScaleId: 'right',
    };

    if (state.chartType === 'area') {
      state.series = state.chart.addAreaSeries({
        ...commonSeriesOpts,
        topColor: 'rgba(16, 160, 85, 0.35)',
        bottomColor: 'rgba(16, 160, 85, 0.0)',
        lineColor: '#10a055',
        lineWidth: 2,
      });
    } else {
      state.series = state.chart.addCandlestickSeries({
        ...commonSeriesOpts,
        upColor: '#10a055',
        downColor: '#e85b4e',
        borderUpColor: '#10a055',
        borderDownColor: '#e85b4e',
        wickUpColor: '#10a055',
        wickDownColor: '#e85b4e',
      });
    }

    try {
      state.chart.priceScale('right').applyOptions({
        autoScale: true,
        scaleMargins: {
          top: 0.12,
          bottom: 0.12,
        },
      });
    } catch (e) {}

    refreshChartMarkers();
  }

  function refreshChartMarkers() {
    if (!state.series) return;
    try {
      state.series.setMarkers([]);
    } catch (e) {}
  }

  // --- Dynamic Traders Sentiment Fluctuations (Exact Quotex) ---
  let currentSentiment = 57; // 57% red (down), 43% green (up)
  function updateSentiment(targetVal) {
    if (typeof targetVal === 'number') {
      currentSentiment = Math.max(30, Math.min(72, targetVal));
    } else {
      const drift = (Math.random() - 0.49) * 3;
      currentSentiment = Math.max(35, Math.min(68, Math.round(currentSentiment + drift)));
    }
    const downPct = currentSentiment;
    const upPct = 100 - downPct;

    const topValEl = document.getElementById('sent-top-val');
    const btmValEl = document.getElementById('sent-bottom-val');
    const redLine = document.getElementById('sent-red-line');
    const greenLine = document.getElementById('sent-green-line');

    if (topValEl) topValEl.textContent = `${downPct}%`;
    if (btmValEl) btmValEl.textContent = `${upPct}%`;
    if (redLine) redLine.style.height = `${downPct}%`;
    if (greenLine) greenLine.style.height = `${upPct}%`;
  }

  function startSentimentEngine() {
    updateSentiment(57);
    setInterval(() => {
      updateSentiment();
    }, 2000);
  }

  // --- Dynamic Decimal Precision & Price Formatting ---
  const serverOffset = (typeof window.SERVER_TIMESTAMP === 'number') 
    ? (window.SERVER_TIMESTAMP * 1000 - Date.now()) 
    : 0;

  function getNowSec() {
    return Math.floor((Date.now() + serverOffset) / 1000);
  }

  function getSymbolSeed(symbol) {
    const sym = String(symbol || '').toUpperCase().trim();
    let hash = 0;
    for (let i = 0; i < sym.length; i++) {
      hash = ((hash * 31) + sym.charCodeAt(i)) & 0x7FFFFFFF;
    }
    return hash % 1000000;
  }

  function deterministicHash(key, slot = 0) {
    let hash = 0;
    const str = `${key}_${slot}`;
    for (let i = 0; i < str.length; i++) {
      hash = ((hash << 5) - hash) + str.charCodeAt(i);
      hash |= 0;
    }
    const val = Math.abs(hash);
    return ((val % 2000000) / 1000000.0) - 1.0;
  }

  function deterministicNoise(seed, slot) {
    let n = (seed + Math.imul(slot, 374761393)) & 0x7FFFFFFF;
    n = Math.imul(n ^ (n >>> 13), 1274126177) & 0x7FFFFFFF;
    n = (n ^ (n >>> 16)) & 0x7FFFFFFF;
    return ((n % 2000000) / 1000000.0) - 1.0;
  }

  function smoothNoise(seed, t, period = 8.0) {
    const pos = t / period;
    const k = Math.floor(pos);
    const f = pos - k;
    const s = f * f * f * (f * (f * 6.0 - 15.0) + 10.0);
    const v0 = deterministicNoise(seed, k);
    const v1 = deterministicNoise(seed, k + 1);
    return v0 + s * (v1 - v0);
  }

  function getDeterministicPrice(symbol, basePrice, timestamp) {
    const p = Math.abs(Number(basePrice) || 1.0);
    const prec = getPrecision(p);
    const pip = Math.pow(10, -prec);
    const vol = p * 0.00030;

    const symKey = String(symbol || '').toUpperCase().trim();
    const seed = getSymbolSeed(symKey);
    const t = Number(timestamp);
    const twoPi = 2 * Math.PI;

    // --- Multi-Year Macro & Secular Waves (4-5 Year Historical Structure) ---
    const w5y = Math.sin(((t + ((seed * 1337) % 157680000)) / 157680000.0) * twoPi) * 45.0;
    const w2y = Math.cos(((t + ((seed * 773) % 63072000)) / 63072000.0) * twoPi) * 28.0;
    const w1y = Math.sin(((t + ((seed * 521) % 31536000)) / 31536000.0) * twoPi) * 18.0;
    const w3m = Math.cos(((t + ((seed * 317) % 7776000)) / 7776000.0) * twoPi) * 10.0;
    const w1m = Math.sin(((t + ((seed * 199) % 2592000)) / 2592000.0) * twoPi) * 6.0;
    const w1w = Math.cos(((t + ((seed * 97) % 604800)) / 604800.0) * twoPi) * 4.0;
    const w1d = Math.sin(((t + ((seed * 43) % 86400)) / 86400.0) * twoPi) * 2.5;

    // --- Intraday Intermediate Waves ---
    const w4h = Math.cos(((t + (seed % 14400)) / 14400.0) * twoPi) * 1.5;
    const w1h = Math.sin(((t + (seed % 3600)) / 3600.0) * twoPi) * 0.9;
    const w15m = Math.sin((t + (seed % 997)) / 900.0) * 0.6;
    const w4m = Math.sin((t + (seed % 499)) / 240.0) * 0.4;
    const w75s = Math.cos((t + (seed % 251)) / 75.0) * 0.25;

    // --- Multi-Scale Smooth Organic Noise (C2-Continuous) ---
    const nDay = smoothNoise(seed + 77, t, 86400.0) * 4.0;
    const nHr  = smoothNoise(seed + 33, t, 3600.0) * 1.5;
    const nMin = smoothNoise(seed, t, 14.0) * 0.35;
    const nSec = smoothNoise(seed + 101, t, 4.5) * 0.15;

    const totalWave = (w5y + w2y + w1y + w3m + w1m + w1w + w1d + w4h + w1h + w15m + w4m + w75s + nDay + nHr + nMin + nSec) * vol;
    return +Math.max(pip, p + totalWave).toFixed(prec);
  }

  function getPrecision(price) {
    const sym = (state.selectedAsset?.symbol || '').toUpperCase();
    const p = Math.abs(Number(price) || (state.selectedAsset ? Number(state.selectedAsset.base) : 0));

    // High value assets like BTC, ETH, Gold, Indices or any asset with price >= 500 MUST be 2 decimals
    if (p >= 500 || sym.includes('BTC') || sym.includes('ETH') || sym.includes('GOLD') || sym.includes('XAU') || sym.includes('SOL') || sym.includes('BNB') || sym.includes('DOW') || sym.includes('SPX') || sym.includes('NDX')) {
      return 2;
    }
    // JPY pairs, indices, PKR, INR, IDR or assets >= 50
    if (p >= 50 || sym.includes('JPY') || sym.includes('INR') || sym.includes('PKR') || sym.includes('IDR')) {
      return 3;
    }
    // Explicit asset digits for forex pairs (e.g. EUR/USD = 5)
    if (state.selectedAsset && typeof state.selectedAsset.digits === 'number' && state.selectedAsset.digits > 0) {
      if (p >= 25) return Math.min(state.selectedAsset.digits, 3);
      return Math.min(state.selectedAsset.digits, 5);
    }
    if (p >= 0.1) return 5;
    return 6;
  }

  function formatPrice(price) {
    const prec = getPrecision(price);
    return Number(price).toFixed(prec);
  }

  function getSmartCandleCount(tfSec) {
    const tf = tfSec || 60;
    if (tf >= 86400) return 1825; // 5 full years of daily candles
    if (tf >= 14400) return 2000; // ~11 months of 4-hour candles
    if (tf >= 3600)  return 1500; // ~2 months of 1-hour candles
    if (tf >= 1800)  return 1200; // ~25 days of 30-min candles
    if (tf >= 900)   return 1200; // ~12.5 days of 15-min candles
    if (tf >= 300)   return 1000; // ~3.5 days of 5-min candles
    if (tf >= 60)    return 1000; // ~16.6 hours of 1-min candles
    return 800;                    // 800 micro candles
  }

  function getDefaultVisibleCandleCount() {
    const chartEl = document.getElementById('trade-chart');
    const width = chartEl?.clientWidth || window.innerWidth || 800;
    const responsiveCount = Math.round(width / DEFAULT_VISIBLE_CANDLE_WIDTH);
    return Math.max(DEFAULT_VISIBLE_CANDLES_MIN, Math.min(DEFAULT_VISIBLE_CANDLES_MAX, responsiveCount));
  }

  function applyDefaultVisibleRange() {
    if (!state.chart || !state.candles.length) return;
    const visibleCount = Math.min(getDefaultVisibleCandleCount(), state.candles.length);
    const lastIndex = state.candles.length - 1;
    try {
      state.chart.timeScale().setVisibleLogicalRange({
        from: Math.max(0, lastIndex - visibleCount + 1),
        to: lastIndex + DEFAULT_RIGHT_OFFSET_CANDLES,
      });
    } catch (e) {}
  }

  let isLoadingHistory = false;

  // --- Load Candles & Price Engine ---
  function loadPriceData(isPrepend = false, customEndTime = null) {
    const symbol = state.selectedAsset ? state.selectedAsset.symbol : 'EUR/USD (OTC)';
    const assetId = state.selectedAsset ? state.selectedAsset.id : 1;
    const reqCount = isPrepend ? 500 : getSmartCandleCount(state.timeframeSec);
    let url = `/trade/price-data?symbol=${encodeURIComponent(symbol)}&asset_id=${assetId}&timeframe=${state.timeframeSec}&count=${reqCount}`;
    if (customEndTime) {
      url += `&to_time=${customEndTime}`;
    }

    if (isPrepend) isLoadingHistory = true;
    const requestSeq = ++state.marketRequestSeq;

    fetch(url)
      .then(r => r.json())
      .then(candles => {
        if (requestSeq !== state.marketRequestSeq) return;
        if (Array.isArray(candles) && candles.length > 0) {
          const prec = getPrecision(candles[candles.length - 1].close);
          const mapped = candles.map(c => ({
            time: c.time,
            open: +Number(c.open).toFixed(prec),
            high: +Number(c.high).toFixed(prec),
            low: +Number(c.low).toFixed(prec),
            close: +Number(c.close).toFixed(prec),
          }));

          if (isPrepend && state.candles.length > 0) {
            const oldestExistingTime = state.candles[0].time;
            const newOlderCandles = mapped.filter(c => c.time < oldestExistingTime);
            if (newOlderCandles.length > 0) {
              state.candles = [...newOlderCandles, ...state.candles];
              updateSeriesData(false);
            }
          } else {
            state.candles = mapped;
            updateSeriesData(true);
            state.currentPrice = state.candles[state.candles.length - 1].close;
            updatePriceLabel();
          }
        }
      })
      .catch(() => {
        // Backend market data is authoritative. Keep the last rendered candles
        // instead of synthesizing a divergent local market path.
      })
      .finally(() => {
        if (isPrepend) {
          setTimeout(() => { isLoadingHistory = false; }, 800);
        }
      });
  }

  function syncLatestBackendCandles() {
    if (!state.selectedAsset || !state.candles.length || document.hidden) return;

    const symbol = state.selectedAsset.symbol || 'EUR/USD (OTC)';
    const assetId = state.selectedAsset.id || 1;
    const url = `/trade/price-data?symbol=${encodeURIComponent(symbol)}&asset_id=${assetId}&timeframe=${state.timeframeSec}&count=5`;
    const requestSeq = state.marketRequestSeq;

    fetch(url)
      .then(r => r.json())
      .then(candles => {
        if (requestSeq !== state.marketRequestSeq) return;
        if (!Array.isArray(candles) || candles.length === 0) return;
        const prec = getPrecision(candles[candles.length - 1].close);
        const mapped = candles.map(c => ({
          time: c.time,
          open: +Number(c.open).toFixed(prec),
          high: +Number(c.high).toFixed(prec),
          low: +Number(c.low).toFixed(prec),
          close: +Number(c.close).toFixed(prec),
        }));

        const byTime = new Map(state.candles.map(c => [c.time, c]));
        mapped.forEach(candle => byTime.set(candle.time, candle));
        state.candles = Array.from(byTime.values()).sort((a, b) => a.time - b.time);
        const maxCandles = getSmartCandleCount(state.timeframeSec);
        if (state.candles.length > maxCandles) {
          state.candles = state.candles.slice(state.candles.length - maxCandles);
        }

        state.currentPrice = state.candles[state.candles.length - 1].close;
        updateSeriesData(false);
        updatePriceLabel();
      })
      .catch(() => {});
  }

  function startBackendMarketSync() {
    if (state.marketSyncTimer) clearInterval(state.marketSyncTimer);
    state.marketSyncTimer = setInterval(syncLatestBackendCandles, 1000);
  }

  function generateLocalData() {
    const base = (state.selectedAsset && Number(state.selectedAsset.base) > 0)
      ? Number(state.selectedAsset.base)
      : 1.0854;
    const prec = getPrecision(base);
    const pip = Math.pow(10, -prec);
    const sym = state.selectedAsset?.symbol || 'EUR/USD (OTC)';
    const seed = getSymbolSeed(sym);
    const now = getNowSec();
    const tf = Math.max(5, state.timeframeSec || 60);
    const count = getSmartCandleCount(tf);
    const currentCandleStart = Math.floor(now / tf) * tf;
    const candles = [];
    let prevClose = null;

    for (let i = count - 1; i >= 0; i--) {
      const cTime = currentCandleStart - (i * tf);
      const o = (prevClose !== null) ? prevClose : getDeterministicPrice(sym, base, cTime);
      const c = (i === 0) ? getDeterministicPrice(sym, base, now) : getDeterministicPrice(sym, base, cTime + tf);

      const p1 = getDeterministicPrice(sym, base, cTime + tf * 0.20);
      const p2 = getDeterministicPrice(sym, base, cTime + tf * 0.45);
      const p3 = getDeterministicPrice(sym, base, cTime + tf * 0.70);
      const p4 = getDeterministicPrice(sym, base, cTime + tf * 0.88);

      const bodyMax = Math.max(o, c);
      const bodyMin = Math.min(o, c);
      const body = bodyMax - bodyMin;

      const trajHigh = Math.max(bodyMax, p1, p2, p3, p4);
      const trajLow  = Math.min(bodyMin, p1, p2, p3, p4);

      const uHash = Math.abs(deterministicNoise(seed, Math.floor(cTime * 3 + 1)));
      const lHash = Math.abs(deterministicNoise(seed, Math.floor(cTime * 5 + 3)));

      const wickUnit = Math.max(pip * 1.0, Math.min(pip * 3.5, body * 0.22));
      const extraUpper = +(wickUnit * (0.2 + 0.8 * uHash)).toFixed(prec);
      const extraLower = +(wickUnit * (0.2 + 0.8 * lHash)).toFixed(prec);

      let h = +Math.max(bodyMax, trajHigh + extraUpper).toFixed(prec);
      let l = +Math.max(pip, Math.min(bodyMin, trajLow - extraLower)).toFixed(prec);

      if (h < bodyMax) h = bodyMax;
      if (l > bodyMin) l = bodyMin;

      candles.push({
        time: cTime,
        open: o,
        high: h,
        low: l,
        close: c
      });
      prevClose = c;
    }

    state.candles = candles;
    state.currentPrice = candles[candles.length - 1].close;
    updateSeriesData(true);
    updatePriceLabel();
  }

  function updateSeriesData(shouldFit = true) {
    if (!state.series) return;
    if (state.chartType === 'area') {
      const areaData = state.candles.map(c => ({ time: c.time, value: c.close }));
      state.series.setData(areaData);
    } else {
      state.series.setData(state.candles);
    }
    if (shouldFit) {
      applyDefaultVisibleRange();
    }
    try {
      state.chart.priceScale('right').applyOptions({ autoScale: true });
    } catch (e) {}
    updateIndicators();
  }

  // --- Technical Indicators Computation (SMA, EMA, Bollinger Bands) ---
  function updateIndicators() {
    if (!state.chart || !state.candles.length) return;

    // 1. SMA (10)
    if (state.indicators.sma) {
      if (!state.smaSeries) {
        state.smaSeries = state.chart.addLineSeries({
          color: '#2962ff',
          lineWidth: 2,
          title: 'SMA 10',
        });
      }
      const period = 10;
      const smaData = [];
      for (let i = period - 1; i < state.candles.length; i++) {
        let sum = 0;
        for (let j = 0; j < period; j++) sum += state.candles[i - j].close;
        smaData.push({ time: state.candles[i].time, value: +(sum / period).toFixed(5) });
      }
      state.smaSeries.setData(smaData);
    } else if (state.smaSeries) {
      state.chart.removeSeries(state.smaSeries);
      state.smaSeries = null;
    }

    // 2. EMA (20)
    if (state.indicators.ema) {
      if (!state.emaSeries) {
        state.emaSeries = state.chart.addLineSeries({
          color: '#ffd600',
          lineWidth: 2,
          title: 'EMA 20',
        });
      }
      const period = 20;
      const k = 2 / (period + 1);
      const emaData = [];
      if (state.candles.length >= period) {
        let prevEma = 0;
        for (let i = 0; i < period; i++) prevEma += state.candles[i].close;
        prevEma /= period;
        emaData.push({ time: state.candles[period - 1].time, value: +prevEma.toFixed(5) });
        for (let i = period; i < state.candles.length; i++) {
          const ema = (state.candles[i].close - prevEma) * k + prevEma;
          emaData.push({ time: state.candles[i].time, value: +ema.toFixed(5) });
          prevEma = ema;
        }
      }
      state.emaSeries.setData(emaData);
    } else if (state.emaSeries) {
      state.chart.removeSeries(state.emaSeries);
      state.emaSeries = null;
    }

    // 3. Bollinger Bands (20, 2)
    if (state.indicators.bollinger) {
      if (!state.bbUpperSeries) {
        state.bbUpperSeries = state.chart.addLineSeries({
          color: '#00e5ff',
          lineWidth: 1,
          lineStyle: LightweightCharts.LineStyle.Dotted,
          title: 'BB Upper',
        });
      }
      if (!state.bbLowerSeries) {
        state.bbLowerSeries = state.chart.addLineSeries({
          color: '#00e5ff',
          lineWidth: 1,
          lineStyle: LightweightCharts.LineStyle.Dotted,
          title: 'BB Lower',
        });
      }
      const period = 20;
      const upperData = [];
      const lowerData = [];
      if (state.candles.length >= period) {
        for (let i = period - 1; i < state.candles.length; i++) {
          let sum = 0;
          for (let j = 0; j < period; j++) sum += state.candles[i - j].close;
          const mean = sum / period;
          let variance = 0;
          for (let j = 0; j < period; j++) variance += Math.pow(state.candles[i - j].close - mean, 2);
          const stdDev = Math.sqrt(variance / period);
          upperData.push({ time: state.candles[i].time, value: +(mean + 2 * stdDev).toFixed(5) });
          lowerData.push({ time: state.candles[i].time, value: +(mean - 2 * stdDev).toFixed(5) });
        }
      }
      state.bbUpperSeries.setData(upperData);
      state.bbLowerSeries.setData(lowerData);
    } else {
      if (state.bbUpperSeries) {
        state.chart.removeSeries(state.bbUpperSeries);
        state.bbUpperSeries = null;
      }
      if (state.bbLowerSeries) {
        state.chart.removeSeries(state.bbLowerSeries);
        state.bbLowerSeries = null;
      }
    }
  }

  let livePriceSocket = null;
  function connectLiveCryptoStream(symbol) {
    if (livePriceSocket) {
      try { livePriceSocket.close(); } catch (e) {}
      livePriceSocket = null;
    }
    // All assets (forex, crypto OTC, commodities) use the synchronized organic OTC price engine
  }

  function pushPriceUpdate(newClose) {
    if (!state.candles.length || !state.series) return;
    const last = state.candles[state.candles.length - 1];
    const now = getNowSec();
    const prec = getPrecision(newClose);
    const tf = Math.max(5, state.timeframeSec || 60);
    const currentCandleStart = Math.floor(now / tf) * tf;

    if (currentCandleStart > last.time) {
      const newCandle = {
        time: currentCandleStart,
        open: last.close,
        high: +Math.max(last.close, newClose).toFixed(prec),
        low: +Math.min(last.close, newClose).toFixed(prec),
        close: newClose,
      };
      state.candles.push(newCandle);
      if (state.chartType === 'area') {
        state.series.update({ time: currentCandleStart, value: newClose });
      } else {
        state.series.update(newCandle);
      }
      updateIndicators();
    } else {
      last.high = +Math.max(last.high, newClose).toFixed(prec);
      last.low  = +Math.min(last.low, newClose).toFixed(prec);
      last.close = newClose;
      if (state.chartType === 'area') {
        state.series.update({ time: last.time, value: newClose });
      } else {
        state.series.update(last);
      }
    }

    state.currentPrice = newClose;
    updatePriceLabel();
    updateActiveTradesRealtime();
  }

  // High-frequency Real-time Price Engine with Synchronized Smooth Ticks & Candle Formation
  function startPriceEngine() {
    if (state.priceTimer) clearInterval(state.priceTimer);

    state.priceTimer = setInterval(() => {
      syncLatestBackendCandles();
    }, 1000);
  }

  function updatePriceLabel() {
    const prec = getPrecision(state.currentPrice);
    const formatted = state.currentPrice.toFixed(prec);
    const lbl = document.getElementById('current-price-label');
    if (lbl) lbl.textContent = formatted;

    const curQuote = document.getElementById('pending-cur-quote-lbl');
    if (curQuote) curQuote.textContent = formatted;
  }

  // --- Duration & Expiry Switchers ---
  const DURATIONS = [5, 10, 15, 30, 60, 120, 180, 300, 600];

  function setDuration(secs) {
    state.durationSeconds = secs;
    renderTimeDisplay();
  }

  function renderTimeDisplay() {
    if (!el.timeDisplay) return;
    if (state.timeMode === 'duration') {
      const m = Math.floor(state.durationSeconds / 60);
      const s = state.durationSeconds % 60;
      el.timeDisplay.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    } else {
      const targetTime = new Date(Date.now() + state.durationSeconds * 1000);
      el.timeDisplay.textContent = targetTime.toTimeString().split(' ')[0];
    }
  }

  document.getElementById('btn-switch-time-mode')?.addEventListener('click', () => {
    state.timeMode = state.timeMode === 'duration' ? 'clock' : 'duration';
    if (el.timeModeLabel) el.timeModeLabel.textContent = state.timeMode === 'duration' ? 'Timer' : 'Expiration';
    renderTimeDisplay();
  });

  document.getElementById('time-minus')?.addEventListener('click', () => {
    const idx = DURATIONS.indexOf(state.durationSeconds);
    if (idx > 0) setDuration(DURATIONS[idx - 1]);
    else setDuration(Math.max(5, state.durationSeconds - 5));
  });

  document.getElementById('time-plus')?.addEventListener('click', () => {
    const idx = DURATIONS.indexOf(state.durationSeconds);
    if (idx >= 0 && idx < DURATIONS.length - 1) setDuration(DURATIONS[idx + 1]);
    else setDuration(state.durationSeconds + 30);
  });

  // --- Investment Amount & Percent Mode ---
  function updatePayoutCalculation() {
    const payoutPct = state.selectedAsset ? state.selectedAsset.payout : 82;
    let actualInvestment = state.investment;
    if (state.investMode === 'percent') {
      const userBalance = parseFloat(el.balanceDisplay?.textContent.replace(/[^0-9.]/g, '') || '89800');
      actualInvestment = Math.max(1, +(userBalance * (state.investment / 100)).toFixed(2));
    }

    const payout = +(actualInvestment * (1 + payoutPct / 100)).toFixed(2);

    if (el.payoutDisplay) el.payoutDisplay.textContent = payout.toFixed(2) + ' $';
    const gainBadge = document.getElementById('asset-gain');
    if (gainBadge) gainBadge.textContent = '+' + payout.toFixed(2) + ' $';
    const investPreview = document.getElementById('invest-preview-display');
    if (investPreview) investPreview.textContent = actualInvestment + ' $';
  }

  function startLiveClock() {
    const clockEl = document.getElementById('live-clock');
    if (!clockEl) return;
    setInterval(() => {
      const now = new Date();
      clockEl.textContent = now.toTimeString().split(' ')[0];
    }, 1000);
  }

  function startLiveTickerStream() {
    const tickerEl = document.getElementById('live-ticker-text');
    if (!tickerEl) return;
    setInterval(() => {
      if (!state.selectedAsset || !state.currentPrice) return;
      const price = (state.currentPrice + (Math.random() - 0.5) * 0.00004).toFixed(5);
      tickerEl.textContent = `Trade opened with price: ${price} ${state.selectedAsset.symbol}`;
    }, 4000);
  }

  document.getElementById('btn-switch-invest-mode')?.addEventListener('click', () => {
    state.investMode = state.investMode === 'fixed' ? 'percent' : 'fixed';
    if (el.investModeLabel) el.investModeLabel.textContent = state.investMode === 'fixed' ? 'Investment' : 'Investment %';
    if (el.investPrefix) el.investPrefix.textContent = state.investMode === 'fixed' ? '$' : '%';
    state.investment = state.investMode === 'fixed' ? 100 : 2;
    if (el.investmentInput) el.investmentInput.value = state.investment;
    updatePayoutCalculation();
  });

  document.getElementById('invest-minus')?.addEventListener('click', () => {
    state.investment = Math.max(1, state.investment - (state.investMode === 'percent' ? 1 : 5));
    if (el.investmentInput) el.investmentInput.value = state.investment;
    updatePayoutCalculation();
  });

  document.getElementById('invest-plus')?.addEventListener('click', () => {
    state.investment = state.investment + (state.investMode === 'percent' ? 1 : 10);
    if (el.investmentInput) el.investmentInput.value = state.investment;
    updatePayoutCalculation();
  });

  el.investmentInput?.addEventListener('input', () => {
    state.investment = parseFloat(el.investmentInput.value) || 1;
    updatePayoutCalculation();
  });

  // --- Multi-Trade Placement & Execution ---
  function placeTrade(direction) {
    const assetId = state.selectedAsset ? state.selectedAsset.id : 1;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    let investVal = state.investment;
    if (state.investMode === 'percent') {
      const userBalance = parseFloat(el.balanceDisplay?.textContent.replace(/[^0-9.]/g, '') || '10000');
      investVal = Math.max(1, +(userBalance * (state.investment / 100)).toFixed(2));
    }

    soundContext.playTradePlaced();

    fetch('/trade/place', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        asset_id: assetId,
        direction: direction,
        investment: investVal,
        duration_seconds: state.durationSeconds,
        open_price: state.currentPrice,
      }),
    })
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          showToast('Error', data.error, 'loss');
          return;
        }

        if (data.balance !== undefined && el.balanceDisplay) {
          el.balanceDisplay.textContent = '$' + parseFloat(data.balance).toFixed(2);
        }

        const tradeObj = {
          trade_id: data.trade_id,
          asset: state.selectedAsset,
          direction: direction,
          open_price: data.open_price,
          investment: investVal,
          payout: data.payout,
          duration: state.durationSeconds,
          expires_at: Date.now() + state.durationSeconds * 1000,
          candleTime: (state.candles && state.candles.length) ? state.candles[state.candles.length - 1].time : getNowSec(),
          priceLine: null,
          beepedCount: 0,
        };

        // Show beginning/end guide lines on chart
        document.getElementById('trade-marker-lines-overlay')?.classList.add('active');

        state.activeTrades.push(tradeObj);
        renderActiveTrades();
        updateChartTradesOverlay();

        // Floating Green Deal Opened Toast (Exact Screenshot 4)
        const toastEl = document.getElementById('chart-trade-toast');
        const toastText = document.getElementById('toast-trade-text');
        if (toastEl && toastText) {
          toastText.textContent = `Trade opened with price: ${formatPrice(data.open_price)} ${state.selectedAsset.symbol}`;
          toastEl.style.display = 'flex';
          setTimeout(() => { toastEl.style.display = 'none'; }, 5000);
        }

        showToast(
          'Deal Placed',
          `${direction === 'up' ? '↑ UP' : '↓ DOWN'} on ${state.selectedAsset.symbol} for $${investVal}`,
          'info'
        );
      })
      .catch(() => showToast('Error', 'Failed to place trade. Try again.', 'loss'));
  }

  el.btnUp?.addEventListener('click', () => placeTrade('up'));
  el.btnDown?.addEventListener('click', () => placeTrade('down'));

  // --- Pending Trade Modal Window (Screenshot 2 & 3) ---
  function updatePendingPriceLine(price) {
    if (!state.series) return;
    if (state.pendingPriceLine) {
      try { state.series.removePriceLine(state.pendingPriceLine); } catch (e) {}
    }
    state.pendingPriceLine = state.series.createPriceLine({
      price: parseFloat(price) || state.currentPrice,
      color: '#026FD3',
      lineWidth: 1,
      lineStyle: LightweightCharts.LineStyle.Dashed,
      axisLabelVisible: true,
      title: 'Order Quote',
    });
  }

  window.togglePendingWindow = function(show) {
    const modal = document.getElementById('pending-trade-popup');
    const overlay = document.getElementById('trade-marker-lines-overlay');
    const btnUp = document.getElementById('btn-up');
    const btnDown = document.getElementById('btn-down');

    if (modal) {
      modal.style.display = show ? 'flex' : 'none';
      if (show) {
        if (btnUp) btnUp.classList.add('disabled-pending');
        if (btnDown) btnDown.classList.add('disabled-pending');
        if (overlay) overlay.classList.add('active');

        const curQuote = document.getElementById('pending-cur-quote-lbl');
        const quoteInp = document.getElementById('pending-quote-input');
        if (curQuote && state.currentPrice) curQuote.textContent = formatPrice(state.currentPrice);
        if (quoteInp && state.currentPrice) {
          quoteInp.value = formatPrice(state.currentPrice);
          updatePendingPriceLine(state.currentPrice);
        }

        const curTime = document.getElementById('pending-cur-time-lbl');
        if (curTime) {
          const now = new Date();
          curTime.textContent = now.toTimeString().split(' ')[0];
        }
      } else {
        if (btnUp) btnUp.classList.remove('disabled-pending');
        if (btnDown) btnDown.classList.remove('disabled-pending');
        if (state.activeTrades.length === 0 && overlay) overlay.classList.remove('active');
        if (state.pendingPriceLine && state.series) {
          try { state.series.removePriceLine(state.pendingPriceLine); } catch (e) {}
          state.pendingPriceLine = null;
        }
      }
    }
  };

  document.getElementById('pending-quote-input')?.addEventListener('input', (e) => {
    updatePendingPriceLine(e.target.value);
  });

  window.switchPendingTab = function(tab) {
    const tabQuote = document.getElementById('tab-pending-quote');
    const tabTime = document.getElementById('tab-pending-time');
    const bodyQuote = document.getElementById('pending-body-quote');
    const bodyTime = document.getElementById('pending-body-time');

    if (tab === 'quote') {
      tabQuote?.classList.add('active');
      tabTime?.classList.remove('active');
      if (bodyQuote) bodyQuote.style.display = 'block';
      if (bodyTime) bodyTime.style.display = 'none';
    } else {
      tabTime?.classList.add('active');
      tabQuote?.classList.remove('active');
      if (bodyTime) bodyTime.style.display = 'block';
      if (bodyQuote) bodyQuote.style.display = 'none';
    }
  };

  let pendingInvestVal = 10;
  let pendingInvestMode = 'percent';

  window.adjustPendingAmt = function(delta) {
    pendingInvestVal = Math.max(1, pendingInvestVal + delta);
    const suffix = pendingInvestMode === 'percent' ? ' %' : ' $';
    const val1 = document.getElementById('pending-invest-val');
    const val2 = document.getElementById('pending-invest-time-val');
    if (val1) val1.textContent = pendingInvestVal + suffix;
    if (val2) val2.textContent = pendingInvestVal + suffix;
  };

  window.togglePendingMode = function() {
    pendingInvestMode = pendingInvestMode === 'percent' ? 'fixed' : 'percent';
    const suffix = pendingInvestMode === 'percent' ? ' %' : ' $';
    const val1 = document.getElementById('pending-invest-val');
    const val2 = document.getElementById('pending-invest-time-val');
    if (val1) val1.textContent = pendingInvestVal + suffix;
    if (val2) val2.textContent = pendingInvestVal + suffix;
  };

  window.submitPendingOrder = function(dir) {
    placeTrade(dir);
    window.togglePendingWindow(false);
    const chk = document.getElementById('pending-checkbox');
    if (chk) chk.checked = false;
  };

  // --- Quotex Exact On-Chart Active Trade Rays & Countdown Pills (Image 1 Exact) ---
  function updateChartTradesOverlay() {
    const container = document.getElementById('chart-active-trades-container');
    const overlay = document.getElementById('trade-marker-lines-overlay');
    if (!container || !state.series || !state.chart) return;

    if (!state.activeTrades || state.activeTrades.length === 0) {
      container.innerHTML = '';
      if (overlay && (!document.getElementById('pending-checkbox')?.checked)) {
        overlay.classList.remove('active');
      }
      return;
    }

    if (overlay) overlay.classList.add('active');

    const chartWidth = container.clientWidth || 800;
    const beginLineEl = document.querySelector('.trade-guide-line.line-begin');
    const endX = beginLineEl ? beginLineEl.offsetLeft : Math.round(chartWidth * 0.65);
    const now = Date.now();
    let minRemaining = null;

    state.activeTrades.forEach(trade => {
      const isWin = (trade.direction === 'up' && state.currentPrice >= trade.open_price) ||
                    (trade.direction === 'down' && state.currentPrice <= trade.open_price);
      const statusClass = isWin ? 'win' : 'loss';

      let item = document.getElementById(`chart-trade-${trade.trade_id}`);
      if (!item) {
        item = document.createElement('div');
        item.id = `chart-trade-${trade.trade_id}`;
        item.className = 'chart-trade-item';
        item.innerHTML = `
          <div class="chart-trade-line ${trade.direction}">
            <span class="chart-trade-node node-start"></span>
            <span class="chart-trade-node node-end"></span>
          </div>
          <div class="chart-trade-pill ${trade.direction}">
            <span class="trade-pill-arrow">${trade.direction === 'up' ? '↑' : '↓'}</span>
            <span class="trade-pill-amt">${Number(trade.investment).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} $</span>
            <span class="trade-pill-timer">00:00</span>
          </div>
        `;
        container.appendChild(item);
      } else {
        const lineEl = item.querySelector('.chart-trade-line');
        if (lineEl) lineEl.className = `chart-trade-line ${trade.direction}`;
        const pillEl = item.querySelector('.chart-trade-pill');
        if (pillEl) pillEl.className = `chart-trade-pill ${trade.direction}`;
      }

      // Calculate vertical Y position from open_price
      let y = null;
      try {
        y = state.series.priceToCoordinate(trade.open_price);
      } catch (e) {}

      if (y === null || isNaN(y)) {
        item.style.display = 'none';
        return;
      }
      item.style.display = 'flex';
      item.style.top = `${Math.round(y)}px`;

      // Calculate horizontal X start position
      let startX = null;
      if (trade.candleTime) {
        try {
          startX = state.chart.timeScale().timeToCoordinate(trade.candleTime);
        } catch (e) {}
      }

      if (startX === null || isNaN(startX)) {
        if (!trade.fixedStartX) {
          trade.fixedStartX = Math.max(30, endX - 110);
        }
        startX = trade.fixedStartX;
      } else {
        trade.fixedStartX = startX;
      }

      const effectiveStartX = Math.min(startX, endX - 25);
      const lineLen = Math.max(25, endX - effectiveStartX);

      item.style.left = `${Math.round(effectiveStartX)}px`;

      const lineEl = item.querySelector('.chart-trade-line');
      if (lineEl) {
        lineEl.style.width = `${Math.round(lineLen)}px`;
      }

      // Update live countdown timer
      const remainingSecs = Math.max(0, Math.ceil((trade.expires_at - now) / 1000));
      if (minRemaining === null || remainingSecs < minRemaining) {
        minRemaining = remainingSecs;
      }
      const m = Math.floor(remainingSecs / 60);
      const s = remainingSecs % 60;
      const timerStr = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

      const timerEl = item.querySelector('.trade-pill-timer');
      if (timerEl && timerEl.textContent !== timerStr) {
        timerEl.textContent = timerStr;
      }
    });

    // Update vertical guide line countdown badge
    const guideBadge = document.getElementById('guide-timer-badge');
    if (guideBadge && minRemaining !== null) {
      const gm = Math.floor(minRemaining / 60);
      const gs = minRemaining % 60;
      guideBadge.textContent = `${String(gm).padStart(2, '0')}:${String(gs).padStart(2, '0')}`;
      guideBadge.style.display = 'block';
    } else if (guideBadge) {
      guideBadge.style.display = 'none';
    }

    // Remove any items that are no longer active
    const activeIds = new Set(state.activeTrades.map(t => `chart-trade-${t.trade_id}`));
    container.querySelectorAll('.chart-trade-item').forEach(el => {
      if (!activeIds.has(el.id)) {
        el.remove();
      }
    });
  }

  // --- Active Trades Monitor & Real-time Settlement ---
  function startActiveTradesMonitor() {
    setInterval(() => {
      const now = Date.now();
      for (let i = state.activeTrades.length - 1; i >= 0; i--) {
        const trade = state.activeTrades[i];
        const remainingSecs = Math.max(0, Math.ceil((trade.expires_at - now) / 1000));

        // Sound countdown beep on last 3 seconds
        if (remainingSecs <= 3 && remainingSecs > 0 && trade.beepedCount < (4 - remainingSecs)) {
          soundContext.playBeep(850, 0.04);
          trade.beepedCount = 4 - remainingSecs;
        }

        if (now >= trade.expires_at && !trade.settlementPending) {
          trade.settlementPending = true;
          settleTrade(trade);
          renderActiveTrades();
        }
      }
      updateChartTradesOverlay();
      updateActiveTradesRealtime();
    }, 250);
  }

  function settleTrade(trade, attempt = 0) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (attempt === 0 && trade.priceLine && state.series) {
      try { state.series.removePriceLine(trade.priceLine); } catch (e) {}
    }
    if (attempt === 0) {
      document.getElementById(`chart-trade-${trade.trade_id}`)?.remove();
    }

    fetch(`/trade/settle/${trade.trade_id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        close_price: state.currentPrice,
      }),
    })
      .then(r => {
        if (!r.ok) throw new Error('Settlement failed');
        return r.json();
      })
      .then(data => {
        state.activeTrades = state.activeTrades.filter(activeTrade => activeTrade.trade_id !== trade.trade_id);
        if (state.activeTrades.length === 0 && (!document.getElementById('pending-checkbox')?.checked)) {
          document.getElementById('trade-marker-lines-overlay')?.classList.remove('active');
        }
        renderActiveTrades();
        updateChartTradesOverlay();

        const won = data.result === 'win';
        const tie = data.result === 'tie';
        if (won) soundContext.playWin();
        else if (tie) soundContext.playBeep(480, 0.08);
        else soundContext.playLoss();

        const profitVal = won ? `+${parseFloat(data.payout).toFixed(2)} $` : (tie ? '0.00 $' : `-${parseFloat(trade.investment).toFixed(2)} $`);
        const resultClass = won ? 'win' : (tie ? 'tie' : 'loss');

        // 1. Show Exact Quotex "RESULT (P/L)" Badge on Chart at expiry candle
        showChartResultBadge(profitVal, resultClass, trade);

        // 2. Show Exact Quotex Bottom-Left Deal Result Card
        showBottomResultCard(trade, profitVal, resultClass);

        if (el.balanceDisplay) {
          el.balanceDisplay.textContent = '$' + parseFloat(data.balance).toFixed(2);
        }

        addClosedTrade(trade, data);
      })
      .catch(() => {
        if (attempt < 3) {
          setTimeout(() => settleTrade(trade, attempt + 1), 1000 * (attempt + 1));
        } else {
          trade.settlementRecovering = true;
          renderActiveTrades();
          showToast('Settlement Pending', 'Result will sync automatically when connection returns.', 'info');
        }
      });
  }

  // --- Quotex Exact Win / Result Display Functions ---
  function showChartResultBadge(profitVal, resultClass, trade) {
    const chartContainer = document.getElementById('chart-area-container') || document.querySelector('.trade-main');
    if (!chartContainer) return;

    const oldBadge = document.getElementById('chart-result-badge');
    if (oldBadge) oldBadge.remove();
    const oldLine = document.getElementById('chart-result-vline');
    if (oldLine) oldLine.remove();

    const beginLineEl = document.querySelector('.trade-guide-line.line-begin');
    const chartWidth = chartContainer.clientWidth || 800;
    const xPos = beginLineEl ? beginLineEl.offsetLeft : Math.round(chartWidth * 0.55);

    // Green/Red vertical line on chart at the expiration candle
    const vline = document.createElement('div');
    vline.id = 'chart-result-vline';
    vline.className = `chart-result-vline ${resultClass}`;
    vline.style.left = `${Math.round(xPos)}px`;
    chartContainer.appendChild(vline);

    const badge = document.createElement('div');
    badge.id = 'chart-result-badge';
    badge.className = `chart-result-badge ${resultClass}`;
    badge.style.left = `${Math.round(xPos - 55)}px`;
    badge.innerHTML = `
      <div class="result-badge-header">
        <span class="result-badge-title">RESULT (P/L)</span>
        <button type="button" class="result-badge-close" onclick="this.closest('.chart-result-badge').remove(); document.getElementById('chart-result-vline')?.remove();">✕</button>
      </div>
      <div class="result-badge-amount">${profitVal}</div>
    `;

    chartContainer.appendChild(badge);

    setTimeout(() => {
      badge.classList.add('fade-out');
      vline.classList.add('fade-out');
      setTimeout(() => {
        badge.remove();
        vline.remove();
      }, 400);
    }, 7000);
  }

  function showBottomResultCard(trade, profitVal, resultClass) {
    const chartContainer = document.getElementById('chart-area-container') || document.querySelector('.trade-main');
    if (!chartContainer) return;

    const oldCard = document.getElementById('bottom-result-card');
    if (oldCard) oldCard.remove();

    const sym = trade.asset ? trade.asset.symbol : (state.selectedAsset?.symbol || 'USD/NGN');
    const flag = trade.asset ? trade.asset.flag : (state.selectedAsset?.flag || '🌐');
    const durMin = Math.floor((trade.duration || 60) / 60);
    const durSec = (trade.duration || 60) % 60;
    const durStr = String(durMin).padStart(2, '0') + ':' + String(durSec).padStart(2, '0');

    // Truncate symbol with ellipsis if needed (e.g. USD/NGN (OTC) -> USD/NGN (... )
    const displaySym = sym.length > 8 ? sym.substring(0, 8) + ' (...' : sym;

    const card = document.createElement('div');
    card.id = 'bottom-result-card';
    card.className = `bottom-result-card ${resultClass}`;
    card.innerHTML = `
      <div class="result-card-header">
        <div class="result-card-info">
          <span class="card-flag">${renderAssetFlagsHtml(sym, flag)}</span>
          <span class="card-sym">${displaySym}</span>
          <span class="card-dot">•</span>
          <span class="card-dur">${durStr}</span>
        </div>
        <button type="button" class="result-card-close" onclick="this.closest('.bottom-result-card').remove()">✕</button>
      </div>
      <div class="result-card-amount ${resultClass}">${profitVal}</div>
    `;

    chartContainer.appendChild(card);

    setTimeout(() => {
      card.classList.add('fade-out');
      setTimeout(() => card.remove(), 400);
    }, 8000);
  }

  function updateDateBanner() {
    const months = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
    const now = new Date();
    const dateStr = `${now.getDate()} ${months[now.getMonth()]}`;
    const lbl = document.getElementById('trades-date-label');
    if (lbl) lbl.textContent = dateStr;
    const dateTextEl = document.getElementById('trades-date-text');
    if (dateTextEl) dateTextEl.textContent = dateStr;
    const dateCountEl = document.getElementById('trades-date-count');
    if (dateCountEl) {
      const closedCards = el.tradeList ? el.tradeList.querySelectorAll('.qx-deal-card').length : 0;
      dateCountEl.textContent = state.activeTrades.length + closedCards;
    }
  }

  function earlySellTrade(trade, buyoutVal) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (trade.priceLine && state.series) {
      try { state.series.removePriceLine(trade.priceLine); } catch (e) {}
    }
    document.getElementById(`chart-trade-${trade.trade_id}`)?.remove();

    const idx = state.activeTrades.indexOf(trade);
    if (idx > -1) state.activeTrades.splice(idx, 1);
    updateChartTradesOverlay();
    renderActiveTrades();

    fetch(`/trade/settle/${trade.trade_id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        close_price: state.currentPrice,
        early_sell: true,
      }),
    })
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          showToast('Error', data.error, 'loss');
          return;
        }
        const paidAmount = Number(data.payout || 0).toFixed(2);
        soundContext.playBeep(600, 0.08);
        showToast('Deal Sold', `Position closed early for ${paidAmount} $`, 'info');
        if (el.balanceDisplay && data.balance !== undefined) {
          el.balanceDisplay.textContent = parseFloat(data.balance).toFixed(2) + ' $';
        }
      })
      .catch(() => {});
  }

  function renderActiveTrades() {
    updateDateBanner();
    if (el.activeTradesCount) el.activeTradesCount.textContent = state.activeTrades.length;

    const mobileBriefcaseCount = document.getElementById('mobile-briefcase-count');
    if (mobileBriefcaseCount) mobileBriefcaseCount.textContent = state.activeTrades.length;
    const mobDrawerActiveCount = document.getElementById('mob-drawer-active-count');
    if (mobDrawerActiveCount) mobDrawerActiveCount.textContent = state.activeTrades.length;

    // Desktop Trades List (Quotex Image 1 Exact)
    if (el.activeTradesContainer) {
      if (state.activeTrades.length === 0) {
        el.activeTradesContainer.innerHTML = '';
      } else {
        el.activeTradesContainer.innerHTML = '';
        state.activeTrades.forEach(trade => {
          const remainingSecs = Math.max(0, Math.ceil((trade.expires_at - Date.now()) / 1000));
          const m = Math.floor(remainingSecs / 60);
          const s = remainingSecs % 60;
          const timerStr = m > 0 ? `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}` : `00:${String(s).padStart(2,'0')}`;
          const timerLabel = trade.settlementPending ? 'Settling' : timerStr;
          const isWin = (trade.direction === 'up' && state.currentPrice > trade.open_price) ||
                        (trade.direction === 'down' && state.currentPrice < trade.open_price);

          const sellNowVal = isWin ? Math.max(1, Math.round(trade.investment * 0.15)) : Math.max(1, Math.round(trade.investment * 0.05));

          const item = document.createElement('div');
          item.className = 'qx-deal-card active-deal';
          item.dataset.tradeId = trade.trade_id;
          item.innerHTML = `
            <div class="deal-card-main">
              <div class="deal-row-top">
                <div class="deal-sym-wrap">
                  <span class="deal-expand-arrow">⌄</span>
                  <span class="deal-flag">${renderAssetFlagsHtml(trade.asset.symbol, trade.asset.flag)}</span>
                  <span class="deal-sym-name">${trade.asset.symbol}</span>
                </div>
                <span class="deal-timer-val">${timerLabel}</span>
              </div>
              <div class="deal-row-mid">
                <div class="deal-invest-dir ${isWin ? 'win' : 'loss'}">
                  <span class="deal-arrow-icon">${trade.direction === 'up' ? '↑' : '↓'}</span>
                  <span class="deal-invest-val">${Number(trade.investment).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} $</span>
                </div>
                <span class="deal-payout-val ${isWin ? 'profit text-green' : 'loss text-red'}">
                  ${isWin ? '+' + parseFloat(trade.payout).toFixed(2) + ' $' : '0.00 $'}
                </span>
              </div>
              <button type="button" class="btn-deal-sell" data-trade-id="${trade.trade_id}" ${trade.settlementPending ? 'disabled' : ''}>
                <span>${trade.settlementPending ? 'Settlement pending' : 'Sell now'}</span>
                <span>${trade.settlementPending ? '' : sellNowVal.toFixed(2) + ' $'}</span>
              </button>
            </div>
            <div class="deal-card-details">
              <div class="deal-detail-row"><span>ID:</span><b>${trade.trade_id || trade.id || ''}</b></div>
              <div class="deal-detail-row"><span>Trade Pair:</span><b>${trade.asset.symbol} ${trade.asset.payout || 82}%</b></div>
              <div class="deal-detail-row"><span>Open Price:</span><b>${trade.open_price}</b></div>
              <div class="deal-detail-row"><span>Current Price:</span><b>${state.currentPrice}</b></div>
            </div>
          `;

          item.querySelector('.deal-card-main')?.addEventListener('click', (e) => {
            if (e.target.closest('.btn-deal-sell')) return;
            item.classList.toggle('expanded');
          });

          item.querySelector('.btn-deal-sell')?.addEventListener('click', (e) => {
            e.stopPropagation();
            earlySellTrade(trade, sellNowVal);
          });

          el.activeTradesContainer.appendChild(item);
        });
      }
    }

    // Mobile Drawer Trades List (Quotex Mobile Screenshot 2 & 3 Exact)
    const mobileList = document.getElementById('mobile-drawer-active-list');
    if (mobileList) {
      if (state.activeTrades.length === 0) {
        mobileList.innerHTML = '<div class="empty-trades-quotex-box"><p>No active deals</p></div>';
      } else {
        mobileList.innerHTML = '';
        state.activeTrades.forEach(trade => {
          const remainingSecs = Math.max(0, Math.ceil((trade.expires_at - Date.now()) / 1000));
          const m = Math.floor(remainingSecs / 60);
          const s = remainingSecs % 60;
          const timerLabel = trade.settlementPending ? 'Settling' : `00:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
          const isWin = (trade.direction === 'up' && state.currentPrice > trade.open_price) ||
                        (trade.direction === 'down' && state.currentPrice < trade.open_price);
          const sellNowVal = isWin ? Math.max(1, Math.round(trade.investment * 0.15)) : Math.max(1, Math.round(trade.investment * 0.05));

          const mobCard = document.createElement('div');
          mobCard.className = 'mobile-deal-card';
          mobCard.dataset.tradeId = trade.trade_id;
          mobCard.innerHTML = `
            <div class="mob-deal-head">
              <div class="mob-deal-symbol">
                <span>${renderAssetFlagsHtml(trade.asset.symbol, trade.asset.flag)}</span>
                <span>${trade.asset.symbol}</span>
              </div>
              <span class="mob-deal-timer">${timerLabel}</span>
            </div>
            <div class="mob-deal-row">
              <div class="mob-deal-invest ${isWin ? 'win' : 'loss'}">
                <span>${trade.direction === 'up' ? '↑' : '↓'}</span>
                <span>${Number(trade.investment).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} $</span>
              </div>
              <div class="mob-deal-payout ${isWin ? 'profit text-green' : 'loss text-red'}">
                ${isWin ? '+' + Number(trade.payout).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' $' : '0.00 $'}
              </div>
            </div>
            <div class="mob-deal-actions">
              <button type="button" class="btn-mob-double" data-trade-id="${trade.trade_id}" ${trade.settlementPending ? 'disabled' : ''}>x2 Double up</button>
              <button type="button" class="btn-mob-sell" data-trade-id="${trade.trade_id}" ${trade.settlementPending ? 'disabled' : ''}>${trade.settlementPending ? 'Settlement pending' : 'Sell now ' + sellNowVal + ' $'}</button>
            </div>
          `;

          mobCard.querySelector('.btn-mob-double')?.addEventListener('click', (e) => {
            e.stopPropagation();
            state.investment = trade.investment;
            if (el.investmentInput) el.investmentInput.value = state.investment;
            placeTrade(trade.direction);
          });

          mobCard.querySelector('.btn-mob-sell')?.addEventListener('click', (e) => {
            e.stopPropagation();
            earlySellTrade(trade, sellNowVal);
          });

          mobileList.appendChild(mobCard);
        });
      }
    }
  }

  function updateActiveTradesRealtime() {
    if (!state.activeTrades.length) return;

    state.activeTrades.forEach(trade => {
      const remainingSecs = Math.max(0, Math.ceil((trade.expires_at - Date.now()) / 1000));
      const compactTimerLabel = trade.settlementPending
        ? 'Settling'
        : (remainingSecs >= 3600
          ? `${String(Math.floor(remainingSecs / 3600)).padStart(2, '0')}:${String(Math.floor((remainingSecs % 3600) / 60)).padStart(2, '0')}:${String(remainingSecs % 60).padStart(2, '0')}`
          : `${String(Math.floor(remainingSecs / 60)).padStart(2, '0')}:${String(remainingSecs % 60).padStart(2, '0')}`);
      const fullTimerLabel = trade.settlementPending
        ? 'Settling'
        : `${String(Math.floor(remainingSecs / 3600)).padStart(2, '0')}:${String(Math.floor((remainingSecs % 3600) / 60)).padStart(2, '0')}:${String(remainingSecs % 60).padStart(2, '0')}`;

      const isWin = (trade.direction === 'up' && state.currentPrice > trade.open_price) ||
                    (trade.direction === 'down' && state.currentPrice < trade.open_price);
      const sellNowVal = isWin ? Math.max(1, Math.round(trade.investment * 0.15)) : Math.max(1, Math.round(trade.investment * 0.05));
      const payoutText = isWin ? '+' + parseFloat(trade.payout).toFixed(2) + ' $' : '0.00 $';

      document.querySelectorAll(`.active-deal[data-trade-id="${trade.trade_id}"]`).forEach(card => {
        const timerEl = card.querySelector('.deal-timer-val');
        if (timerEl) timerEl.textContent = compactTimerLabel;

        const investEl = card.querySelector('.deal-invest-dir');
        if (investEl) {
          investEl.classList.toggle('win', isWin);
          investEl.classList.toggle('loss', !isWin);
        }

        const payoutEl = card.querySelector('.deal-payout-val');
        if (payoutEl) {
          payoutEl.textContent = payoutText;
          payoutEl.classList.toggle('profit', isWin);
          payoutEl.classList.toggle('text-green', isWin);
          payoutEl.classList.toggle('loss', !isWin);
          payoutEl.classList.toggle('text-red', !isWin);
        }

        const sellButton = card.querySelector('.btn-deal-sell');
        if (sellButton && !trade.settlementPending) {
          const amountEl = sellButton.querySelector('span:last-child');
          if (amountEl) amountEl.textContent = sellNowVal.toFixed(2) + ' $';
        }

        const currentPriceEl = card.querySelector('.deal-detail-row:nth-child(4) b');
        if (currentPriceEl) currentPriceEl.textContent = state.currentPrice;
      });

      document.querySelectorAll(`.mobile-deal-card[data-trade-id="${trade.trade_id}"]`).forEach(card => {
        const timerEl = card.querySelector('.mob-deal-timer');
        if (timerEl) timerEl.textContent = fullTimerLabel;

        const investEl = card.querySelector('.mob-deal-invest');
        if (investEl) {
          investEl.classList.toggle('win', isWin);
          investEl.classList.toggle('loss', !isWin);
        }

        const payoutEl = card.querySelector('.mob-deal-payout');
        if (payoutEl) {
          payoutEl.textContent = payoutText;
          payoutEl.classList.toggle('profit', isWin);
          payoutEl.classList.toggle('text-green', isWin);
          payoutEl.classList.toggle('loss', !isWin);
          payoutEl.classList.toggle('text-red', !isWin);
        }

        const sellButton = card.querySelector('.btn-mob-sell');
        if (sellButton && !trade.settlementPending) {
          sellButton.textContent = 'Sell now ' + sellNowVal + ' $';
        }
      });
    });
  }

  function addClosedTrade(trade, resultData) {
    if (!el.tradeList) return;
    const emptyMsg = document.getElementById('empty-history-msg');
    if (emptyMsg) emptyMsg.style.display = 'none';

    const item = document.createElement('div');
    const won = resultData.result === 'win';
    item.className = `qx-deal-card closed-deal ${won ? 'deal-win' : 'deal-loss'}`;
    const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);
    const diff = won ? '+7' : '-6';
    const uuid = 'c9b7aacb-' + Math.random().toString(36).substring(2, 6) + '-435a';
    const durSec = trade.duration_seconds || 5;
    const durStr = `00:00:${String(durSec).padStart(2, '0')}`;

    item.innerHTML = `
      <div class="deal-card-main">
        <div class="deal-row-top">
          <div class="deal-sym-wrap">
            <span class="deal-expand-arrow">⌄</span>
            <span class="deal-flag">${trade.asset.flag || '🌐'}</span>
            <span class="deal-sym-name">${trade.asset.symbol}</span>
          </div>
          <span class="deal-duration-val">${durStr}</span>
        </div>
        <div class="deal-row-mid">
          <div class="deal-invest-dir ${won ? 'win' : 'loss'}">
            <span class="deal-arrow-icon">${won ? '+' : '-'}</span>
            <span class="deal-invest-val">${Number(trade.investment).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})} $</span>
          </div>
          <span class="deal-payout-val ${won ? 'profit text-green' : 'loss text-red'}">
            ${won ? '+' + parseFloat(resultData.payout).toFixed(2) + ' $' : '0.00 $'}
          </span>
        </div>
      </div>
      <div class="deal-card-details">
        <div class="deal-detail-row"><span>ID:</span><b>${uuid}</b></div>
        <div class="deal-detail-row"><span>Trade Pair:</span><b>${trade.asset.symbol} ${trade.asset.payout || 82}%</b></div>
        <div class="deal-detail-row"><span>Open Price:</span><b>${trade.open_price}</b></div>
        <div class="deal-detail-row"><span>Close Price:</span><b>${state.currentPrice}</b></div>
        <div class="deal-detail-row"><span>Open time:</span><b>${nowStr}</b></div>
        <div class="deal-detail-row"><span>Close time:</span><b>${nowStr}</b></div>
        <div class="deal-detail-row"><span>Duration:</span><b>${durStr}</b></div>
        <div class="deal-detail-row"><span>Difference:</span><b class="${won ? 'text-green' : 'text-red'}">${diff}</b></div>
      </div>
    `;

    item.querySelector('.deal-card-main')?.addEventListener('click', () => {
      item.classList.toggle('expanded');
    });

    el.tradeList.prepend(item);

    const historyCountEl = document.getElementById('history-trades-count');
    if (historyCountEl) {
      const cur = parseInt(historyCountEl.textContent || '0', 10);
      historyCountEl.textContent = cur + 1;
    }
    updateDateBanner();
  }

  window.toggleSidebarMenu = function() {
    const menu = document.getElementById('sidebar-popup-menu');
    if (menu) {
      menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
  };


  // --- Trades Tab Navigation (Active vs History) ---
  document.querySelectorAll('.trades-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.trades-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const target = tab.dataset.tab;
      if (target === 'active') {
        el.activeTradesContainer.style.display = 'block';
        el.closedTradesContainer.style.display = 'none';
      } else {
        el.activeTradesContainer.style.display = 'none';
        el.closedTradesContainer.style.display = 'block';
      }
    });
  });

  // --- Account Switcher & Refill Demo ---
  document.querySelectorAll('.account-option').forEach(opt => {
    opt.addEventListener('click', (e) => {
      if (e.target.closest('#btn-refill-demo')) return;
      const type = opt.dataset.type;
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

      fetch('/trade/switch-account', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ account_type: type }),
      })
        .then(r => r.json())
        .then(data => {
          if (el.balanceDisplay) el.balanceDisplay.textContent = '$' + parseFloat(data.balance).toFixed(2);
          if (el.accountTypeLabel) el.accountTypeLabel.textContent = `${type.toUpperCase()} ACCOUNT`;
          document.querySelectorAll('.account-option').forEach(o => o.classList.toggle('active', o.dataset.type === type));
          el.accountDropdown?.classList.remove('open');
          showToast('Account Switched', `Now trading on ${type.toUpperCase()} balance`, 'info');
        });
    });
  });

  document.getElementById('btn-refill-demo')?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (typeof window.refillDemoBalance === 'function') {
      window.refillDemoBalance();
    }
  });

  // --- Chart Type Toggle (Candles vs Area) ---
  document.getElementById('btn-chart-candles')?.addEventListener('click', () => {
    document.getElementById('btn-chart-candles').classList.add('active');
    document.getElementById('btn-chart-area').classList.remove('active');
    state.chartType = 'candles';
    createSeries();
    updateSeriesData();
  });

  document.getElementById('btn-chart-area')?.addEventListener('click', () => {
    document.getElementById('btn-chart-area').classList.add('active');
    document.getElementById('btn-chart-candles').classList.remove('active');
    state.chartType = 'area';
    createSeries();
    updateSeriesData();
  });

  // --- Quotex Candle Timeframes Map ---
  const TF_MAP = {
    '5s': 5,
    '10s': 10,
    '15s': 15,
    '30s': 30,
    '1m': 60,
    '2m': 120,
    '3m': 180,
    '5m': 300,
    '10m': 600,
    '15m': 900,
    '30m': 1800,
    '1h': 3600,
    '4h': 14400,
    '1d': 86400
  };
  document.querySelectorAll('.tf-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tf-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const tfStr = btn.dataset.tf;
      setTimeframe(tfStr);
    });
  });

  // --- Technical Indicators Event Handlers ---
  const indBtn = document.getElementById('btn-indicators-toggle');
  const indDrop = document.getElementById('indicators-dropdown');
  indBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    indDrop?.classList.toggle('open');
    indBtn.classList.toggle('active', indDrop?.classList.contains('open'));
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#btn-indicators-toggle') && !e.target.closest('#indicators-dropdown')) {
      indDrop?.classList.remove('open');
      indBtn?.classList.remove('active');
    }
  });

  document.getElementById('chk-ind-sma')?.addEventListener('change', (e) => {
    state.indicators.sma = e.target.checked;
    updateIndicators();
  });
  document.getElementById('chk-ind-ema')?.addEventListener('change', (e) => {
    state.indicators.ema = e.target.checked;
    updateIndicators();
  });
  document.getElementById('chk-ind-bollinger')?.addEventListener('change', (e) => {
    state.indicators.bollinger = e.target.checked;
    updateIndicators();
  });

  // --- Quick Preset Chips Event Handlers ---
  // Duration Chips
  document.querySelectorAll('.preset-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.preset-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      setDuration(parseInt(chip.dataset.secs));
    });
  });

  // Investment Chips
  document.querySelectorAll('.invest-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.invest-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      state.investment = parseFloat(chip.dataset.val);
      if (el.investmentInput) el.investmentInput.value = state.investment;
      updatePayoutCalculation();
    });
  });

  // --- Drawer & Modal Controls ---
  // Asset Drawer
  document.getElementById('btn-close-asset-drawer')?.addEventListener('click', () => {
    el.assetPanel?.classList.remove('open');
    const ab = document.getElementById('btn-add-asset-tab');
    if (ab) ab.textContent = '+';
  });

  // Category Filtering in Asset Drawer
  function filterAssetRows() {
    const activeCatBtn = document.querySelector('.cat-btn.active');
    const cat = activeCatBtn ? activeCatBtn.dataset.cat : 'all';
    const q = el.assetSearchInput ? el.assetSearchInput.value.toLowerCase().trim() : '';

    document.querySelectorAll('.asset-list-item').forEach(item => {
      let catMatch = false;
      if (!cat || cat === 'all') catMatch = true;
      else if (cat === 'stock') catMatch = (item.dataset.cat === 'stock' || item.dataset.cat === 'index');
      else catMatch = (item.dataset.cat === cat);

      let searchMatch = true;
      if (q) {
        searchMatch = item.dataset.symbol.toLowerCase().includes(q) || item.dataset.name.toLowerCase().includes(q);
      }
      item.style.display = (catMatch && searchMatch) ? 'flex' : 'none';
    });
  }

  document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filterAssetRows();
    });
  });

  el.assetSearchInput?.addEventListener('input', () => {
    filterAssetRows();
  });

  document.querySelectorAll('.asset-list-item').forEach(item => {
    item.addEventListener('click', () => {
      selectAsset({
        id: parseInt(item.dataset.id),
        symbol: item.dataset.symbol,
        name: item.dataset.name,
        payout: parseInt(item.dataset.payout),
        flag: item.dataset.flag,
        base: parseFloat(item.dataset.base),
        digits: item.dataset.digits ? parseInt(item.dataset.digits) : 5,
      });
    });
  });

  // Signals Drawer
  document.getElementById('btn-nav-signals')?.addEventListener('click', () => {
    el.signalsDrawer?.classList.toggle('open');
  });
  document.getElementById('btn-close-signals')?.addEventListener('click', () => {
    el.signalsDrawer?.classList.remove('open');
  });

  // Copy Signal Button
  document.querySelectorAll('.btn-copy-sig').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const card = e.target.closest('.signal-card');
      const sym = card.dataset.symbol;
      const dir = card.dataset.dir;
      const dur = parseInt(card.dataset.dur);

      const targetAsset = state.openTabs.find(t => t.symbol === sym);
      if (targetAsset) selectAsset(targetAsset);
      setDuration(dur);
      el.signalsDrawer?.classList.remove('open');
      showToast('Signal Copied', `Target: ${sym} ${dir.toUpperCase()} (${dur}s)`, 'info');
    });
  });

  // Leaderboard Modal
  document.getElementById('btn-nav-leaderboard')?.addEventListener('click', () => {
    el.leaderboardModal?.classList.add('open');
  });
  document.getElementById('btn-close-leaderboard')?.addEventListener('click', () => {
    el.leaderboardModal?.classList.remove('open');
  });

  // Settings Modal
  document.getElementById('btn-nav-settings')?.addEventListener('click', () => {
    el.settingsModal?.classList.add('open');
  });
  document.getElementById('btn-close-settings')?.addEventListener('click', () => {
    el.settingsModal?.classList.remove('open');
  });

  document.getElementById('chk-sound')?.addEventListener('change', (e) => {
    soundContext.enabled = e.target.checked;
  });

  // Close modals on outside click
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#asset-panel') && 
        !e.target.closest('#asset-selector-btn') && 
        !e.target.closest('#btn-add-asset-tab') && 
        !e.target.closest('#panel-asset-btn') &&
        !e.target.closest('.dJ15T') &&
        !e.target.closest('.qx-tab-pill') &&
        !e.target.closest('.qx-active-tab') &&
        !e.target.closest('.Q02Z1') &&
        !e.target.closest('#qx-open-tabs-bar')) {
      el.assetPanel?.classList.remove('open');
      const ab = document.getElementById('btn-add-asset-tab');
      if (ab) ab.textContent = '+';
    }
    if (!e.target.closest('.account-switcher-wrap')) {
      el.accountDropdown?.classList.remove('open');
    }
    if (!e.target.closest('.indicators-menu-wrap')) {
      indDrop?.classList.remove('open');
      indBtn?.classList.remove('active');
    }
    if (e.target === el.leaderboardModal) el.leaderboardModal.classList.remove('open');
    if (e.target === el.settingsModal) el.settingsModal.classList.remove('open');
  });

  // --- Toast ---
  function showToast(title, body, type = 'info') {
    if (!el.toast) return;
    el.toast.querySelector('.toast-title').textContent = title;
    el.toast.querySelector('.toast-body').textContent = body;
    el.toast.className = `trade-toast show ${type}`;
    el.toast.querySelector('.toast-title').className = `toast-title ${type}`;
    setTimeout(() => el.toast.classList.remove('show'), 3500);
  }

  function bindEvents() {
    renderTimeDisplay();
    document.getElementById('panel-asset-btn')?.addEventListener('click', (e) => {
      e.stopPropagation();
      state.assetPanelMode = 'replace';
      const isOpen = el.assetPanel?.classList.toggle('open');
      const ab = document.getElementById('btn-add-asset-tab');
      if (ab) ab.textContent = isOpen ? '✕' : '+';
    });
  }

  // --- Mobile Active Trades Drawer Helpers ---
  window.toggleMobileTradesDrawer = function() {
    const drawer = document.getElementById('mobile-trades-drawer');
    if (drawer) {
      drawer.style.display = (drawer.style.display === 'none' || !drawer.style.display) ? 'flex' : 'none';
      if (drawer.style.display === 'flex') {
        renderActiveTrades();
      }
    }
  };

  window.switchMobileDrawerTab = function(tab) {
    const tabTrades = document.getElementById('mob-tab-trades');
    const tabOrders = document.getElementById('mob-tab-orders');
    const activeList = document.getElementById('mobile-drawer-active-list');
    const ordersList = document.getElementById('mobile-drawer-orders-list');
    if (tab === 'trades') {
      tabTrades?.classList.add('active');
      tabOrders?.classList.remove('active');
      if (activeList) activeList.style.display = 'block';
      if (ordersList) ordersList.style.display = 'none';
    } else {
      tabTrades?.classList.remove('active');
      tabOrders?.classList.add('active');
      if (activeList) activeList.style.display = 'none';
      if (ordersList) ordersList.style.display = 'block';
    }
  };

  // --- Quotex Candle Timeframe Popover & Candle Countdown Timer ---
  window.toggleTimeframePopover = function(e, mode) {
    if (e) e.stopPropagation();
    const popover = document.getElementById('quotex-timeframe-popover');
    if (!popover) return;
    const isVisible = popover.style.display === 'block';

    if (isVisible) {
      popover.style.display = 'none';
      updateMobileDotsIcon(false);
    } else {
      popover.classList.remove('mobile-pos', 'desktop-pos');
      if (mode === 'mobile') {
        popover.classList.add('mobile-pos');
      } else {
        popover.classList.add('desktop-pos');
      }
      popover.style.display = 'block';
      updateMobileDotsIcon(true);
    }
  };

  function updateMobileDotsIcon(isOpen) {
    const dots = document.getElementById('mobile-dots-icon');
    const close = document.getElementById('mobile-dots-close');
    if (dots && close) {
      dots.style.display = isOpen ? 'none' : 'inline-flex';
      close.style.display = isOpen ? 'inline-flex' : 'none';
    }
  }

  document.addEventListener('click', (e) => {
    const popover = document.getElementById('quotex-timeframe-popover');
    if (popover && popover.style.display === 'block') {
      if (!e.target.closest('#quotex-timeframe-popover') && 
          !e.target.closest('#btn-mobile-chart-menu') && 
          !e.target.closest('#btn-tf-toggle')) {
        popover.style.display = 'none';
        updateMobileDotsIcon(false);
      }
    }
  });

  window.setTimeframe = function(tf) {
    document.querySelectorAll('.tf-grid-btn, .tf-btn').forEach(b => {
      b.classList.toggle('active', b.dataset.tf === tf);
    });
    const tfBtn = document.getElementById('btn-tf-toggle');
    if (tfBtn) tfBtn.textContent = tf;

    state.timeframeSec = TF_MAP[tf] || 60;
    if (state.chart) {
      state.chart.applyOptions({
        timeScale: { secondsVisible: state.timeframeSec <= 60 }
      });
    }
    loadPriceData();
    updateCandleCountdown();
  };

  document.querySelectorAll('.tf-grid-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const tf = btn.dataset.tf;
      setTimeframe(tf);
      const popover = document.getElementById('quotex-timeframe-popover');
      if (popover) popover.style.display = 'none';
      updateMobileDotsIcon(false);
    });
  });

  function updateCandleCountdown() {
    const badge = document.getElementById('candle-timer-badge');
    if (!badge) return;

    const tf = state.timeframeSec || 60;
    const nowSec = Math.floor((Date.now() + serverOffset) / 1000);
    const secRemaining = tf - (nowSec % tf);
    const mm = Math.floor(secRemaining / 60);
    const ss = secRemaining % 60;
    badge.textContent = `${String(mm).padStart(2, '0')}:${String(ss).padStart(2, '0')}`;

    if (state.series && state.currentPrice) {
      try {
        const y = state.series.priceToCoordinate(state.currentPrice);
        if (y !== null && !isNaN(y)) {
          badge.style.top = `${y}px`;
          badge.style.display = 'block';
        }
      } catch (err) {}
    }
  }

  setInterval(updateCandleCountdown, 250);

  document.getElementById('btn-chart-type-toggle')?.addEventListener('click', () => {
    state.chartType = state.chartType === 'candles' ? 'area' : 'candles';
    createSeries();
    updateSeriesData();
    showToast('Chart Type', `Switched to ${state.chartType}`, 'info');
  });

  document.getElementById('btn-zoom-in')?.addEventListener('click', () => {
    if (!state.chart) return;
    const range = state.chart.timeScale().getVisibleLogicalRange();
    if (range) {
      const span = range.to - range.from;
      state.chart.timeScale().setVisibleLogicalRange({
        from: range.from + span * 0.15,
        to: range.to - span * 0.15,
      });
    }
  });

  document.getElementById('btn-zoom-out')?.addEventListener('click', () => {
    if (!state.chart) return;
    const range = state.chart.timeScale().getVisibleLogicalRange();
    if (range) {
      const span = range.to - range.from;
      state.chart.timeScale().setVisibleLogicalRange({
        from: range.from - span * 0.2,
        to: range.to + span * 0.2,
      });
    }
  });

  document.getElementById('btn-pending-trade')?.addEventListener('click', () => {
    showToast('Pending Trade', 'Order book & pending orders queue active', 'info');
  });

  // --- Global Window Helpers for More, Settings, and Account Popover ---
  window.toggleSidebarMenu = function() {
    const drawer = document.getElementById('sidebar-popup-menu');
    if (drawer) {
      drawer.style.display = drawer.style.display === 'none' ? 'flex' : 'none';
    }
  };

  window.toggleSettingsSidepanel = function() {
    const panel = document.getElementById('settings-sidepanel');
    if (panel) {
      panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }
  };

  window.openSignalsDrawer = function() {
    window.toggleSidebarMenu();
    const drawer = document.getElementById('trading-signals-drawer');
    if (drawer) {
      drawer.style.display = 'flex';
      fetchTradingSignals();
    }
  };

  window.closeSignalsDrawer = function() {
    const drawer = document.getElementById('trading-signals-drawer');
    if (drawer) {
      drawer.style.display = 'none';
    }
  };

  window.fetchTradingSignals = function() {
    fetch('/trade/signals')
      .then(r => r.json())
      .then(data => {
        renderSignalsLists(data);
      })
      .catch(err => {
        console.error('Error fetching signals:', err);
      });
  };

  function renderSignalsLists(data) {
    const activeList = document.getElementById('active-signals-list');
    const pastList = document.getElementById('past-signals-list');
    if (!activeList || !pastList) return;

    const active = data.active || [];
    const past = data.past || [];

    if (active.length === 0) {
      activeList.innerHTML = '<div class="signals-empty-note">No active trading signals at the moment.</div>';
    } else {
      activeList.innerHTML = active.map(sig => {
        const flagHtml = renderAssetFlagsHtml(sig.pair_name);
        const isCall = sig.direction === 'call';
        const dirClass = isCall ? 'call' : 'put';
        const dirArrow = isCall ? '↑' : '↓';
        return `
          <div class="signal-card active-sig" onclick="applySignalToTrade(${sig.id})">
            <div class="signal-card-top">
              <div class="signal-card-pair-wrap">
                <span class="flag-icon">${flagHtml}</span>
                <span class="signal-card-pair">${sig.pair_name}</span>
              </div>
              <div class="signal-dir-circle ${dirClass}">
                ${dirArrow}
              </div>
            </div>
            <div class="signal-card-bottom">
              <span class="signal-duration-txt">Duration: ${sig.duration}</span>
              <span class="signal-time-txt">${sig.time_formatted}</span>
            </div>
          </div>
        `;
      }).join('');
    }

    if (past.length === 0) {
      pastList.innerHTML = '<div class="signals-empty-note">No past signals.</div>';
    } else {
      pastList.innerHTML = past.map(sig => {
        const flagHtml = renderAssetFlagsHtml(sig.pair_name);
        const isCall = sig.direction === 'call';
        const dirClass = isCall ? 'call' : 'put';
        const dirArrow = isCall ? '↑' : '↓';
        return `
          <div class="signal-card past" onclick="showToast('Signal Expired', 'This signal time has passed and cannot be placed.', 'lose')">
            <div class="signal-card-top">
              <div class="signal-card-pair-wrap">
                <span class="flag-icon">${flagHtml}</span>
                <span class="signal-card-pair">${sig.pair_name}</span>
              </div>
              <div class="signal-dir-circle ${dirClass}">
                ${dirArrow}
              </div>
            </div>
            <div class="signal-card-bottom">
              <span class="signal-duration-txt">Duration: ${sig.duration}</span>
              <span class="signal-time-txt">${sig.time_formatted}</span>
            </div>
          </div>
        `;
      }).join('');
    }

    window._lastSignalsMap = {};
    active.forEach(s => window._lastSignalsMap[s.id] = s);
  }

  window.applySignalToTrade = function(signalId) {
    const sig = window._lastSignalsMap ? window._lastSignalsMap[signalId] : null;
    if (!sig) return;

    if (sig.expires_at_timestamp && Date.now() >= sig.expires_at_timestamp) {
      showToast('Signal Expired', 'This signal has expired and cannot be placed.', 'lose');
      fetchTradingSignals();
      return;
    }

    // Find and switch asset
    const allAssets = window.ALL_ASSETS || window.ASSETS_DATA || [];
    const foundAsset = allAssets.find(a => a.id === sig.asset_id || (a.symbol && a.symbol.toLowerCase() === sig.pair_name.toLowerCase()));
    if (foundAsset) {
      selectAsset(foundAsset);
    } else if (state.selectedAsset && state.selectedAsset.symbol.toLowerCase() !== sig.pair_name.toLowerCase()) {
      selectAsset({
        id: sig.asset_id || 999,
        symbol: sig.pair_name,
        name: sig.pair_name,
        payout: sig.payout_percent || 85,
        base: 1335.50,
        digits: 2,
        type: 'forex'
      });
    }

    // Set duration
    if (sig.duration_seconds) {
      setDuration(sig.duration_seconds);
    }

    closeSignalsDrawer();

    const dirText = sig.direction === 'call' ? 'CALL / UP ▲' : 'PUT / DOWN ▼';
    showToast('Signal Applied', `${sig.pair_name} — ${dirText} (Duration: ${sig.duration})`, 'win');
  };

  window.openLeaderboardDrawer = function() {
    if (typeof window.toggleSidebarMenu === 'function') {
      const menu = document.getElementById('sidebar-popup-menu');
      if (menu && menu.style.display !== 'none') {
        window.toggleSidebarMenu();
      }
    }
    const drawer = document.getElementById('leaderboard-drawer');
    if (drawer) {
      drawer.style.display = 'flex';
    }
  };

  window.openLeaderboardModal = function() {
    window.openLeaderboardDrawer();
  };

  window.closeLeaderboardDrawer = function() {
    const drawer = document.getElementById('leaderboard-drawer');
    if (drawer) {
      drawer.style.display = 'none';
    }
  };

  window.showRatingWorkInfo = function() {
    const modal = document.getElementById('rating-info-modal');
    if (modal) {
      modal.style.display = 'flex';
    }
  };

  window.closeRatingWorkModal = function(e) {
    if (e && e.target && e.target.id !== 'rating-info-modal' && !e.target.classList.contains('qx-rating-info-close')) {
      return;
    }
    const modal = document.getElementById('rating-info-modal');
    if (modal) {
      modal.style.display = 'none';
    }
  };

  window.toggleEditDemoInput = function() {
    const box = document.getElementById('edit-demo-box');
    if (box) {
      const isHidden = box.style.display === 'none' || !box.style.display;
      box.style.display = isHidden ? 'flex' : 'none';
      if (isHidden) {
        const inp = document.getElementById('input-new-demo-val');
        if (inp) {
          inp.focus();
          inp.select();
        }
      }
    }
  };

  window.confirmNewDemoBalance = function() {
    const inp = document.getElementById('input-new-demo-val');
    const newBal = parseFloat(inp?.value || '10000');
    if (isNaN(newBal) || newBal < 0) {
      alert('Please enter a valid balance amount');
      return;
    }
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('/trade/switch-account', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify({ account_type: 'demo', new_balance: newBal }),
    })
      .then(r => r.json())
      .then(data => {
        const actualBal = (data && data.demo_balance !== undefined) ? parseFloat(data.demo_balance) : newBal;
        state.balance = actualBal;
        const balLbl = document.getElementById('account-balance');
        const ddDemo = document.getElementById('dd-demo-balance');
        if (balLbl) balLbl.textContent = '$' + actualBal.toFixed(2);
        if (ddDemo) ddDemo.textContent = '$' + actualBal.toFixed(2);
        if (inp) inp.value = actualBal;
        const box = document.getElementById('edit-demo-box');
        if (box) box.style.display = 'none';
        showToast('Balance Updated', `Demo balance set to $${actualBal.toFixed(2)}`, 'win');
      })
      .catch((err) => {
        console.error(err);
        state.balance = newBal;
        const balLbl = document.getElementById('account-balance');
        const ddDemo = document.getElementById('dd-demo-balance');
        if (balLbl) balLbl.textContent = '$' + newBal.toFixed(2);
        if (ddDemo) ddDemo.textContent = '$' + newBal.toFixed(2);
        const box = document.getElementById('edit-demo-box');
        if (box) box.style.display = 'none';
      });
  };

  window.refillDemoBalance = function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('/trade/refill-demo', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
    })
      .then(r => r.json())
      .then(data => {
        const bal = parseFloat(data.demo_balance || 10000);
        state.balance = bal;
        const balLbl = document.getElementById('account-balance');
        const ddDemo = document.getElementById('dd-demo-balance');
        const inp = document.getElementById('input-new-demo-val');
        if (balLbl) balLbl.textContent = '$' + bal.toFixed(2);
        if (ddDemo) ddDemo.textContent = '$' + bal.toFixed(2);
        if (inp) inp.value = bal;
        showToast('Demo Refilled', `Demo account reset to $${bal.toFixed(2)}`, 'win');
      })
      .catch((err) => {
        console.error(err);
      });
  };

  window.switchAccountType = function(type) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('/trade/switch-account', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify({ account_type: type }),
    })
      .then(r => r.json())
      .then(data => {
        location.reload();
      })
      .catch(() => location.reload());
  };

  window.adjustGridOpacity = function(delta) {
    const inp = document.getElementById('gridsopacity-val');
    if (!inp) return;
    let val = Math.max(0, Math.min(10, parseInt(inp.value || '8') + delta));
    inp.value = val;
    if (state.chart) {
      state.chart.applyOptions({
        grid: {
          vertLines: { color: `rgba(24, 32, 48, ${val / 10})` },
          horzLines: { color: `rgba(24, 32, 48, ${val / 10})` },
        }
      });
    }
  };

  // Account dropdown switcher toggle
  const switcherBtn = document.getElementById('account-switcher-toggle');
  const accountDropdown = document.getElementById('account-dropdown');
  const accountBackdrop = document.getElementById('account-popover-backdrop');

  let lastDropdownToggle = 0;
  window.closeAccountDropdown = function() {
    const dd = document.getElementById('account-dropdown');
    const bg = document.getElementById('account-popover-backdrop');
    if (dd) {
      dd.classList.remove('open');
      dd.style.display = 'none';
    }
    if (bg) {
      bg.style.display = 'none';
    }
  };

  window.toggleAccountDropdown = function() {
    const now = Date.now();
    if (now - lastDropdownToggle < 200) return;
    lastDropdownToggle = now;

    const dd = document.getElementById('account-dropdown');
    const bg = document.getElementById('account-popover-backdrop');
    if (!dd) return;

    const isOpen = dd.classList.contains('open') && dd.style.display === 'flex';
    if (isOpen) {
      window.closeAccountDropdown();
      return;
    }

    // Position dropdown relative to switcher button
    const switcher = document.getElementById('account-switcher-toggle');
    const isMobile = window.innerWidth <= 860;
    if (switcher) {
      const rect = switcher.getBoundingClientRect();
      if (isMobile) {
        dd.style.position = 'fixed';
        dd.style.top = Math.max(50, rect.bottom + 6) + 'px';
        dd.style.left = '8px';
        dd.style.right = '8px';
        dd.style.width = 'calc(100vw - 16px)';
        dd.style.maxWidth = '440px';
        dd.style.margin = '0 auto';
        dd.style.maxHeight = Math.max(300, window.innerHeight - rect.bottom - 20) + 'px';
        dd.style.overflowY = 'auto';
        dd.style.flexDirection = 'column';
      } else {
        dd.style.position = 'fixed';
        dd.style.top = (rect.bottom + 6) + 'px';
        const rightOffset = Math.max(12, window.innerWidth - rect.right);
        dd.style.right = rightOffset + 'px';
        dd.style.left = 'auto';
        dd.style.width = '440px';
        dd.style.maxWidth = 'none';
        dd.style.maxHeight = Math.max(400, window.innerHeight - rect.bottom - 20) + 'px';
        dd.style.overflowY = 'auto';
        dd.style.flexDirection = 'row';
      }
    }

    dd.classList.add('open');
    dd.style.display = 'flex';
    dd.style.zIndex = '99999';
    if (bg) {
      bg.style.display = 'block';
      bg.style.zIndex = '99998';
    }
  };

  // Notifications Popover Controls (Deposit & Withdrawal)
  window.closeNotifications = function() {
    const pop = document.getElementById('notifications-popover');
    const bdrop = document.getElementById('notifications-backdrop');
    if (pop) {
      pop.style.display = 'none';
      pop.classList.remove('open');
    }
    if (bdrop) {
      bdrop.style.display = 'none';
    }
  };

  window.toggleNotifications = function(e) {
    if (e) {
      e.stopPropagation();
      e.preventDefault();
    }
    if (typeof window.closeAccountDropdown === 'function') {
      window.closeAccountDropdown();
    }
    const pop = document.getElementById('notifications-popover');
    const bdrop = document.getElementById('notifications-backdrop');
    if (!pop) return;

    const isOpen = pop.classList.contains('open') && (pop.style.display === 'flex' || pop.style.display === 'block');
    if (isOpen) {
      window.closeNotifications();
      return;
    }

    const bellBtn = document.getElementById('btn-header-bell');
    const isMobile = window.innerWidth <= 768;
    if (bellBtn && !isMobile) {
      const rect = bellBtn.getBoundingClientRect();
      pop.style.position = 'fixed';
      pop.style.top = (rect.bottom + 6) + 'px';
      pop.style.left = Math.max(10, rect.right - 360) + 'px';
      pop.style.right = 'auto';
      pop.style.zIndex = '99999';
    } else if (isMobile) {
      pop.style.position = 'fixed';
      pop.style.top = '52px';
      pop.style.left = '10px';
      pop.style.right = '10px';
      pop.style.width = 'auto';
      pop.style.zIndex = '99999';
    }

    pop.style.display = 'flex';
    pop.classList.add('open');
    if (bdrop) {
      bdrop.style.display = 'block';
      bdrop.style.zIndex = '99998';
    }
  };

  window.filterNotifications = function(type, el) {
    const tabs = document.querySelectorAll('.notif-tab');
    tabs.forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');

    const items = document.querySelectorAll('.notif-item');
    items.forEach(item => {
      const itemType = item.getAttribute('data-type');
      if (type === 'all' || itemType === type) {
        item.style.display = 'flex';
      } else {
        item.style.display = 'none';
      }
    });
  };

  window.markAllNotificationsRead = function() {
    const items = document.querySelectorAll('.notif-item.unread');
    items.forEach(i => i.classList.remove('unread'));
    const badge = document.getElementById('bell-count-badge');
    if (badge) badge.style.display = 'none';
    const headerCount = document.querySelector('.notif-header-count');
    if (headerCount) headerCount.textContent = '0';
  };

  switcherBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (typeof window.closeNotifications === 'function') {
      window.closeNotifications();
    }
    window.toggleAccountDropdown();
  });

  accountDropdown?.addEventListener('click', (e) => {
    e.stopPropagation();
  });

  document.addEventListener('click', (e) => {
    const tfMenu = document.getElementById('timeframe-menu');
    if (tfMenu && !tfMenu.contains(e.target)) {
      tfMenu.classList.remove('open');
    }

    if (accountDropdown && !accountDropdown.contains(e.target) && (!switcherBtn || !switcherBtn.contains(e.target))) {
      window.closeAccountDropdown();
    }

    const notifPop = document.getElementById('notifications-popover');
    const notifBell = document.getElementById('btn-header-bell');
    if (notifPop && !notifPop.contains(e.target) && (!notifBell || !notifBell.contains(e.target))) {
      window.closeNotifications();
    }

    const drawer = document.getElementById('sidebar-popup-menu');
    const moreBtn = document.getElementById('btn-nav-more');
    const hamburgerBtn = document.getElementById('btn-hamburger');
    if (drawer && !drawer.contains(e.target) && (!moreBtn || !moreBtn.contains(e.target)) && (!hamburgerBtn || !hamburgerBtn.contains(e.target))) {
      drawer.style.display = 'none';
    }

    const sp = document.getElementById('settings-sidepanel');
    const spBtn = document.getElementById('btn-settings-toggle');
    if (sp && !sp.contains(e.target) && (!spBtn || !spBtn.contains(e.target))) {
      sp.style.display = 'none';
    }
  });

  // Boot
  function bootApp() {
    // Move backdrops and dropdowns to <body> to escape overflow:hidden parents (mobile fix)
    const backdrop = document.getElementById('account-popover-backdrop');
    const dropdown = document.getElementById('account-dropdown');
    if (backdrop && backdrop.parentElement !== document.body) {
      document.body.appendChild(backdrop);
    }
    if (dropdown && dropdown.parentElement !== document.body) {
      document.body.appendChild(dropdown);
    }

    const notifBackdrop = document.getElementById('notifications-backdrop');
    const notifPopover = document.getElementById('notifications-popover');
    if (notifBackdrop && notifBackdrop.parentElement !== document.body) {
      document.body.appendChild(notifBackdrop);
    }
    if (notifPopover && notifPopover.parentElement !== document.body) {
      document.body.appendChild(notifPopover);
    }

    init();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootApp);
  } else {
    bootApp();
  }

})();
