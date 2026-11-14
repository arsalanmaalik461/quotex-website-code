<?php $__env->startSection('title', $tournament->title . ' | Quotex Tournaments'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  /* Full width container for Tournament Details */
  .page__body--full {
    padding: 0 !important;
    max-width: 100% !important;
  }

  .tournament-detail-page {
    background: #0f141e;
    padding: 24px 32px 48px 32px;
    min-height: calc(100vh - 120px);
    box-sizing: border-box;
    width: 100%;
  }

  .DckO3 {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
  }

  .tournament-title {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 8px;
  }

  .tournament__back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #026FD3;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 24px;
    transition: color 0.2s;
  }

  .tournament__back:hover {
    color: #2196f3;
  }

  .tournament__back::before {
    content: '';
    display: inline-block;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #026FD3;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='15 18 9 12 15 6'%3E%3C/polyline%3E%3C/svg%3E");
    background-size: 11px;
    background-position: center;
    background-repeat: no-repeat;
  }

  /* 3-Column Container */
  .tournament-container {
    display: grid;
    grid-template-columns: 1.8fr 1fr 1fr;
    gap: 24px;
    align-items: flex-start;
  }

  @media (max-width: 1200px) {
    .tournament-container {
      grid-template-columns: 1fr 1fr;
    }
  }

  @media (max-width: 800px) {
    .tournament-container {
      grid-template-columns: 1fr;
    }
    .tournament-detail-page {
      padding: 16px 12px 32px 12px;
    }
  }

  /* Left Column: Hero Banner + Stats Grid + Description */
  .tournament-container__information {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* Hero Banner Card */
  .tournamentInformation__body {
    background: linear-gradient(135deg, #1b2333 0%, #171d2b 100%);
    border: 1px solid #232d3f;
    border-radius: 12px;
    padding: 24px 28px;
    position: relative;
    overflow: hidden;
    min-height: 180px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
  }

  .tournamentInformation__body::after {
    content: '';
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 160px;
    height: 160px;
    background: radial-gradient(circle, rgba(2, 111, 211, 0.15) 0%, rgba(2, 111, 211, 0) 70%);
    pointer-events: none;
  }

  .tournamentInformation__body-label {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(2, 111, 211, 0.2);
    border: 1px solid rgba(2, 111, 211, 0.4);
    color: #60a5fa;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 4px 12px;
    border-radius: 20px;
  }

  .tournamentInformation__body-label::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #3b82f6;
  }

  .tournamentInformation__body-footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-top: 24px;
    gap: 20px;
    flex-wrap: wrap;
  }

  .tournamentInformation__body-title {
    font-size: 28px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.15;
  }

  .tournament-hero-right {
    display: flex;
    align-items: center;
    gap: 20px;
  }

  .tournamentInformation__body-prize__block {
    text-align: right;
  }

  .tournamentInformation__body-prize__title {
    font-size: 11px;
    font-weight: 700;
    color: #63738e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
  }

  .tournamentInformation__body-prize {
    font-size: 26px;
    font-weight: 800;
    color: #00e676;
    line-height: 1;
  }

  .tournamentInformation__body-button {
    background: #00c853;
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    text-decoration: none;
  }

  .tournamentInformation__body-button:hover {
    background: #00e676;
  }

  .tournamentInformation__body-button:active {
    transform: scale(0.98);
  }

  /* Info Grid (4 Blocks) */
  .tournamentInformation-info {
    background: #171d2b;
    border: 1px solid #232d3f;
    border-radius: 12px;
    padding: 24px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
  }

  @media (max-width: 900px) {
    .tournamentInformation-info {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .tournamentInformation-info__block {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .tournamentInformation-info__container-time,
  .tournamentInformation-info__container {
    display: flex;
    flex-direction: column;
  }

  .tournamentInformation-info-utc {
    font-size: 10px;
    font-weight: 700;
    color: #63738e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 3px;
  }

  .tournamentInformation-info__value {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 3px;
  }

  .tournamentInformation-info__label {
    font-size: 11px;
    font-weight: 600;
    color: #63738e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Description Section */
  .tournamentInformation__description {
    background: #171d2b;
    border: 1px solid #232d3f;
    border-radius: 12px;
    padding: 24px 28px;
  }

  .tournamentInformation__description-title {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 14px;
  }

  .tournamentInformation__description-text {
    color: #94a3b8;
    font-size: 13.5px;
    line-height: 1.65;
  }

  /* Middle Column: Prize Pool Distribution */
  .prize-distribution {
    background: #171d2b;
    border: 1px solid #232d3f;
    border-radius: 12px;
    padding: 24px;
  }

  .prize-distribution__title {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .prize-distribution__header {
    display: flex;
    justify-content: space-between;
    padding: 0 12px 10px 12px;
    font-size: 11px;
    font-weight: 700;
    color: #63738e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #232d3f;
  }

  .prize-distribution__header-price {
    text-align: right;
  }

  .prize-distribution__item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
    margin-top: 4px;
    transition: background 0.15s;
  }

  .prize-distribution__item.grey {
    background: #131722;
  }

  .prize-distribution__item:hover {
    background: #1c2436;
  }

  .prize-distribution__item-price {
    color: #00e676;
    font-weight: 700;
    font-size: 13.5px;
  }

  /* Right Column: FAQ */
  .tournament-container__faq {
    background: #171d2b;
    border: 1px solid #232d3f;
    border-radius: 12px;
    padding: 24px;
  }

  .faq-tournaments__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
  }

  .faq-tournaments__title {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
  }

  .faq-tournaments__link {
    color: #026FD3;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .faq-tournaments__link:hover {
    color: #2196f3;
  }

  .faq-tournaments-item {
    border-bottom: 1px solid #212a3b;
    padding: 14px 0;
    cursor: pointer;
  }

  .faq-tournaments-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .faq-tournaments-item__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
  }

  .faq-tournaments-item__name {
    font-size: 13px;
    font-weight: 600;
    color: #b0c0d8;
    transition: color 0.15s;
    line-height: 1.4;
  }

  .faq-tournaments-item:hover .faq-tournaments-item__name {
    color: #ffffff;
  }

  .faq-chevron {
    color: #63738e;
    font-size: 11px;
    transition: transform 0.2s;
    flex-shrink: 0;
  }

  .faq-tournaments-item.open .faq-chevron {
    transform: rotate(180deg);
  }

  .faq-tournaments-item__answer {
    display: none;
    padding-top: 10px;
    font-size: 12.5px;
    line-height: 1.55;
    color: #8a98b0;
  }

  .faq-tournaments-item.open .faq-tournaments-item__answer {
    display: block;
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="tournament-detail-page">
  <div class="DckO3">
    
    <div class="tournament-title">
      Tournaments
    </div>

    <a class="tournament__back" href="<?php echo e(route('tournaments')); ?>">
      Return back
    </a>

    <div class="tournament-container">
      
      
      <div class="tournament-container__information active">
        
        
        <div class="tournamentInformation__body">
          <div class="tournamentInformation__body-label">
            <?php echo e($tournament->badge_text ?: 'Until start: 1 day(s)'); ?>

          </div>

          <div class="tournamentInformation__body-footer">
            <div class="tournamentInformation__body-title">
              <?php echo e($tournament->title); ?>

            </div>

            <div class="tournament-hero-right">
              <div class="tournamentInformation__body-prize__block">
                <div class="tournamentInformation__body-prize__title">
                  Prize pool
                </div>
                <div class="tournamentInformation__body-prize">
                  <?php echo e(number_format($tournament->prize_pool, 0, '.', '')); ?>$
                </div>
              </div>

              <?php if($tournament->entry_fee > 0): ?>
                <button type="button" class="KtjVk JQZcs _5qIw tournamentInformation__body-button" onclick="joinTournamentModal('<?php echo e(addslashes($tournament->title)); ?>', '<?php echo e(number_format($tournament->entry_fee, 0)); ?>$')">
                  <span class="oQ4Z4">
                    Join now — <?php echo e(number_format($tournament->entry_fee, 0, '.', '')); ?>$
                  </span>
                </button>
              <?php else: ?>
                <button type="button" class="KtjVk JQZcs _5qIw tournamentInformation__body-button" onclick="joinTournamentModal('<?php echo e(addslashes($tournament->title)); ?>', 'Free')">
                  <span class="oQ4Z4">
                    Join now — Free
                  </span>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>

        
        <div class="tournamentInformation-info">
          
          <div class="tournamentInformation-info__block">
            <div class="tournamentInformation-info__container-time">
              <div class="tournamentInformation-info-utc">
                <?php echo e($tournament->timezone_label ?: 'UTC+05:00'); ?>

              </div>
              <div class="tournamentInformation-info__value">
                <?php echo e($tournament->formatted_start); ?>

              </div>
              <div class="tournamentInformation-info__label">
                Start
              </div>
            </div>

            <div>
              <div class="tournamentInformation-info-utc">
                <?php echo e($tournament->timezone_label ?: 'UTC+05:00'); ?>

              </div>
              <div class="tournamentInformation-info__value">
                <?php echo e($tournament->formatted_end); ?>

              </div>
              <div class="tournamentInformation-info__label">
                End
              </div>
            </div>
          </div>

          
          <div class="tournamentInformation-info__block">
            <div class="tournamentInformation-info__container">
              <div class="tournamentInformation-info__value">
                <?php echo e(number_format($tournament->prize_pool, 0, '.', '')); ?>$
              </div>
              <div class="tournamentInformation-info__label">
                Prize pool
              </div>
            </div>

            <div>
              <div class="tournamentInformation-info__value">
                <?php echo e($tournament->number_of_rebuys ?? 100); ?>

              </div>
              <div class="tournamentInformation-info__label">
                Number of rebuys
              </div>
            </div>
          </div>

          
          <div class="tournamentInformation-info__block">
            <div class="tournamentInformation-info__container">
              <div class="tournamentInformation-info__value">
                <?php echo e(number_format($tournament->entry_fee, 0, '.', '')); ?>$
              </div>
              <div class="tournamentInformation-info__label">
                Entry fee
              </div>
            </div>

            <div>
              <div class="tournamentInformation-info__value">
                <?php echo e($tournament->duration); ?>

              </div>
              <div class="tournamentInformation-info__label">
                Duration
              </div>
            </div>
          </div>

          
          <div class="tournamentInformation-info__block">
            <div class="tournamentInformation-info__container">
              <div class="tournamentInformation-info__value">
                <?php echo e(number_format($tournament->rebuy_cost ?? $tournament->entry_fee ?? 10.00, 2)); ?>$
              </div>
              <div class="tournamentInformation-info__label">
                Rebuy cost
              </div>
            </div>
          </div>
        </div>

        
        <div class="tournamentInformation__description">
          <div class="tournamentInformation__description-header">
            <div class="tournamentInformation__description-title">
              Description
            </div>
          </div>
          <div class="tournamentInformation__description-text">
            <?php echo e($tournament->description ?: 'Total prize pool is $' . number_format($tournament->prize_pool, 0) . '. To participate you need to register and pay the entrance fee, after that $100 will be deposited to a special tournament account. Initial conditions in the tournament are the same for all participants. During one day, participants need to make trades on the tournament account. And 5 traders with the biggest tournament balance will become winners.'); ?>

          </div>
        </div>

      </div>

      
      <div class="tournament-container__members">
        <div class="prize-distribution">
          <div class="prize-distribution__title">
            🏆 Prize pool distribution
          </div>
          
          <div>
            <div class="prize-distribution__header">
              <div>Position</div>
              <div class="prize-distribution__header-price">Prize</div>
            </div>

            <?php
              $prizes = $tournament->resolved_prize_distribution;
            ?>

            <?php $__currentLoopData = $prizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="prize-distribution__item <?php echo e($index % 2 === 0 ? 'grey' : ''); ?>">
                <div><?php echo e($item['place'] ?? ($index + 1 . ' Place')); ?></div>
                <div class="prize-distribution__item-price"><?php echo e($item['prize'] ?? '0$'); ?></div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
      </div>

      
      <div class="tournament-container__faq">
        <div class="faq-tournaments__header">
          <div class="faq-tournaments__title">
            FAQ
          </div>
          <a class="faq-tournaments__link" href="/en/support/faq">
            <span>Check out full FAQ</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>

        <div class="faq-tournaments-item" onclick="toggleFaq(this)">
          <div class="faq-tournaments-item__header">
            <div class="faq-tournaments-item__name">What is a tournament?</div>
            <span class="faq-chevron">▼</span>
          </div>
          <div class="faq-tournaments-item__answer">
            A tournament is a trading competition between users where everyone starts with equal balance. Traders who make the highest profits on the tournament account win cash prizes.
          </div>
        </div>

        <div class="faq-tournaments-item" onclick="toggleFaq(this)">
          <div class="faq-tournaments-item__header">
            <div class="faq-tournaments-item__name">What is the price of entering a tournament?</div>
            <span class="faq-chevron">▼</span>
          </div>
          <div class="faq-tournaments-item__answer">
            The entrance fee varies from free to $50 depending on the tournament. The entry fee is deducted from your real account balance upon registration.
          </div>
        </div>

        <div class="faq-tournaments-item" onclick="toggleFaq(this)">
          <div class="faq-tournaments-item__header">
            <div class="faq-tournaments-item__name">How a winner is determined?</div>
            <span class="faq-chevron">▼</span>
          </div>
          <div class="faq-tournaments-item__answer">
            Winners are determined by their tournament account balance at the moment the tournament ends. The higher the balance, the higher the rank on the leaderboard.
          </div>
        </div>

        <div class="faq-tournaments-item" onclick="toggleFaq(this)">
          <div class="faq-tournaments-item__header">
            <div class="faq-tournaments-item__name">What are the reasons for disqualification?</div>
            <span class="faq-chevron">▼</span>
          </div>
          <div class="faq-tournaments-item__answer">
            Using automated trading bots, multiple accounts, or attempting fraudulent balance manipulations will result in immediate disqualification.
          </div>
        </div>

        <div class="faq-tournaments-item" onclick="toggleFaq(this)">
          <div class="faq-tournaments-item__header">
            <div class="faq-tournaments-item__name">Are there free tournaments?</div>
            <span class="faq-chevron">▼</span>
          </div>
          <div class="faq-tournaments-item__answer">
            Yes! We regularly organize Free Friday tournaments with real cash prize pools that require zero entrance fee.
          </div>
        </div>

        <div class="faq-tournaments-item" onclick="toggleFaq(this)">
          <div class="faq-tournaments-item__header">
            <div class="faq-tournaments-item__name">What is a rebuy?</div>
            <span class="faq-chevron">▼</span>
          </div>
          <div class="faq-tournaments-item__answer">
            If your tournament balance runs low, a rebuy allows you to reset your tournament balance back to initial starting funds for a small fixed fee.
          </div>
        </div>

        <div class="faq-tournaments-item" onclick="toggleFaq(this)">
          <div class="faq-tournaments-item__header">
            <div class="faq-tournaments-item__name">If I win, when will I get the money?</div>
            <span class="faq-chevron">▼</span>
          </div>
          <div class="faq-tournaments-item__answer">
            Prizes are credited automatically to your live real account balance immediately after the tournament finishes.
          </div>
        </div>

      </div>

    </div>

  </div>
</div>

<script>
  function toggleFaq(el) {
    el.classList.toggle('open');
  }

  function joinTournamentModal(title, fee) {
    if (confirm(`Do you want to register for ${title} with entry fee ${fee}?`)) {
      alert(`Registration request submitted for ${title}! Good luck with your trading!`);
    }
  }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/account/tournament_detail.blade.php ENDPATH**/ ?>