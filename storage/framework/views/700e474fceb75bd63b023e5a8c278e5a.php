<?php $__env->startSection('title', 'Analytics | Quotex'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Full-width container for Analytics */
.page__body,
.page__body--full {
  max-width: 100% !important;
  width: 100% !important;
  margin: 0 !important;
  padding: 16px 32px 48px 32px !important;
  box-sizing: border-box !important;
  background-color: #0f141e !important;
}

.DckO3 {
  width: 100% !important;
  max-width: 1440px !important;
  margin: 0 auto !important;
  padding: 0 !important;
  box-sizing: border-box !important;
}

/* 1. Top Profile Bar */
.analytics__profile {
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 10px;
  padding: 16px 24px;
  margin-bottom: 20px;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  box-sizing: border-box;
}

.analytics__profile-body {
  display: flex;
  align-items: center;
}

.analytics__profile-container {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 48px;
  flex-wrap: wrap;
}

.analytics__profile-block {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.analytics__profile-label {
  font-size: 11px;
  font-weight: 500;
  color: #63738e;
}

.analytics__profile-value {
  font-size: 15px;
  font-weight: 700;
  color: #ffffff;
}

.analytics__profile-tabs {
  display: flex;
  gap: 8px;
  align-items: center;
}

.analytics__profile-tab {
  padding: 6px 16px;
  border-radius: 18px;
  background: #1f2738;
  font-size: 12px;
  font-weight: 600;
  color: #8fa0c2;
  cursor: pointer;
  transition: all 0.15s;
}

.analytics__profile-tab:hover {
  background: #273248;
  color: #ffffff;
}

.analytics__profile-tab.active {
  background: #026FD3;
  color: #ffffff;
}

/* 2. Main 2-Column Grid */
.analytics__body {
  display: grid;
  grid-template-columns: 46% 54%;
  gap: 20px;
  width: 100%;
  box-sizing: border-box;
}

@media (max-width: 1150px) {
  .analytics__body {
    grid-template-columns: 1fr;
  }
  .analytics__profile {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  .page__body,
  .page__body--full {
    padding: 12px 12px 32px 12px !important;
  }
}

.analytics__column {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Base Card Styling */
.analytics__profile-statistics,
.analytics__top-instruments,
.analytics__success,
.analytics__percentage,
.analytics__instruments,
.analytics__distribution-instrument {
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 10px;
  padding: 20px 22px;
  box-sizing: border-box;
}

.analytics__profile-statistics__title,
.analytics__top-instruments-header__title,
.analytics__success-header__title,
.analytics__percentage-header__title,
.analytics__instruments-header__title,
.analytics__distribution-instrument-header__title {
  font-size: 14px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 16px;
  display: block;
}

/* General Data 3x3 Grid */
.analytics__profile-statistics__container {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.analytics__profile-statistics__items {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  background: #131722;
  border: 1px solid #1f2738;
  border-radius: 6px;
  padding: 14px 16px;
}

.analytics__profile-statistics__item {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.analytics__profile-statistics__item-value {
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
}

.analytics__profile-statistics__item-money {
  color: #ffffff;
}

.analytics__profile-statistics__item-label {
  font-size: 11px;
  font-weight: 500;
  color: #63738e;
}

/* Range Bar Navigation */
.analytics__profile-statistics__navigation {
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid #1f2738;
}

.analytics__range-track {
  width: 100%;
  height: 4px;
  background: #232d3f;
  border-radius: 2px;
  position: relative;
  margin-bottom: 10px;
  overflow: hidden;
}

.analytics__range-fill {
  position: absolute;
  height: 100%;
  width: 33.33%;
  left: 33.33%;
  background: linear-gradient(90deg, #ff4c6a 0%, #026FD3 50%, #00e676 100%);
  border-radius: 2px;
}

.analytics__profile-statistics__navigation-labels {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

.analytics__profile-statistics__navigation-label {
  font-size: 11px;
  font-weight: 600;
  color: #63738e;
  cursor: pointer;
  transition: color 0.15s;
}

.analytics__profile-statistics__navigation-label:hover {
  color: #b0c0d8;
}

.analytics__profile-statistics__navigation-label.active {
  color: #026FD3;
}

/* Top 5 Most Profitable Instruments (Donut Chart & Legend) */
.analytics__top-instruments-body {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 48px;
  padding: 10px 10px 4px 10px;
}

@media (max-width: 600px) {
  .analytics__top-instruments-body {
    flex-direction: column;
    gap: 20px;
  }
}

.analytics__pie-wrapper {
  width: 150px;
  height: 150px;
  position: relative;
  flex-shrink: 0;
}

.analytics__pie-legend {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.analytics__legend-row {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12.5px;
  font-weight: 600;
  color: #c9d5e8;
}

.analytics__legend-color-box {
  width: 10px;
  height: 10px;
  border-radius: 2px;
  flex-shrink: 0;
}

.analytics__legend-color-box.c-green  { background: #00c853; }
.analytics__legend-color-box.c-blue   { background: #026FD3; }
.analytics__legend-color-box.c-orange { background: #ff9800; }
.analytics__legend-color-box.c-yellow { background: #ffd600; }
.analytics__legend-color-box.c-red    { background: #f44336; }

/* Right Column: Charts & Blocks */
.analytics-chart-panel {
  position: relative;
  height: 160px;
  background: #131722;
  border-radius: 8px;
  border: 1px solid #1f2738;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 14px 16px 8px;
  box-sizing: border-box;
}

.chart-grid-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
}
.chart-grid-line.line-1 { top: 25%; }
.chart-grid-line.line-2 { top: 50%; }
.chart-grid-line.line-3 { top: 75%; }
.chart-grid-line.line-4 { top: 88%; }
.chart-grid-line.line-5 { top: 12%; }
.chart-grid-line.line-6 { top: 38%; }

.analytics-line-svg {
  position: absolute;
  top: 10px;
  left: 10px;
  right: 10px;
  width: calc(100% - 20px);
  height: 115px;
  z-index: 2;
  pointer-events: none;
}

.analytics-active-tooltip {
  position: absolute;
  background: #1b2333;
  border: 1px solid #2d3b54;
  border-radius: 6px;
  padding: 6px 10px;
  font-size: 11px;
  color: #fff;
  z-index: 5;
  pointer-events: none;
  transform: translate(-50%, -100%);
  white-space: nowrap;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.5);
}

.analytics-active-tooltip .tooltip-time {
  font-size: 10px;
  color: #63738e;
  margin-bottom: 2px;
}

.analytics-active-tooltip .tooltip-val {
  font-size: 11px;
  font-weight: 700;
  color: #00e676;
}

.chart-x-dates {
  position: relative;
  z-index: 3;
  display: flex;
  justify-content: space-between;
  font-size: 10px;
  color: #55647e;
  padding-top: 6px;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  margin-top: auto;
}

.analytics-chart-panel.with-y-axis {
  display: flex;
  flex-direction: row;
  align-items: stretch;
  padding: 10px 16px 8px 10px;
  gap: 12px;
}

.chart-y-axis {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  font-size: 10px;
  color: #55647e;
  height: 115px;
  padding-bottom: 22px;
  box-sizing: border-box;
  width: 24px;
  text-align: right;
}

.chart-body-with-y {
  position: relative;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
}

/* Bottom 2 Instrument Cards */
.analytics__block-instruments {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 600px) {
  .analytics__block-instruments {
    grid-template-columns: 1fr;
  }
}

.analytics__nodata-label {
  height: 90px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 600;
  color: #55647e;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="DckO3" dir="ltr">
  
  <!-- 1. Top Profile Summary Bar (Exact Quotex DOM) -->
  <div class="analytics__profile">
    <div class="analytics__profile-body">
      <div class="analytics__profile-container">
        
        <div class="analytics__profile-block">
          <div class="analytics__profile-label"><?php echo e($user->email); ?></div>
          <div class="analytics__profile-value">ID: <?php echo e($user->id ? (93090570 + $user->id) : '93090578'); ?></div>
        </div>

        <div class="analytics__profile-block">
          <div class="analytics__profile-label">Location</div>
          <div class="analytics__profile-value"><?php echo e($user->country ?? 'Pakistan'); ?></div>
        </div>

        <div class="analytics__profile-block">
          <div class="analytics__profile-label">In the account</div>
          <div class="analytics__profile-value">$<?php echo e(number_format($user->live_balance ?? 0, 2)); ?></div>
        </div>

        <div class="analytics__profile-block">
          <div class="analytics__profile-label">In the demo</div>
          <div class="analytics__profile-value">$<?php echo e(number_format($user->demo_balance ?? 5657.26, 2)); ?></div>
        </div>

      </div>
    </div>
    
    <!-- Time Range Tabs -->
    <div class="analytics__profile-tabs">
      <div class="analytics__profile-tab" onclick="setActiveAnalyticsTab(this, 'today')">Today</div>
      <div class="analytics__profile-tab" onclick="setActiveAnalyticsTab(this, 'yesterday')">Yesterday</div>
      <div class="analytics__profile-tab active" onclick="setActiveAnalyticsTab(this, 'week')">Week</div>
      <div class="analytics__profile-tab" onclick="setActiveAnalyticsTab(this, 'month')">Month</div>
    </div>
  </div>

  <!-- 2. Main Analytics Grid -->
  <div class="analytics__body">
    
    <!-- Left Column: General Data & Top Instruments -->
    <div class="analytics__column">
      
      <!-- General Data Card -->
      <div class="analytics__profile-statistics">
        <div class="analytics__profile-statistics__title">General data</div>
        
        <div class="analytics__profile-statistics__container">
          
          <!-- Row 1 -->
          <div class="analytics__profile-statistics__items">
            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money"><?php echo e($stats['trades_count'] ?? 0); ?></div>
              </div>
              <div class="analytics__profile-statistics__item-label">Trades count</div>
            </div>

            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money"><?php echo e(number_format($stats['trades_profit'] ?? 0, 0)); ?>$</div>
              </div>
              <div class="analytics__profile-statistics__item-label">Trades profit</div>
            </div>

            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money"><?php echo e($stats['profitable_trades'] ?? 0); ?></div>
              </div>
              <div class="analytics__profile-statistics__item-label">Profitable trades</div>
            </div>
          </div>

          <!-- Row 2 -->
          <div class="analytics__profile-statistics__items">
            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money"><?php echo e(number_format($stats['avg_profit'] ?? 0, 0)); ?>$</div>
              </div>
              <div class="analytics__profile-statistics__item-label">Average profit</div>
            </div>

            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money"><?php echo e(number_format($stats['net_turnover'] ?? 0, 0)); ?>$</div>
              </div>
              <div class="analytics__profile-statistics__item-label">Net turnover</div>
            </div>

            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money">0$</div>
              </div>
              <div class="analytics__profile-statistics__item-label">Hedged trades</div>
            </div>
          </div>

          <!-- Row 3 -->
          <div class="analytics__profile-statistics__items">
            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money"><?php echo e(number_format($stats['min_trade'] ?? 0, 0)); ?>$</div>
              </div>
              <div class="analytics__profile-statistics__item-label">Min trade amount</div>
            </div>

            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money"><?php echo e(number_format($stats['max_trade'] ?? 0, 0)); ?>$</div>
              </div>
              <div class="analytics__profile-statistics__item-label">Max trade amount</div>
            </div>

            <div class="analytics__profile-statistics__item">
              <div class="analytics__profile-statistics__item-value">
                <div class="analytics__profile-statistics__item-money"><?php echo e(number_format($stats['max_profit'] ?? 0, 0)); ?>$</div>
              </div>
              <div class="analytics__profile-statistics__item-label">Max trade profit</div>
            </div>
          </div>

        </div>

        <!-- Range Navigation -->
        <div class="analytics__profile-statistics__navigation">
          <div class="analytics__range-track">
            <div class="analytics__range-fill"></div>
          </div>
          <div class="analytics__profile-statistics__navigation-labels">
            <div class="analytics__profile-statistics__navigation-label" onclick="setRangeLabel(this)">-1K–0</div>
            <div class="analytics__profile-statistics__navigation-label active" onclick="setRangeLabel(this)">0–1K</div>
            <div class="analytics__profile-statistics__navigation-label" onclick="setRangeLabel(this)">+1K</div>
          </div>
        </div>
      </div>

      <!-- Top 5 Most Profitable Instruments -->
      <div class="analytics__top-instruments">
        <div class="analytics__top-instruments-header">
          <span class="analytics__top-instruments-header__title">
            Top 5 most profitable instruments among traders
          </span>
        </div>

        <div class="analytics__top-instruments-body">
          <!-- Pie Chart -->
          <div class="analytics__pie-wrapper">
            <svg viewBox="0 0 100 100" width="150" height="150" style="transform: rotate(-90deg);">
              <!-- Segment 1: Green 37% (0 to 37%) -->
              <circle cx="50" cy="50" r="25" fill="none" stroke="#00c853" stroke-width="50"
                      stroke-dasharray="58.12 157.08" stroke-dashoffset="0" />
              <!-- Segment 2: Blue 18% (37% to 55%) -->
              <circle cx="50" cy="50" r="25" fill="none" stroke="#026FD3" stroke-width="50"
                      stroke-dasharray="28.27 157.08" stroke-dashoffset="-58.12" />
              <!-- Segment 3: Orange 16% (55% to 71%) -->
              <circle cx="50" cy="50" r="25" fill="none" stroke="#ff9800" stroke-width="50"
                      stroke-dasharray="25.13 157.08" stroke-dashoffset="-86.39" />
              <!-- Segment 4: Yellow 15% (71% to 86%) -->
              <circle cx="50" cy="50" r="25" fill="none" stroke="#ffd600" stroke-width="50"
                      stroke-dasharray="23.56 157.08" stroke-dashoffset="-111.52" />
              <!-- Segment 5: Red 14% (86% to 100%) -->
              <circle cx="50" cy="50" r="25" fill="none" stroke="#f44336" stroke-width="50"
                      stroke-dasharray="21.99 157.08" stroke-dashoffset="-135.08" />
            </svg>
          </div>

          <!-- Legend -->
          <div class="analytics__pie-legend">
            <div class="analytics__legend-row">
              <span class="analytics__legend-color-box c-green"></span>
              <span>EUR_USD_otc 37%</span>
            </div>
            <div class="analytics__legend-row">
              <span class="analytics__legend-color-box c-blue"></span>
              <span>NZD_CAD_otc 18%</span>
            </div>
            <div class="analytics__legend-row">
              <span class="analytics__legend-color-box c-orange"></span>
              <span>EUR_CAD_otc 16%</span>
            </div>
            <div class="analytics__legend-row">
              <span class="analytics__legend-color-box c-yellow"></span>
              <span>USD_SGD_otc 15%</span>
            </div>
            <div class="analytics__legend-row">
              <span class="analytics__legend-color-box c-red"></span>
              <span>USD_MXN_otc 14%</span>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Right Column: Statistics & Distribution -->
    <div class="analytics__column">
      
      <!-- Statistics of profitable trades chart -->
      <div class="analytics__success">
        <div class="analytics__success-header">
          <span class="analytics__success-header__title">Statistics of profitable trades</span>
        </div>
        <div class="analytics-chart-panel">
          <div class="chart-grid-line line-1"></div>
          <div class="chart-grid-line line-2"></div>
          <div class="chart-grid-line line-3"></div>
          <div class="chart-grid-line line-4"></div>

          <!-- Green Line Trajectory -->
          <svg class="analytics-line-svg" viewBox="0 0 1000 115" preserveAspectRatio="none">
            <line x1="0" y1="58" x2="1000" y2="58" stroke="#00c853" stroke-width="2" />
          </svg>

          <!-- Dates along X axis -->
          <?php
            $dates = [];
            for ($i = 7; $i >= 0; $i--) {
                $dates[] = now()->subDays($i)->format('j.M');
            }
          ?>
          <div class="chart-x-dates">
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <span><?php echo e($d); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
      </div>

      <!-- Percentage % of profitable trades chart -->
      <div class="analytics__percentage">
        <div class="analytics__percentage-header">
          <span class="analytics__percentage-header__title">Percentage % of profitable trades</span>
        </div>
        
        <div class="analytics-chart-panel with-y-axis">
          <!-- Y-axis scale labels -->
          <div class="chart-y-axis">
            <span>100</span>
            <span>75</span>
            <span>50</span>
            <span>25</span>
            <span>0</span>
          </div>

          <div class="chart-body-with-y">
            <div class="chart-grid-line line-1"></div>
            <div class="chart-grid-line line-2"></div>
            <div class="chart-grid-line line-3"></div>
            <div class="chart-grid-line line-4"></div>
            <div class="chart-grid-line line-5"></div>
            <div class="chart-grid-line line-6"></div>

            <!-- Trajectory SVG -->
            <svg class="analytics-line-svg" viewBox="0 0 1000 115" preserveAspectRatio="none">
              <line x1="0" y1="95" x2="1000" y2="95" stroke="#00c853" stroke-width="2" />
            </svg>

            <!-- Active Tooltip Badge matching Quotex screenshot -->
            <div class="analytics-active-tooltip" style="left: 14%; top: 78px;">
              <div class="tooltip-time">Profitable: 0 (0%)</div>
              <div class="tooltip-val" style="color:#63738e; font-weight:500;">Non-profitable: 0 (0%)</div>
            </div>

            <!-- Dates along X axis -->
            <div class="chart-x-dates">
              <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span><?php echo e($d); ?></span>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Instruments Grid (2 boxes) -->
      <div class="analytics__block-instruments">
        <div class="analytics__block-instrument">
          <div class="analytics__instruments">
            <div class="analytics__instruments-header">
              <span class="analytics__instruments-header__title">Statistics Profit &amp; Loss by instruments</span>
            </div>
            <div class="analytics__instruments__block">
              <div class="analytics__nodata-label">No data</div>
            </div>
          </div>
        </div>

        <div class="analytics__block-instrument">
          <div class="analytics__distribution-instrument">
            <div class="analytics__distribution-instrument-header">
              <span class="analytics__distribution-instrument-header__title">Distribution of trades by instruments, %</span>
            </div>
            <div class="analytics__distribution-instrument-header__block">
              <div class="analytics__nodata-label">No data</div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<script>
function setActiveAnalyticsTab(el, range) {
  document.querySelectorAll('.analytics__profile-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}

function setRangeLabel(el) {
  document.querySelectorAll('.analytics__profile-statistics__navigation-label').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/account/analytics.blade.php ENDPATH**/ ?>