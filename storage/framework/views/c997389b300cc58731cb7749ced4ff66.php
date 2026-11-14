<?php $__env->startSection('title', 'Tournaments | Quotex'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .page__body--full {
    padding: 0 !important;
    max-width: 100% !important;
  }

  .tournaments-wrapper {
    background: #111622;
    padding: 24px 32px 60px 32px;
    min-height: calc(100vh - 120px);
    box-sizing: border-box;
    width: 100%;
  }

  .tournaments-main-title {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 18px;
  }

  /* Sub-tabs */
  .tournaments-nav-tabs {
    display: flex;
    align-items: center;
    gap: 32px;
    border-bottom: 1px solid #1f2738;
    margin-bottom: 32px;
  }

  .tournaments-nav-tab {
    padding: 10px 4px 14px 4px;
    font-size: 13px;
    font-weight: 700;
    color: #63738e;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: color 0.2s;
  }

  .tournaments-nav-tab:hover {
    color: #ffffff;
  }

  .tournaments-nav-tab.active {
    color: #ffffff;
  }

  .tournaments-nav-tab.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 2px;
    background: #026FD3;
  }

  .tournaments-count-pill {
    background: #026FD3;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  /* Section Title */
  .tournaments-section-heading {
    text-align: center;
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 30px;
  }

  /* Cards Grid */
  .tournaments-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    max-width: 1280px;
    margin: 0 auto;
    box-sizing: border-box;
  }

  @media (max-width: 900px) {
    .tournaments-grid {
      grid-template-columns: 1fr;
    }
  }

  /* Card */
  .qx-tournament-card {
    background: #171d2b;
    border: 1px solid #232d3f;
    border-radius: 8px;
    padding: 24px 28px;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    transition: border-color 0.2s, transform 0.2s;
  }

  .qx-tournament-card:hover {
    border-color: #2e3d55;
  }

  /* Card Header Row */
  .qx-tcard-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    position: relative;
    z-index: 2;
  }

  .qx-tcard-badge {
    background: #026FD3;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 9px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .qx-tcard-badge.completed-badge {
    background: #374151;
    color: #9ca3af;
  }

  .qx-tcard-prize-box {
    text-align: right;
  }

  .qx-tcard-prize-lbl {
    font-size: 11px;
    font-weight: 700;
    color: #60718d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .qx-tcard-prize-val {
    font-size: 22px;
    font-weight: 800;
    color: #00e676;
    margin-top: 2px;
    letter-spacing: 0.5px;
  }

  /* Card Title & Stats */
  .qx-tcard-title {
    font-size: 22px;
    font-weight: 700;
    color: #ffffff;
    margin: 18px 0 20px 0;
    position: relative;
    z-index: 2;
  }

  .qx-tcard-stats-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 40px;
    margin-bottom: 24px;
    position: relative;
    z-index: 2;
  }

  .qx-tcard-stat {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }

  .qx-tcard-stat-val {
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
  }

  .qx-tcard-stat-lbl {
    font-size: 11px;
    font-weight: 600;
    color: #60718d;
    margin-top: 2px;
  }

  /* Details Button */
  .qx-tcard-btn {
    width: 100%;
    background: #1f283a;
    border: none;
    border-radius: 6px;
    color: #b0c0d8;
    padding: 13px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    font-family: inherit;
    position: relative;
    z-index: 2;
  }

  .qx-tcard-btn:hover {
    background: #27344c;
    color: #ffffff;
  }

  .btn-info-icon {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #b0c0d8;
    color: #171d2b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 800;
    font-family: serif;
  }

  .qx-tcard-btn:hover .btn-info-icon {
    background: #ffffff;
  }

  /* Modal */
  .qx-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
  }

  .qx-modal-content {
    background: #171d2b;
    border: 1px solid #28354c;
    border-radius: 10px;
    width: 540px;
    max-width: 92%;
    padding: 28px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
    position: relative;
    color: #ffffff;
  }

  .qx-modal-close {
    position: absolute;
    top: 18px;
    right: 20px;
    background: none;
    border: none;
    color: #63738e;
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
  }

  .qx-modal-close:hover {
    color: #ffffff;
  }

  .qx-modal-header-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 16px;
  }

  .qx-modal-prizes-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 16px 0 24px 0;
  }

  .qx-modal-prize-row {
    display: flex;
    justify-content: space-between;
    background: #131722;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
  }

  .qx-modal-btn-participate {
    width: 100%;
    background: #00c853;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    padding: 13px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
  }

  .qx-modal-btn-participate:hover {
    background: #00e676;
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  // If no database tournaments found, provide default Quotex tournaments
  $hasDbActive = isset($activeTournaments) && $activeTournaments->isNotEmpty();
  $hasDbCompleted = isset($completedTournaments) && $completedTournaments->isNotEmpty();

  $activeCount = $hasDbActive ? $activeTournaments->count() : 3;
  $completedCount = $hasDbCompleted ? $completedTournaments->count() : 3;
?>

<div class="tournaments-wrapper">
  <div class="tournaments-main-title">Tournaments</div>

  <!-- Sub-Tabs -->
  <div class="tournaments-nav-tabs">
    <div class="tournaments-nav-tab active" id="tab-active" onclick="switchTournamentsTab('active')">
      <span>Active</span>
      <span class="tournaments-count-pill"><?php echo e($activeCount); ?></span>
    </div>
    <div class="tournaments-nav-tab" id="tab-completed" onclick="switchTournamentsTab('completed')">
      <span>Completed</span>
    </div>
  </div>

  <!-- Section Heading -->
  <div class="tournaments-section-heading" id="tournaments-heading">
    Available for participation (<?php echo e($activeCount); ?>)
  </div>

  <!-- Active Tournaments View -->
  <div class="tournaments-grid" id="grid-active">
    <?php if($hasDbActive): ?>
      <?php $__currentLoopData = $activeTournaments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="qx-tournament-card">
          <div class="qx-tcard-top">
            <div class="qx-tcard-badge">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span><?php echo e($t->badge_text ?: 'Until start: 1 day(s)'); ?></span>
            </div>
            <div class="qx-tcard-prize-box">
              <div class="qx-tcard-prize-lbl">Prize pool</div>
              <div class="qx-tcard-prize-val"><?php echo e(number_format($t->prize_pool, 0, '.', ' ')); ?> $</div>
            </div>
          </div>
          <div class="qx-tcard-title"><?php echo e($t->title); ?></div>
          <div class="qx-tcard-stats-row">
            <div class="qx-tcard-stat">
              <div class="qx-tcard-stat-val"><?php echo e(number_format($t->entry_fee, 0, '.', ' ')); ?> $</div>
              <div class="qx-tcard-stat-lbl">Entry fee</div>
            </div>
            <div class="qx-tcard-stat">
              <div class="qx-tcard-stat-val"><?php echo e($t->duration); ?></div>
              <div class="qx-tcard-stat-lbl">Duration</div>
            </div>
          </div>
          <a href="<?php echo e(route('tournament.detail', $t->id)); ?>" class="qx-tcard-btn" style="text-decoration:none;">
            <span>Details</span>
            <span class="btn-info-icon">i</span>
          </a>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
      <!-- Default Quotex Active Tournaments -->
      <div class="qx-tournament-card">
        <div class="qx-tcard-top">
          <div class="qx-tcard-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Until start: 1 day(s)</span>
          </div>
          <div class="qx-tcard-prize-box">
            <div class="qx-tcard-prize-lbl">Prize pool</div>
            <div class="qx-tcard-prize-val">7 500 $</div>
          </div>
        </div>
        <div class="qx-tcard-title">Crazy Wednesday</div>
        <div class="qx-tcard-stats-row">
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">10 $</div>
            <div class="qx-tcard-stat-lbl">Entry fee</div>
          </div>
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">1 day</div>
            <div class="qx-tcard-stat-lbl">Duration</div>
          </div>
        </div>
        <a href="<?php echo e(route('tournament.detail', 1)); ?>" class="qx-tcard-btn" style="text-decoration:none;">
          <span>Details</span>
          <span class="btn-info-icon">i</span>
        </a>
      </div>

      <div class="qx-tournament-card">
        <div class="qx-tcard-top">
          <div class="qx-tcard-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Until start: 3 day(s)</span>
          </div>
          <div class="qx-tcard-prize-box">
            <div class="qx-tcard-prize-lbl">Prize pool</div>
            <div class="qx-tcard-prize-val">1 000 $</div>
          </div>
        </div>
        <div class="qx-tcard-title">Free Friday</div>
        <div class="qx-tcard-stats-row">
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">0 $</div>
            <div class="qx-tcard-stat-lbl">Entry fee</div>
          </div>
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">1 day</div>
            <div class="qx-tcard-stat-lbl">Duration</div>
          </div>
        </div>
        <a href="<?php echo e(route('tournament.detail', 2)); ?>" class="qx-tcard-btn" style="text-decoration:none;">
          <span>Details</span>
          <span class="btn-info-icon">i</span>
        </a>
      </div>

      <div class="qx-tournament-card">
        <div class="qx-tcard-top">
          <div class="qx-tcard-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Until start: 4 day(s)</span>
          </div>
          <div class="qx-tcard-prize-box">
            <div class="qx-tcard-prize-lbl">Prize pool</div>
            <div class="qx-tcard-prize-val">5 000 $</div>
          </div>
        </div>
        <div class="qx-tcard-title">Weekend Battle</div>
        <div class="qx-tcard-stats-row">
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">1 $</div>
            <div class="qx-tcard-stat-lbl">Entry fee</div>
          </div>
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">2 days</div>
            <div class="qx-tcard-stat-lbl">Duration</div>
          </div>
        </div>
        <a href="<?php echo e(route('tournament.detail', 3)); ?>" class="qx-tcard-btn" style="text-decoration:none;">
          <span>Details</span>
          <span class="btn-info-icon">i</span>
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Completed Tournaments View -->
  <div class="tournaments-grid" id="grid-completed" style="display: none;">
    <?php if($hasDbCompleted): ?>
      <?php $__currentLoopData = $completedTournaments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="qx-tournament-card">
          <div class="qx-tcard-top">
            <div class="qx-tcard-badge completed-badge">
              <span><?php echo e($t->badge_text ?: 'Finished'); ?></span>
            </div>
            <div class="qx-tcard-prize-box">
              <div class="qx-tcard-prize-lbl">Prize pool</div>
              <div class="qx-tcard-prize-val"><?php echo e(number_format($t->prize_pool, 0, '.', ' ')); ?> $</div>
            </div>
          </div>
          <div class="qx-tcard-title"><?php echo e($t->title); ?></div>
          <div class="qx-tcard-stats-row">
            <div class="qx-tcard-stat">
              <div class="qx-tcard-stat-val"><?php echo e(number_format($t->entry_fee, 0, '.', ' ')); ?> $</div>
              <div class="qx-tcard-stat-lbl">Entry fee</div>
            </div>
            <div class="qx-tcard-stat">
              <div class="qx-tcard-stat-val"><?php echo e($t->duration); ?></div>
              <div class="qx-tcard-stat-lbl">Duration</div>
            </div>
          </div>
          <a href="<?php echo e(route('tournament.detail', $t->id)); ?>" class="qx-tcard-btn" style="text-decoration:none;">
            <span>Details</span>
            <span class="btn-info-icon">i</span>
          </a>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
      <!-- Default Completed Tournaments -->
      <div class="qx-tournament-card">
        <div class="qx-tcard-top">
          <div class="qx-tcard-badge completed-badge">
            <span>Finished</span>
          </div>
          <div class="qx-tcard-prize-box">
            <div class="qx-tcard-prize-lbl">Prize pool</div>
            <div class="qx-tcard-prize-val">7 500 $</div>
          </div>
        </div>
        <div class="qx-tcard-title">Crazy Wednesday</div>
        <div class="qx-tcard-stats-row">
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">10 $</div>
            <div class="qx-tcard-stat-lbl">Entry fee</div>
          </div>
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">1 day</div>
            <div class="qx-tcard-stat-lbl">Duration</div>
          </div>
        </div>
        <button type="button" class="qx-tcard-btn" onclick="openTournamentModal('Crazy Wednesday', '7 500 $', '10 $', '1 day')">
          <span>Details</span>
          <span class="btn-info-icon">i</span>
        </button>
      </div>

      <div class="qx-tournament-card">
        <div class="qx-tcard-top">
          <div class="qx-tcard-badge completed-badge">
            <span>Finished</span>
          </div>
          <div class="qx-tcard-prize-box">
            <div class="qx-tcard-prize-lbl">Prize pool</div>
            <div class="qx-tcard-prize-val">5 000 $</div>
          </div>
        </div>
        <div class="qx-tcard-title">Weekend Battle</div>
        <div class="qx-tcard-stats-row">
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">1 $</div>
            <div class="qx-tcard-stat-lbl">Entry fee</div>
          </div>
          <div class="qx-tcard-stat">
            <div class="qx-tcard-stat-val">2 days</div>
            <div class="qx-tcard-stat-lbl">Duration</div>
          </div>
        </div>
        <button type="button" class="qx-tcard-btn" onclick="openTournamentModal('Weekend Battle', '5 000 $', '1 $', '2 days')">
          <span>Details</span>
          <span class="btn-info-icon">i</span>
        </button>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal -->
<div class="qx-modal-backdrop" id="t-modal" onclick="closeTournamentModal(event)">
  <div class="qx-modal-content" onclick="event.stopPropagation()">
    <button type="button" class="qx-modal-close" onclick="closeTournamentModal()">&times;</button>
    <div class="qx-modal-header-title" id="m-title">Tournament Details</div>
    <div style="color: #7d8ea9; font-size: 13px; line-height: 1.5; margin-bottom: 16px;" id="m-desc">
      Compete with other traders and climb the leaderboard. The highest balance at the end of the duration wins the prize pool!
    </div>

    <div style="font-weight: 700; font-size: 14px; margin-bottom: 10px;">Prize Distribution:</div>
    <div class="qx-modal-prizes-list">
      <div class="qx-modal-prize-row">
        <span style="color: #ffd700; font-weight: 700;">🥇 1st Place</span>
        <b style="color: #00e676;" id="m-p1">40% of Pool</b>
      </div>
      <div class="qx-modal-prize-row">
        <span style="color: #c0c0c0; font-weight: 700;">🥈 2nd Place</span>
        <b style="color: #00e676;" id="m-p2">25% of Pool</b>
      </div>
      <div class="qx-modal-prize-row">
        <span style="color: #cd7f32; font-weight: 700;">🥉 3rd Place</span>
        <b style="color: #00e676;" id="m-p3">15% of Pool</b>
      </div>
      <div class="qx-modal-prize-row">
        <span style="color: #8b9ab4;">4th - 10th Place</span>
        <b style="color: #00e676;">20% Shared</b>
      </div>
    </div>

    <div style="display: flex; justify-content: space-between; font-size: 13px; color: #8b9ab4; margin-bottom: 20px;">
      <div>Entry Fee: <b style="color: #fff;" id="m-fee">0 $</b></div>
      <div>Duration: <b style="color: #fff;" id="m-dur">1 day</b></div>
    </div>

    <button type="button" class="qx-modal-btn-participate" onclick="participateTournament()">
      Participate in Tournament
    </button>
  </div>
</div>

<script>
  function switchTournamentsTab(tab) {
    const tabActive = document.getElementById('tab-active');
    const tabCompleted = document.getElementById('tab-completed');
    const gridActive = document.getElementById('grid-active');
    const gridCompleted = document.getElementById('grid-completed');
    const heading = document.getElementById('tournaments-heading');

    if (tab === 'active') {
      tabActive.classList.add('active');
      tabCompleted.classList.remove('active');
      gridActive.style.display = 'grid';
      gridCompleted.style.display = 'none';
      heading.textContent = 'Available for participation (<?php echo e($activeCount); ?>)';
    } else {
      tabCompleted.classList.add('active');
      tabActive.classList.remove('active');
      gridActive.style.display = 'none';
      gridCompleted.style.display = 'grid';
      heading.textContent = 'Completed tournaments (<?php echo e($completedCount); ?>)';
    }
  }

  function openTournamentModal(title, prize, fee, dur, desc) {
    document.getElementById('m-title').textContent = title + ' (' + prize + ')';
    document.getElementById('m-fee').textContent = fee;
    document.getElementById('m-dur').textContent = dur;
    if (desc) {
      document.getElementById('m-desc').textContent = desc;
    } else {
      document.getElementById('m-desc').textContent = 'Compete with other traders and climb the leaderboard. The highest balance at the end of the duration wins the prize pool!';
    }
    document.getElementById('t-modal').style.display = 'flex';
  }

  function closeTournamentModal() {
    document.getElementById('t-modal').style.display = 'none';
  }

  function participateTournament() {
    alert('You have successfully registered for the tournament! Head to Trade to participate.');
    closeTournamentModal();
  }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/account/tournaments.blade.php ENDPATH**/ ?>