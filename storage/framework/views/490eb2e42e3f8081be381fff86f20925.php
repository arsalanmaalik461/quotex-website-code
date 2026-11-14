<?php $__env->startSection('title', 'Live Trade Monitor'); ?>
<?php $__env->startSection('page-title', '🔴 Live Trade Monitor'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.live-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.pulse-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--red);
    box-shadow: 0 0 0 0 rgba(255,76,106,0.4);
    animation: pulse 1.5s infinite;
    display: inline-block;
    margin-right: 6px;
}
@keyframes pulse {
    0%   { box-shadow: 0 0 0 0 rgba(255,76,106,0.4); }
    70%  { box-shadow: 0 0 0 10px rgba(255,76,106,0); }
    100% { box-shadow: 0 0 0 0 rgba(255,76,106,0); }
}

.trade-row {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px 16px;
    display: grid;
    grid-template-columns: 180px 100px 90px 100px 80px 70px 90px 80px auto;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
    transition: .2s;
    font-size: 13px;
}
.trade-row:hover { border-color: var(--accent); }
.trade-row .user-info .name { font-weight: 600; color: var(--text-primary); }
.trade-row .user-info .sub  { font-size: 11px; color: var(--text-muted); }

.countdown {
    font-size: 20px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.countdown.low { color: var(--red); animation: blink .8s step-end infinite; }
.countdown.mid { color: var(--orange); }
.countdown.ok  { color: var(--green); }
@keyframes blink { 50% { opacity:.3; } }

.override-btns { display: flex; gap: 6px; }
.override-win  { background: rgba(0,209,122,0.15); color: var(--green); border: 1px solid rgba(0,209,122,0.3); border-radius: 6px; padding: 5px 10px; font-size: 11px; font-weight: 700; cursor: pointer; transition: .15s; }
.override-win.active  { background: var(--green); color: #000; }
.override-win:hover   { background: rgba(0,209,122,0.3); }
.override-loss { background: rgba(255,76,106,0.15); color: var(--red); border: 1px solid rgba(255,76,106,0.3); border-radius: 6px; padding: 5px 10px; font-size: 11px; font-weight: 700; cursor: pointer; transition: .15s; }
.override-loss.active { background: var(--red); color: #fff; }
.override-loss:hover  { background: rgba(255,76,106,0.3); }
.override-reset { background: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border); border-radius: 6px; padding: 5px 10px; font-size: 11px; cursor: pointer; transition: .15s; }
.override-reset:hover { color: var(--text-primary); }

.table-header {
    display: grid;
    grid-template-columns: 180px 100px 90px 100px 80px 70px 90px 80px auto;
    gap: 12px;
    padding: 8px 16px;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 8px;
}

#no-trades {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
    display: none;
}
.refresh-indicator { font-size: 11px; color: var(--text-muted); }
.mode-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}
.mode-panel-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.mode-panel-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.mode-toggle-group { display: flex; gap: 8px; flex-wrap: wrap; }
.mode-toggle {
    border: 1px solid var(--border);
    background: var(--bg-hover);
    color: var(--text-secondary);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: .15s;
}
.mode-toggle:hover { color: var(--text-primary); border-color: var(--accent); }
.mode-toggle.active-normal { background: var(--accent); border-color: var(--accent); color: #fff; }
.mode-toggle.active-win { background: var(--green); border-color: var(--green); color: #000; }
.mode-toggle.active-loss { background: var(--red); border-color: var(--red); color: #fff; }
.mode-toggle:disabled { opacity: .65; cursor: wait; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="mode-panel">
    <div>
        <div class="mode-panel-title">Global Trade Result Mode</div>
        <div class="mode-panel-sub" id="modeHint">Normal follows market. Win/Loss applies to every new trade while active.</div>
    </div>
    <div class="mode-toggle-group" id="tradeModeButtons" data-mode="<?php echo e($tradeMode); ?>">
        <button type="button" class="mode-toggle" data-mode="normal" onclick="setTradeMode('normal')">Normal</button>
        <button type="button" class="mode-toggle" data-mode="simulation_win" onclick="setTradeMode('simulation_win')">Win</button>
        <button type="button" class="mode-toggle" data-mode="simulation_loss" onclick="setTradeMode('simulation_loss')">Loss</button>
    </div>
</div>

<div class="live-header">
    <div class="flex items-center gap-2">
        <span class="pulse-dot"></span>
        <span style="font-size:13px;color:var(--text-secondary)">Auto-refresh every 3s</span>
        <span class="refresh-indicator" id="refreshTimer">Next refresh: 3s</span>
    </div>
    <div class="flex gap-2 items-center">
        <span id="tradeCount" style="font-size:13px;color:var(--text-muted)">Loading...</span>
        <button onclick="loadTrades()" class="btn btn-ghost btn-sm">↻ Refresh Now</button>
    </div>
</div>


<div class="table-header">
    <div>User</div>
    <div>Pair</div>
    <div>Direction</div>
    <div>Investment</div>
    <div>Duration</div>
    <div>Profit</div>
    <div>Closes In</div>
    <div>Override</div>
    <div>Win / Loss Control</div>
</div>


<div id="tradeContainer"></div>

<div id="no-trades">
    <div style="font-size:40px;margin-bottom:12px">📭</div>
    <div style="font-size:16px;font-weight:600;margin-bottom:6px;color:var(--text-secondary)">No Active Trades</div>
    <div style="font-size:13px">Waiting for users to place trades...</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
let trades = {};        // keyed by trade_id, stores remaining time
let countdownTimer;
let refreshCountdown = 3;
let tradeMode = document.getElementById('tradeModeButtons').dataset.mode || 'normal';

function renderTradeMode(mode) {
    tradeMode = mode || 'normal';
    document.querySelectorAll('.mode-toggle').forEach(button => {
        button.classList.remove('active-normal', 'active-win', 'active-loss');
        if (button.dataset.mode !== tradeMode) return;
        if (tradeMode === 'simulation_win') button.classList.add('active-win');
        else if (tradeMode === 'simulation_loss') button.classList.add('active-loss');
        else button.classList.add('active-normal');
    });
}

async function setTradeMode(mode) {
    const buttons = document.querySelectorAll('.mode-toggle');
    buttons.forEach(button => button.disabled = true);

    try {
        const res = await fetch('<?php echo e(route("admin.live-trades.mode")); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ mode })
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            alert(data.error || 'Mode update failed');
            return;
        }

        renderTradeMode(data.mode);
    } catch (e) {
        console.error('Failed to update trade mode:', e);
        alert('Mode update failed');
    } finally {
        buttons.forEach(button => button.disabled = false);
    }
}

async function loadTrades() {
    try {
        const res = await fetch('<?php echo e(route("admin.live-trades.data")); ?>');
        const data = await res.json();

        const container = document.getElementById('tradeContainer');
        const noTrades  = document.getElementById('no-trades');
        const countEl   = document.getElementById('tradeCount');

        if (data.length === 0) {
            container.innerHTML = '';
            noTrades.style.display = 'block';
            countEl.textContent = '0 active trades';
            return;
        }

        noTrades.style.display = 'none';
        countEl.textContent = data.length + ' active trade' + (data.length !== 1 ? 's' : '');

        // Update or add rows
        const currentIds = new Set(data.map(t => t.trade_id));

        // Remove rows that no longer exist
        container.querySelectorAll('.trade-row').forEach(row => {
            if (!currentIds.has(parseInt(row.dataset.id))) row.remove();
        });

        data.forEach(trade => {
            const existing = container.querySelector(`[data-id="${trade.trade_id}"]`);
            if (existing) {
                // Update countdown from server
                trades[trade.trade_id] = trade.remaining_sec;
                // Update override buttons state
                updateOverrideButtons(trade.trade_id, trade.admin_override);
            } else {
                // Create new row
                trades[trade.trade_id] = trade.remaining_sec;
                const row = createRow(trade);
                container.appendChild(row);
            }
        });

    } catch(e) {
        console.error('Failed to load trades:', e);
    }
}

function createRow(trade) {
    const el = document.createElement('div');
    el.className = 'trade-row';
    el.dataset.id = trade.trade_id;

    const dirClass = trade.direction === 'up' ? 'green' : 'red';
    const dirIcon  = trade.direction === 'up' ? '▲ UP' : '▼ DOWN';
    const profitPct = trade.payout > 0 ? '+' + ((trade.payout - trade.investment) / trade.investment * 100).toFixed(0) + '%' : '—';

    el.innerHTML = `
        <div class="user-info">
            <div class="name">${trade.user_name}</div>
            <div class="sub">$${trade.user_balance} · ${trade.account_age}d old · ${trade.total_trades} trades</div>
        </div>
        <div style="font-weight:600;color:var(--text-primary)">${trade.asset_symbol}</div>
        <div><span class="badge-status ${dirClass}">${dirIcon}</span></div>
        <div>
            <span style="font-weight:700;color:var(--text-primary)">$${trade.investment.toFixed(2)}</span>
            <div style="font-size:11px;color:var(--text-muted)">Payout: $${trade.payout.toFixed(2)}</div>
        </div>
        <div style="color:var(--text-secondary)">${trade.duration_min}m</div>
        <div style="color:var(--green);font-weight:600">${profitPct}</div>
        <div>
            <div class="countdown ok" id="cd-${trade.trade_id}">--:--</div>
        </div>
        <div id="override-${trade.trade_id}">
            ${overrideBadge(trade.admin_override)}
        </div>
        <div class="override-btns">
            <button class="override-win ${trade.admin_override === 'force_win' ? 'active' : ''}"
                    id="btn-win-${trade.trade_id}"
                    onclick="setOverride(${trade.trade_id}, 'win')">✓ WIN</button>
            <button class="override-loss ${trade.admin_override === 'force_loss' ? 'active' : ''}"
                    id="btn-loss-${trade.trade_id}"
                    onclick="setOverride(${trade.trade_id}, 'loss')">✕ LOSS</button>
            <button class="override-reset"
                    onclick="resetOverride(${trade.trade_id})">⟳</button>
        </div>
    `;

    return el;
}

function overrideBadge(override) {
    if (override === 'force_win')  return '<span class="badge-status green">▲ WIN</span>';
    if (override === 'force_loss') return '<span class="badge-status red">▼ LOSS</span>';
    return '<span class="badge-status gray">Auto</span>';
}

function updateOverrideButtons(id, override) {
    const overrideEl = document.getElementById(`override-${id}`);
    if (overrideEl) overrideEl.innerHTML = overrideBadge(override);

    const winBtn  = document.getElementById(`btn-win-${id}`);
    const lossBtn = document.getElementById(`btn-loss-${id}`);
    if (winBtn)  winBtn.className  = `override-win  ${override === 'force_win'  ? 'active' : ''}`;
    if (lossBtn) lossBtn.className = `override-loss ${override === 'force_loss' ? 'active' : ''}`;
}

async function setOverride(tradeId, type) {
    const url = `/admin/live-trades/${tradeId}/force-${type}`;
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const d = await res.json();
    if (d.success) {
        updateOverrideButtons(tradeId, type === 'win' ? 'force_win' : 'force_loss');
    } else {
        alert(d.error || 'Error');
    }
}

async function resetOverride(tradeId) {
    const res = await fetch(`/admin/live-trades/${tradeId}/reset-override`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const d = await res.json();
    if (d.success) updateOverrideButtons(tradeId, 'none');
}

// Live countdown
function tickCountdowns() {
    Object.keys(trades).forEach(id => {
        const el = document.getElementById(`cd-${id}`);
        if (!el) { delete trades[id]; return; }
        const sec = Math.max(0, trades[id] - 1);
        trades[id] = sec;
        const m = Math.floor(sec / 60).toString().padStart(2, '0');
        const s = (sec % 60).toString().padStart(2, '0');
        el.textContent = `${m}:${s}`;
        el.className = `countdown ${sec <= 5 ? 'low' : sec <= 15 ? 'mid' : 'ok'}`;
    });
}

// Refresh timer display
function tickRefresh() {
    refreshCountdown--;
    document.getElementById('refreshTimer').textContent = `Next refresh: ${refreshCountdown}s`;
    if (refreshCountdown <= 0) {
        refreshCountdown = 3;
        loadTrades();
    }
}

// Init
renderTradeMode(tradeMode);
loadTrades();
setInterval(tickCountdowns, 1000);
setInterval(tickRefresh, 1000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/live-trades/index.blade.php ENDPATH**/ ?>