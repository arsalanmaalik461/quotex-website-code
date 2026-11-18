@extends('layouts.platform')

@section('title', 'Tournaments | Quotex')

@push('styles')
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
@endpush

@section('content')
@php
  // P0-4 (2026-11-18): no hardcoded fallback tournaments. When the
  // database has none, the page shows an empty state instead of cards
  // linking to ids 1/2/3 that do not exist.
  $hasDbActive = isset($activeTournaments) && $activeTournaments->isNotEmpty();
  $hasDbCompleted = isset($completedTournaments) && $completedTournaments->isNotEmpty();

  $activeCount = $hasDbActive ? $activeTournaments->count() : 0;
  $completedCount = $hasDbCompleted ? $completedTournaments->count() : 0;
@endphp

<div class="tournaments-wrapper">
  <div class="tournaments-main-title">Tournaments</div>

  <!-- Sub-Tabs -->
  <div class="tournaments-nav-tabs">
    <div class="tournaments-nav-tab active" id="tab-active" onclick="switchTournamentsTab('active')">
      <span>Active</span>
      <span class="tournaments-count-pill">{{ $activeCount }}</span>
    </div>
    <div class="tournaments-nav-tab" id="tab-completed" onclick="switchTournamentsTab('completed')">
      <span>Completed</span>
    </div>
  </div>

  <!-- Section Heading -->
  <div class="tournaments-section-heading" id="tournaments-heading">
    Available for participation ({{ $activeCount }})
  </div>

  <!-- Active Tournaments View -->
  <div class="tournaments-grid" id="grid-active">
    @if($hasDbActive)
      @foreach($activeTournaments as $t)
        <div class="qx-tournament-card">
          <div class="qx-tcard-top">
            <div class="qx-tcard-badge">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>{{ $t->badge_text ?: 'Until start: 1 day(s)' }}</span>
            </div>
            <div class="qx-tcard-prize-box">
              <div class="qx-tcard-prize-lbl">Prize pool</div>
              <div class="qx-tcard-prize-val">{{ number_format($t->prize_pool, 0, '.', ' ') }} $</div>
            </div>
          </div>
          <div class="qx-tcard-title">{{ $t->title }}</div>
          <div class="qx-tcard-stats-row">
            <div class="qx-tcard-stat">
              <div class="qx-tcard-stat-val">{{ number_format($t->entry_fee, 0, '.', ' ') }} $</div>
              <div class="qx-tcard-stat-lbl">Entry fee</div>
            </div>
            <div class="qx-tcard-stat">
              <div class="qx-tcard-stat-val">{{ $t->duration }}</div>
              <div class="qx-tcard-stat-lbl">Duration</div>
            </div>
          </div>
          <a href="{{ route('tournament.detail', $t->id) }}" class="qx-tcard-btn" style="text-decoration:none;">
            <span>Details</span>
            <span class="btn-info-icon">i</span>
          </a>
        </div>
      @endforeach
    @else
      <div class="qx-tournament-empty" style="grid-column: 1 / -1; padding: 32px 16px; text-align: center; color: #8b9ab4;">No active tournaments right now. Check back soon.</div>
    @endif
  </div>

  <!-- Completed Tournaments View -->
  <div class="tournaments-grid" id="grid-completed" style="display: none;">
    @if($hasDbCompleted)
      @foreach($completedTournaments as $t)
        <div class="qx-tournament-card">
          <div class="qx-tcard-top">
            <div class="qx-tcard-badge completed-badge">
              <span>{{ $t->badge_text ?: 'Finished' }}</span>
            </div>
            <div class="qx-tcard-prize-box">
              <div class="qx-tcard-prize-lbl">Prize pool</div>
              <div class="qx-tcard-prize-val">{{ number_format($t->prize_pool, 0, '.', ' ') }} $</div>
            </div>
          </div>
          <div class="qx-tcard-title">{{ $t->title }}</div>
          <div class="qx-tcard-stats-row">
            <div class="qx-tcard-stat">
              <div class="qx-tcard-stat-val">{{ number_format($t->entry_fee, 0, '.', ' ') }} $</div>
              <div class="qx-tcard-stat-lbl">Entry fee</div>
            </div>
            <div class="qx-tcard-stat">
              <div class="qx-tcard-stat-val">{{ $t->duration }}</div>
              <div class="qx-tcard-stat-lbl">Duration</div>
            </div>
          </div>
          <a href="{{ route('tournament.detail', $t->id) }}" class="qx-tcard-btn" style="text-decoration:none;">
            <span>Details</span>
            <span class="btn-info-icon">i</span>
          </a>
        </div>
      @endforeach
    @else
      <div class="qx-tournament-empty" style="grid-column: 1 / -1; padding: 32px 16px; text-align: center; color: #8b9ab4;">No completed tournaments yet.</div>
    @endif
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
      heading.textContent = 'Available for participation ({{ $activeCount }})';
    } else {
      tabCompleted.classList.add('active');
      tabActive.classList.remove('active');
      gridActive.style.display = 'none';
      gridCompleted.style.display = 'grid';
      heading.textContent = 'Completed tournaments ({{ $completedCount }})';
    }
  }

  // NOTE: the old list-page registration modal (openTournamentModal /
  // participateTournament) was removed: it showed "Registration
  // Successful" without ever calling the server. Every card on this
  // page now links to the tournament detail page, whose Join modal
  // posts to the real POST /tournament/{id}/register endpoint.
</script>
@endsection
