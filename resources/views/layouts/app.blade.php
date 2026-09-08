<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Titip Kilat' }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --red: #E53935;
      --red-light: #FFEBEE;
      --orange: #FB8C00;
      --orange-light: #FFF3E0;
      --dark: #1E293B;
      --slate: #334155;
      --muted: #94A3B8;
      --bg: #F1F5F9;
      --surface: #FFFFFF;
      --border: #E2E8F0;
      --green: #22C55E;
      --green-light: #F0FDF4;
      --radius: 14px;
      --radius-lg: 20px;
    }

    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background: var(--bg);
      color: var(--dark);
      line-height: 1.6;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    .layout { display: flex; min-height: 100vh; }

    .sidebar {
      width: 260px;
      background: var(--dark);
      color: #fff;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0; bottom: 0;
      z-index: 100;
      transition: transform .25s;
    }

    .sidebar-brand {
      padding: 28px 24px 20px;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .sidebar-brand a {
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 20px;
      font-weight: 800;
    }

    .sidebar-brand .logo {
      width: 36px; height: 36px;
      background: var(--red);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }

    .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }

    .sidebar-section {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: rgba(255,255,255,.35);
      padding: 16px 12px 8px;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 14px;
      border-radius: 10px;
      color: rgba(255,255,255,.65);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all .15s;
      margin-bottom: 2px;
    }

    .sidebar-link:hover { background: rgba(255,255,255,.08); color: #fff; }
    .sidebar-link.active { background: var(--red); color: #fff; font-weight: 600; }
    .sidebar-link .icon { width: 20px; text-align: center; font-size: 16px; }

    .sidebar-footer {
      padding: 16px 12px;
      border-top: 1px solid rgba(255,255,255,.08);
    }

    .sidebar-user {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 10px;
      background: rgba(255,255,255,.06);
    }

    .sidebar-avatar {
      width: 36px; height: 36px;
      background: var(--red);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 14px;
    }

    .sidebar-user-info { flex: 1; min-width: 0; }
    .sidebar-user-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar-user-role { font-size: 11px; color: rgba(255,255,255,.45); }

    /* ── Main ── */
    .main { flex: 1; margin-left: 260px; min-height: 100vh; }

    .topbar {
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      padding: 0 32px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .topbar-title { font-size: 16px; font-weight: 700; }

    .topbar-actions { display: flex; align-items: center; gap: 12px; }

    .topbar-btn {
      padding: 8px 16px;
      border-radius: 8px;
      border: none;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: all .15s;
    }

    .btn-red { background: var(--red); color: #fff; }
    .btn-red:hover { background: #D32F2F; }
    .btn-ghost { background: transparent; color: var(--muted); }
    .btn-ghost:hover { background: var(--bg); color: var(--dark); }

    .content { padding: 32px; max-width: none; }

    /* ── Cards ── */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px;
      transition: box-shadow .2s;
    }

    .card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.04); }

    .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }

    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px;
      display: flex;
      align-items: flex-start;
      gap: 16px;
    }

    .stat-icon {
      width: 44px; height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }

    .stat-value { font-size: 28px; font-weight: 800; line-height: 1.1; }
    .stat-label { font-size: 13px; color: var(--muted); margin-top: 2px; }

    /* ── Hero Banner ── */
    .hero-banner {
      background: linear-gradient(135deg, var(--red) 0%, var(--orange) 100%);
      border-radius: var(--radius-lg);
      padding: 36px 32px;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .hero-banner::after {
      content: '';
      position: absolute;
      right: -20px; top: -20px;
      width: 180px; height: 180px;
      background: rgba(255,255,255,.08);
      border-radius: 50%;
    }

    .hero-banner h1 { font-size: 28px; font-weight: 800; margin-bottom: 6px; position: relative; }
    .hero-banner p { opacity: .85; font-size: 14px; max-width: 480px; position: relative; }

    .hero-pills { display: flex; gap: 10px; margin-top: 20px; position: relative; }

    .hero-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      background: rgba(255,255,255,.2);
      border-radius: 24px;
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      transition: background .15s;
    }

    .hero-pill:hover { background: rgba(255,255,255,.3); }

    /* ── Tags / Badges ── */
    .badge {
      display: inline-flex;
      align-items: center;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
    }

    .badge-red { background: var(--red-light); color: var(--red); }
    .badge-orange { background: var(--orange-light); color: var(--orange); }
    .badge-green { background: var(--green-light); color: var(--green); }
    .badge-slate { background: var(--bg); color: var(--slate); }

    /* ── Forms ── */
    .form-group { margin-bottom: 18px; }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 6px;
      color: var(--slate);
    }

    .form-input {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-size: 14px;
      font-family: inherit;
      color: var(--dark);
      background: var(--surface);
      transition: border-color .15s;
      outline: none;
    }

    .form-input:focus { border-color: var(--red); }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    /* ── Buttons ── */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 11px 20px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      font-family: inherit;
      border: none;
      cursor: pointer;
      text-decoration: none;
      transition: all .15s;
    }

    .btn-primary { background: var(--red); color: #fff; }
    .btn-primary:hover { background: #D32F2F; transform: translateY(-1px); }
    .btn-accent { background: var(--orange); color: #fff; }
    .btn-accent:hover { background: #F57C00; }
    .btn-outline { background: transparent; color: var(--dark); border: 1.5px solid var(--border); }
    .btn-outline:hover { border-color: var(--red); color: var(--red); }
    .btn-sm { padding: 7px 14px; font-size: 12px; }

    /* ── Section ── */
    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .section-title { font-size: 20px; font-weight: 800; }

    /* ── Steps ── */
    .steps { display: flex; align-items: center; gap: 0; margin-bottom: 32px; }

    .step {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 600;
      color: var(--muted);
    }

    .step.active { color: var(--red); }
    .step-num {
      width: 28px; height: 28px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      background: var(--bg);
      color: var(--muted);
    }

    .step.active .step-num { background: var(--red); color: #fff; }
    .step-line { flex: 1; height: 2px; background: var(--border); margin: 0 12px; }

    /* ── Empty State ── */
    .empty {
      text-align: center;
      padding: 48px 24px;
      color: var(--muted);
    }

    .empty-icon { font-size: 48px; margin-bottom: 12px; opacity: .4; }
    .empty-title { font-size: 16px; font-weight: 700; color: var(--dark); margin-bottom: 4px; }

    /* ── Table ── */
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; font-size: 12px; font-weight: 600; color: var(--muted); padding: 10px 14px; border-bottom: 1px solid var(--border); text-transform: uppercase; letter-spacing: .5px; }
    td { padding: 14px; border-bottom: 1px solid var(--border); font-size: 14px; }

    /* ── Dark Card ── */
    .dark-card {
      background: var(--dark);
      color: #fff;
      border-radius: var(--radius-lg);
      padding: 28px;
    }

    .dark-card .label { font-size: 13px; color: rgba(255,255,255,.5); }
    .dark-card .amount { font-size: 36px; font-weight: 800; margin: 6px 0 12px; }
    .dark-card .note { font-size: 12px; color: rgba(255,255,255,.4); }

    /* ── Responsive ── */
    @media (max-width: 860px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.open { transform: translateX(0); }
      .main { margin-left: 0; }
      .content { padding: 20px 16px; }
      .form-row { grid-template-columns: 1fr; }
      .card-grid { grid-template-columns: 1fr; }
    }
  </style>
  <style>
    /* Clean light mode / blue gradient refresh */
    :root { --red:#2563eb; --red-light:#eff6ff; --orange:#4f46e5; --orange-light:#eef2ff; --dark:#172554; --slate:#334155; --muted:#64748b; --bg:#f4f7fb; --surface:#fff; --border:#dbe4f0; --green:#16a34a; --green-light:#ecfdf3; --radius:16px; --radius-lg:24px; }
    body { background:linear-gradient(135deg,#f8fbff 0%,#eef4ff 54%,#f8f7ff 100%); color:var(--dark); }
    .sidebar { width:232px; background:#fff; color:var(--dark); border-right:1px solid #e5edf7; box-shadow:12px 0 34px rgba(37,99,235,.06); }
    .sidebar { width:100%; height:72px; right:0; bottom:auto; flex-direction:row; align-items:center; }
    .sidebar-brand { padding:18px 24px; border-bottom:0; border-right:1px solid #edf2f8; }
    .sidebar-brand a { color:var(--dark); }
    .sidebar-brand .logo { background:linear-gradient(135deg,#2563eb,#4f46e5); border-radius:11px; box-shadow:0 8px 18px rgba(37,99,235,.22); }
    .sidebar-brand .logo,.sidebar-link .icon,.menu-toggle { font-size:0; }
    .sidebar-brand .logo::before { content:'\f4f4'; font-family:'bootstrap-icons'; font-size:18px; }
    .sidebar-link .icon::before,.menu-toggle::before { font-family:'bootstrap-icons'; font-size:16px; }
    .sidebar-link[href$="/"] .icon::before { content:'\f425'; }
    .sidebar-link.home-link .icon::before { content:'\f425'; }
    .sidebar-link[href$="users"] .icon::before { content:'\f4cf'; }
    .sidebar-link[href$="orders"] .icon::before { content:'\f290'; }
    .sidebar-link[href$="wallet"] .icon::before { content:'\f5d5'; }
    .sidebar-link[href$="trips"] .icon::before { content:'\f5ef'; }
    .sidebar-link[href$="user"] .icon::before { content:'\f4e1'; }
    .sidebar-link[href$="courier"] .icon::before { content:'\f5e4'; }
    .sidebar-link[href$="traveler"] .icon::before { content:'\f3ee'; }
    .sidebar-link[href$="admin"] .icon::before { content:'\f52f'; }
    .sidebar-link[href$="super-admin"] .icon::before { content:'\f3e5'; }
    .sidebar-link[href$="user-management"] { display:none; }
    .sidebar-link[href$="user-management"] .icon::before { content:'\f4cf'; }
    .sidebar-link[href$="settings"] .icon::before { content:'\f3e5'; }
    .menu-toggle::before { content:'\f479'; }
    .sidebar-nav { flex:1; display:flex; align-items:center; gap:4px; padding:10px 18px; overflow-x:auto; }
    .sidebar-section { display:none; }
    .sidebar-link { color:#64748b; border-radius:11px; margin-bottom:0; white-space:nowrap; }
    .sidebar-link:hover { background:#f1f5ff; color:#2563eb; }
    .sidebar-link.active { background:linear-gradient(90deg,#eaf2ff,#f5f3ff); color:#2563eb; font-weight:700; box-shadow:inset 3px 0 #2563eb; }
    .sidebar-link .icon { color:inherit; }
    .sidebar-footer { display:none; }
    .sidebar-user { background:#f8faff; border:1px solid #e6edf7; }
    .sidebar-avatar { background:linear-gradient(135deg,#2563eb,#4f46e5); }
    .sidebar-user-role { color:#94a3b8; }
    .main { margin-left:0; padding-top:72px; }
    .topbar { height:72px; top:72px; padding:0 clamp(20px,4vw,56px); background:rgba(255,255,255,.84); border-bottom:1px solid #e5edf7; box-shadow:0 4px 18px rgba(30,64,175,.04); }
    .topbar-title { color:var(--dark); font-size:15px; }
    .menu-toggle { display:none; border:1px solid var(--border); color:var(--red); background:#fff; border-radius:10px; padding:7px 10px; cursor:pointer; font-size:16px; }
    .content { max-width:none; padding:clamp(26px,4vw,56px) clamp(20px,4vw,56px) 80px; }
    .card,.stat-card { background:rgba(255,255,255,.86); border:1px solid #e2eaf5; border-radius:var(--radius); box-shadow:0 12px 30px rgba(30,64,175,.06); }
    .card:hover { box-shadow:0 18px 38px rgba(30,64,175,.11); transform:translateY(-2px); }
    .card-grid { display:flex; flex-direction:column; gap:12px; }
    .card-grid > .card { width:100%; }
    .stat-value,.section-title,h1,h2,h3,h4 { color:var(--dark); }
    .stat-icon { background:var(--red-light)!important; color:var(--red)!important; font-size:0!important; }
    .stat-icon::before { content:'\f290'; font-family:'bootstrap-icons'; font-size:20px; }
    .hero-pill { font-size:0; }
    .hero-pill::before { font-family:'bootstrap-icons'; font-size:15px; }
    .hero-pill[href$="create"]::before { content:'\f1c1'; }
    .hero-pill[href$="trips"]::before { content:'\f5ef'; }
    .hero-pill[href$="wallet"]::before { content:'\f5d5'; }
    .empty-icon { font-size:0; }
    .empty-icon::before { content:'\f290'; font-family:'bootstrap-icons'; font-size:44px; }
    .card-grid a span[style*="font-size:24px"] { font-size:0!important; }
    .card-grid a span[style*="font-size:24px"]::before { content:'\f1c1'; font-family:'bootstrap-icons'; font-size:24px; }
    .card > div[style*="font-size:40px"] { font-size:0!important; }
    .card > div[style*="font-size:40px"]::before { content:'\f1c1'; font-family:'bootstrap-icons'; font-size:38px; color:var(--red); }
    .hero-banner { min-height:min(58vh,520px); padding:clamp(34px,6vw,76px) clamp(26px,6vw,80px); border-radius:var(--radius-lg); display:flex; flex-direction:column; justify-content:flex-end; background:linear-gradient(120deg,#1d4ed8 0%,#2563eb 43%,#6366f1 100%); box-shadow:0 24px 58px rgba(37,99,235,.2); }
    .hero-banner h1 { color:#fff; font-size:clamp(38px,6vw,76px); letter-spacing:-.06em; line-height:.98; max-width:720px; }
    .hero-banner p { font-size:16px; line-height:1.7; max-width:560px; }
    .section-header { margin-bottom:20px; }
    .section-title { font-size:22px; letter-spacing:-.03em; }
    .btn-primary { background:linear-gradient(135deg,#2563eb,#4f46e5); box-shadow:0 8px 18px rgba(37,99,235,.2); }
    .btn-accent { background:linear-gradient(135deg,#4f46e5,#7c3aed); }
    .btn-outline { color:#2563eb; border-color:#cbdaf0; background:#fff; }
    .btn-outline:hover { border-color:#2563eb; color:#1d4ed8; background:#eff6ff; }
    .form-input { color:var(--dark); background:#fff; border-color:#d5e0ee; }
    .form-input::placeholder { color:#94a3b8; }
    .form-label { color:var(--slate); }
    .empty-title { color:var(--dark); }
    th { border-bottom-color:var(--border); } td { border-bottom-color:var(--border); }
    .dark-card { background:linear-gradient(135deg,#1d4ed8,#4f46e5); }
    @media (max-width:860px) { .sidebar{width:232px;height:auto;right:auto;bottom:0;align-items:stretch;flex-direction:column;transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.sidebar-brand{padding:26px 22px 22px;border-right:0;border-bottom:1px solid #edf2f8}.sidebar-nav{display:block;padding:18px 12px}.sidebar-section{display:block;color:#94a3b8;padding:18px 12px 8px}.sidebar-link{margin-bottom:4px}.sidebar-footer{display:block;border-top:1px solid #edf2f8}.main{padding-top:0}.topbar{top:0;padding-left:16px}.menu-toggle{display:inline-flex}.content{padding:20px 16px 48px}.hero-banner{min-height:430px} }
  </style>
  <style>
    /* Floating iOS-style navigation */
    .sidebar {
      position: fixed;
      top: 18px;
      left: 50%;
      right: auto;
      bottom: auto;
      width: min(calc(100% - 40px), 1180px);
      height: 68px;
      transform: translateX(-50%);
      flex-direction: row;
      align-items: center;
      border: 1px solid rgba(255,255,255,.72);
      border-radius: 24px;
      background: rgba(255,255,255,.66);
      box-shadow: 0 16px 42px rgba(30,64,175,.14), inset 0 1px 0 rgba(255,255,255,.9);
      backdrop-filter: blur(24px) saturate(150%);
      -webkit-backdrop-filter: blur(24px) saturate(150%);
    }
    .sidebar-brand { flex-shrink: 0; padding: 14px 22px; border-right: 1px solid rgba(148,163,184,.22); }
    .sidebar-brand a { font-size: 17px; }
    .sidebar-brand .logo { width: 38px; height: 38px; }
    .sidebar-nav { display:flex; align-items:center; gap:4px; padding:8px 12px; overflow-x:auto; scrollbar-width:none; }
    .sidebar-nav::-webkit-scrollbar { display:none; }
    .sidebar-link { padding:10px 13px; margin:0; font-size:12px; gap:8px; border-radius:14px; }
    .sidebar-link .icon { width:18px; }
    .sidebar-link.active { box-shadow:none; background:linear-gradient(135deg,#2563eb,#5b5ce2); color:#fff; }
    .sidebar-link[href$="dashboard/user"],.sidebar-link[href$="dashboard/courier"],.sidebar-link[href$="dashboard/traveler"],.sidebar-link[href$="dashboard/admin"] { display:none; }
    .sidebar-footer { display:none; }
    .main { padding-top:86px; }
    .topbar { top:0; background:transparent; border-bottom:0; box-shadow:none; }
    .content { padding-top:20px; }
    @media (max-width:860px) {
      .sidebar { top:16px; left:16px; width:calc(100% - 32px); height:62px; right:auto; bottom:auto; transform:none; flex-direction:row; }
      .sidebar.open { transform:none; }
      .sidebar-brand { padding:11px 14px; border-right:1px solid rgba(148,163,184,.22); }
      .sidebar-brand a { font-size:15px; }
      .sidebar-brand .logo { width:34px; height:34px; }
      .sidebar-nav { padding:7px 8px; }
      .sidebar-link { font-size:0; padding:10px 12px; }
      .sidebar-link .icon { font-size:0; }
      .sidebar-link .icon::before { font-size:17px; }
      .sidebar-section { display:none; }
      .main { padding-top:76px; }
      .topbar { height:64px; }
      .topbar-title { font-size:14px; }
      .menu-toggle { display:none; }
      .content { padding-top:12px; }
    }
  </style>
  <style>
    /* iOS glass language across the full product UI */
    .topbar { display:none !important; }
    body { -webkit-font-smoothing:antialiased; }
    .content { position:relative; }
    .content::before { content:''; position:fixed; z-index:-1; width:420px; height:420px; top:18%; right:-120px; border-radius:50%; background:rgba(99,102,241,.11); filter:blur(70px); pointer-events:none; }
    .card, .stat-card, .dark-card {
      border-radius:22px;
      background:rgba(255,255,255,.64);
      border:1px solid rgba(255,255,255,.86);
      box-shadow:0 18px 48px rgba(45,78,145,.09), inset 0 1px 0 rgba(255,255,255,.95);
      backdrop-filter:blur(22px) saturate(140%);
      -webkit-backdrop-filter:blur(22px) saturate(140%);
    }
    .card { padding:26px; }
    .card:hover { border-color:rgba(96,131,220,.32); box-shadow:0 22px 56px rgba(45,78,145,.14), inset 0 1px 0 rgba(255,255,255,.98); }
    .stat-card { padding:20px 22px; }
    .stat-icon { border-radius:15px; box-shadow:inset 0 1px 0 rgba(255,255,255,.72); }
    .hero-banner { border:1px solid rgba(255,255,255,.48); box-shadow:0 28px 70px rgba(37,99,235,.22), inset 0 1px 0 rgba(255,255,255,.3); }
    .hero-banner::after { width:340px; height:340px; right:-80px; top:-130px; background:rgba(255,255,255,.13); filter:blur(2px); }
    .hero-pills { flex-wrap:wrap; }
    .hero-pill { border:1px solid rgba(255,255,255,.35); box-shadow:inset 0 1px 0 rgba(255,255,255,.22), 0 7px 18px rgba(20,50,130,.12); backdrop-filter:blur(10px); }
    .badge { border-radius:999px; padding:5px 11px; }
    .btn { border-radius:999px; padding:12px 20px; box-shadow:0 8px 18px rgba(37,99,235,.12); }
    .btn-sm { padding:8px 15px; }
    .btn-primary, .btn-accent { border:1px solid rgba(255,255,255,.35); }
    .order-modal { display:none; position:fixed; inset:0; z-index:200; place-items:center; padding:20px; background:rgba(23,37,84,.42); backdrop-filter:blur(8px); }
    .order-modal.is-open { display:grid; }
    .order-dialog { width:min(100%,520px); position:relative; padding:28px; border:1px solid rgba(255,255,255,.9); border-radius:24px; background:rgba(255,255,255,.95); box-shadow:0 24px 70px rgba(23,37,84,.25); }
    .order-dialog h2 { margin-bottom:6px; }
    .order-dialog > p { color:var(--muted); font-size:13px; }
    .order-modal-close { position:absolute; top:16px; right:16px; width:32px; height:32px; border:0; border-radius:50%; background:var(--bg); color:var(--slate); font-size:20px; cursor:pointer; }
    .order-modal-options { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:22px; }
    .order-modal-option { display:flex; flex-direction:column; gap:7px; padding:18px; border:1px solid var(--border); border-radius:16px; color:var(--dark); text-decoration:none; text-align:left; font:inherit; cursor:pointer; transition:all .15s; }
    .order-modal-option:hover { border-color:#2563eb; background:#eff6ff; transform:translateY(-2px); }
    .order-modal-option i { color:#2563eb; font-size:24px; }
    .order-modal-option strong { font-size:14px; }
    .order-modal-option span { color:var(--muted); font-size:12px; line-height:1.5; }
    .order-form-modal { display:none; position:fixed; inset:0; z-index:205; place-items:center; padding:20px; background:rgba(23,37,84,.46); backdrop-filter:blur(9px); }.order-form-modal.is-open { display:grid; }.order-form-dialog { width:min(100%,680px); max-height:90vh; overflow:auto; position:relative; padding:28px; border:1px solid rgba(255,255,255,.9); border-radius:24px; background:rgba(255,255,255,.96); box-shadow:0 24px 70px rgba(23,37,84,.25); animation:ui-rise .35s ease-out; }.order-form-dialog h2 { margin-bottom:5px; }.order-form-dialog > p { color:var(--muted); font-size:13px; margin-bottom:20px; }.order-form-close { position:absolute; top:16px; right:16px; width:32px; height:32px; border:0; border-radius:50%; background:var(--bg); color:var(--slate); font-size:20px; cursor:pointer; }.order-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:13px; }.order-form-field { display:grid; gap:6px; }.order-form-field.full { grid-column:1/-1; }.order-form-field label { color:var(--slate); font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }.order-form-field input,.order-form-field textarea { width:100%; padding:12px 14px; border:1px solid var(--border); border-radius:12px; background:#fff; font:inherit; }.order-form-field textarea { min-height:76px; resize:vertical; }.order-form-actions { display:flex; gap:10px; margin-top:22px; }.order-form-actions .btn { flex:1; justify-content:center; }@media(max-width:560px){.order-form-grid{grid-template-columns:1fr}.order-form-field.full{grid-column:auto}}
    .auth-actions { order:3; margin-left:auto; display:flex; align-items:center; gap:8px; padding:8px 12px; }
    .nav-greeting { color:var(--slate); font-size:12px; font-weight:700; white-space:nowrap; }
    .sidebar-nav { order:2; }
    .auth-login { border:1px solid #cbdaf0; background:#fff; color:#2563eb; cursor:pointer; }
    .auth-signup { white-space:nowrap; }
    .login-modal { display:none; position:fixed; inset:0; z-index:210; place-items:center; padding:20px; background:rgba(23,37,84,.42); backdrop-filter:blur(8px); }
    .login-modal.is-open { display:grid; }
    .login-dialog { width:min(100%,430px); position:relative; padding:28px; border-radius:24px; background:rgba(255,255,255,.95); box-shadow:0 24px 70px rgba(23,37,84,.25); }
    .login-dialog h2 { margin-bottom:6px; }.login-dialog>p { color:var(--muted); font-size:13px; }.login-form { display:grid; gap:14px; margin-top:20px; }.login-form label { display:grid; gap:6px; color:var(--slate); font-size:12px; font-weight:700; }.login-form input { width:100%; padding:12px 14px; border:1px solid var(--border); border-radius:12px; font:inherit; }.login-close { position:absolute; top:16px; right:16px; width:32px; height:32px; border:0; border-radius:50%; background:var(--bg); color:var(--slate); font-size:20px; cursor:pointer; }.login-signup { margin-top:16px; color:var(--muted); font-size:12px; text-align:center; }.login-signup a { color:#2563eb; font-weight:700; }
    .toast-container { position:fixed; top:96px; right:24px; z-index:300; display:grid; gap:10px; width:min(360px,calc(100% - 32px)); pointer-events:none; }.toast { display:flex; align-items:flex-start; gap:11px; padding:15px 16px; border:1px solid rgba(255,255,255,.82); border-radius:17px; background:rgba(255,255,255,.78); color:var(--dark); box-shadow:0 18px 45px rgba(23,37,84,.18),inset 0 1px 0 rgba(255,255,255,.95); backdrop-filter:blur(20px) saturate(150%); pointer-events:auto; animation:toast-in .25s ease-out; }.toast i{font-size:19px}.toast strong{display:block;font-size:13px}.toast span{display:block;margin-top:2px;color:var(--muted);font-size:12px;line-height:1.45}.toast-success i{color:#16a34a}.toast-error i{color:#dc2626}.toast-close{margin-left:auto;border:0;background:transparent;color:var(--muted);font-size:18px;cursor:pointer}.toast.is-leaving{animation:toast-out .2s ease-in forwards}@keyframes toast-in{from{opacity:0;transform:translateY(-12px) scale(.97)}to{opacity:1;transform:none}}@keyframes toast-out{to{opacity:0;transform:translateY(-8px) scale(.97)}}body.dark-mode .toast{background:rgba(23,37,84,.82);border-color:#475569}
    .form-loading { opacity:.8; cursor:wait!important; }.form-loading::before { content:''; display:inline-block; width:13px; height:13px; margin-right:7px; border:2px solid currentColor; border-right-color:transparent; border-radius:50%; vertical-align:-2px; animation:form-spin .65s linear infinite; } @keyframes form-spin { to { transform:rotate(360deg); } }
    .password-field { position:relative; display:flex; align-items:center; width:100%; }.password-field input { padding-right:44px!important; }.password-toggle { position:absolute; right:10px; border:0; background:transparent; color:var(--muted); cursor:pointer; font-size:16px; padding:5px; }.password-toggle:hover { color:#2563eb; }
    @media(max-width:520px) { .order-modal-options { grid-template-columns:1fr; } }
    .btn-outline { box-shadow:inset 0 1px 0 rgba(255,255,255,.9), 0 6px 15px rgba(30,64,175,.06); }
    .form-input { border-radius:14px; padding:13px 15px; background:rgba(255,255,255,.7); box-shadow:inset 0 1px 2px rgba(30,64,175,.04); }
    .form-input:focus { border-color:#7aa2f7; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
    .form-label { font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
    .steps { padding:14px 18px; border-radius:18px; background:rgba(255,255,255,.5); border:1px solid rgba(255,255,255,.8); box-shadow:inset 0 1px 0 #fff; }
    .step-num { box-shadow:inset 0 1px 0 rgba(255,255,255,.8); }
    .section-header { align-items:flex-end; }
    .section-title { font-weight:800; }
    .table-wrap { border-radius:18px; overflow:hidden; }
    table { background:rgba(255,255,255,.3); }
    th { background:rgba(239,246,255,.7); }
    td, th { border-bottom-color:rgba(148,163,184,.18); }
    .empty { padding:64px 24px; }
    @media (max-width:860px) { .card { padding:21px; } .steps { padding:12px; } }
    /* Compact app-style icon navigation */
    .sidebar-nav { gap:8px; }
    .sidebar-link { width:44px; height:44px; justify-content:center; padding:10px; font-size:0; gap:0; position:relative; }
    .sidebar-link .icon { width:auto; font-size:0; }
    .sidebar-link .icon::before { font-size:19px; }
    .sidebar-link:hover::after { content:attr(title); position:absolute; top:calc(100% + 9px); left:50%; transform:translateX(-50%); z-index:20; padding:6px 9px; border-radius:8px; background:#172554; color:#fff; font-size:11px; font-weight:600; white-space:nowrap; pointer-events:none; box-shadow:0 8px 18px rgba(23,37,84,.2); }
    .sidebar-link { width:58px; height:54px; flex-direction:column; padding:7px 4px; gap:2px; font-size:9px; line-height:1.1; text-align:center; }
    .sidebar-link .icon { display:block; line-height:1; }
    .sidebar-link .icon::before { font-size:17px; }
    .sidebar-link:hover::after { display:none; }
    .sidebar { width:max-content; max-width:calc(100% - 32px); }
    .sidebar-nav { flex:0 1 auto; max-width:min(58vw,620px); overflow-x:auto; }
    @media(max-width:860px) { .sidebar { width:calc(100% - 32px); max-width:none; } .sidebar-nav { flex:1; max-width:none; } }
    /* Shared motion language across the product */
    .content > * { animation:ui-enter .55s cubic-bezier(.22,1,.36,1) both; }
    .content > *:nth-child(2) { animation-delay:.06s; }.content > *:nth-child(3) { animation-delay:.12s; }.content > *:nth-child(4) { animation-delay:.18s; }
    .card, .stat-card, .dark-card, .table-wrap, .metric-strip, .process-row { animation:ui-rise .5s cubic-bezier(.22,1,.36,1) both; transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease, background-color .22s ease; }
    .card:hover, .stat-card:hover, .dark-card:hover { transform:translateY(-4px); }
    .btn, .sidebar-link, a, button { transition:transform .18s ease, color .18s ease, background-color .18s ease, border-color .18s ease, box-shadow .18s ease, opacity .18s ease; }
    .btn:hover { transform:translateY(-2px); }.btn:active, button:active { transform:translateY(0) scale(.97); }
    .sidebar-link:hover { transform:translateY(-3px); }.sidebar-link.active { animation:ui-active-pulse 2.8s ease-in-out infinite; }
    .table-wrap tbody tr { animation:ui-row-in .42s ease both; }.table-wrap tbody tr:nth-child(2) { animation-delay:.04s; }.table-wrap tbody tr:nth-child(3) { animation-delay:.08s; }.table-wrap tbody tr:nth-child(4) { animation-delay:.12s; }.table-wrap tbody tr:nth-child(5) { animation-delay:.16s; }
    .badge { animation:ui-badge-in .45s ease both; }.section-title, h1, h2, h3, h4 { animation:ui-text-in .5s ease both; }
    @keyframes ui-enter { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
    @keyframes ui-rise { from { opacity:0; transform:translateY(10px) scale(.99); } to { opacity:1; transform:none; } }
    @keyframes ui-row-in { from { opacity:0; transform:translateX(-8px); } to { opacity:1; transform:none; } }
    @keyframes ui-badge-in { from { opacity:0; transform:scale(.85); } to { opacity:1; transform:scale(1); } }
    @keyframes ui-text-in { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }
    @keyframes ui-active-pulse { 0%,100% { box-shadow:0 5px 14px rgba(37,99,235,.12); } 50% { box-shadow:0 8px 22px rgba(37,99,235,.28); } }
    @media(prefers-reduced-motion:reduce) { *, *::before, *::after { animation-duration:.01ms!important; animation-iteration-count:1!important; scroll-behavior:auto!important; transition-duration:.01ms!important; } }
    .page-banner { position:relative; display:flex; justify-content:space-between; align-items:center; gap:28px; width:calc(100% + 64px); margin:-32px 0 30px -32px; padding:30px 32px 28px; overflow:hidden; border-bottom:1px solid #c7d7f5; background:linear-gradient(112deg,#dceaff 0%,#eee9ff 48%,#d9f2ff 100%); background-size:220% 220%; animation:page-banner-flow 12s ease-in-out infinite; }
    .page-banner::before { content:''; position:absolute; inset:0 auto 0 0; width:7px; background:linear-gradient(180deg,#2563eb,#7c3aed,#06b6d4); animation:page-banner-pulse 4s ease-in-out infinite; }
    .page-banner::after { content:''; position:absolute; right:8%; top:-130px; width:390px; height:280px; border:1px solid rgba(99,102,241,.3); border-radius:50%; box-shadow:0 0 0 24px rgba(129,140,248,.14),0 0 0 50px rgba(96,165,250,.1); pointer-events:none; animation:page-banner-drift 9s ease-in-out infinite; }
    .page-banner > * { position:relative; z-index:1; }.page-banner-kicker { color:#315bd6; font-size:11px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }.page-banner h1 { margin-top:7px; color:#172554; font-size:clamp(25px,3.5vw,36px); letter-spacing:-.055em; }.page-banner p { margin-top:6px; color:#526581; font-size:13px; }.page-banner-status { min-width:218px; padding:14px 16px; border:1px solid rgba(99,102,241,.28); border-radius:15px; background:rgba(255,255,255,.55); color:#273b72; box-shadow:0 10px 22px rgba(49,46,129,.1); backdrop-filter:blur(8px); }.page-banner-status strong { display:flex; align-items:center; gap:8px; font-size:10px; letter-spacing:.1em; }.page-banner-status strong::before { content:''; width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 0 4px #dcfce7; animation:page-live-pulse 2s ease-in-out infinite; }.page-banner-status span { display:block; margin-top:9px; color:#526581; font-size:12px; font-weight:700; }.page-banner-status i { float:right; color:#4f46e5; }
    .pagination-wrap { display:flex; justify-content:flex-end; margin-top:16px; overflow:auto; }.pagination-wrap nav { width:100%; }.pagination-wrap nav > div:first-child { color:var(--muted); font-size:12px; }.pagination-wrap nav > div:last-child { display:flex; justify-content:flex-end; }.pagination-wrap a, .pagination-wrap span { display:inline-flex; align-items:center; justify-content:center; min-width:34px; height:34px; margin-left:5px; padding:0 10px; border:1px solid var(--border); border-radius:10px; background:rgba(255,255,255,.72); color:var(--slate); font-size:12px; text-decoration:none; }.pagination-wrap a:hover { border-color:#7aa2f7; background:#eff6ff; color:#2563eb; transform:translateY(-2px); }.pagination-wrap span[aria-current="page"] { border-color:#2563eb; background:#2563eb; color:#fff; font-weight:800; }.pagination-wrap span[aria-disabled="true"] { opacity:.45; }
    @keyframes page-banner-flow { 0%,100% { background-position:0% 50%; } 50% { background-position:100% 50%; } } @keyframes page-banner-drift { 0%,100% { transform:translate3d(0,0,0) rotate(0); } 50% { transform:translate3d(-24px,16px,0) rotate(8deg); } } @keyframes page-banner-pulse { 0%,100% { opacity:.85; } 50% { opacity:1; filter:saturate(1.8); } } @keyframes page-live-pulse { 0%,100% { box-shadow:0 0 0 4px #dcfce7; } 50% { box-shadow:0 0 0 7px rgba(220,252,231,.35); } }
    @media(max-width:860px) { .page-banner { width:calc(100% + 40px); margin:-12px 0 26px -20px; padding:26px 20px 24px; } }
    @media(max-width:560px) { .page-banner { display:block; } .page-banner::after { right:-180px; } .page-banner-status { display:inline-block; min-width:0; margin-top:18px; } }
  </style>
</head>
<body>
  <div class="layout">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <a href="{{ auth()->user()?->role?->name === 'SUPER_ADMIN' ? url('/dashboard/super-admin') : url('/') }}">
          <span class="logo">⚡</span>
          Titip Kilat
        </a>
      </div>
      <div class="auth-actions">@auth <span class="nav-greeting">Hi, {{ Str::limit(auth()->user()->name, 18) }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn auth-login btn-sm" type="submit">Logout</button></form> @else <button class="btn auth-login btn-sm" type="button" data-login-modal>Login</button> @endauth</div>
      <nav class="sidebar-nav">
        <div class="sidebar-section">Menu</div>
        <a href="{{ auth()->user()?->role?->name === 'SUPER_ADMIN' ? url('/dashboard/super-admin') : url('/') }}" title="{{ auth()->user()?->role?->name === 'SUPER_ADMIN' ? 'Dashboard' : 'Beranda' }}" class="sidebar-link home-link {{ auth()->user()?->role?->name === 'SUPER_ADMIN' ? (request()->is('dashboard/super-admin') ? 'active' : '') : (request()->is('/') ? 'active' : '') }}">
          <span class="icon">{{ auth()->user()?->role?->name === 'SUPER_ADMIN' ? '📊' : '🏠' }}</span> {{ auth()->user()?->role?->name === 'SUPER_ADMIN' ? 'Dashboard' : 'Beranda' }}
        </a>
        <a href="{{ url('/orders') }}" title="Pesanan" class="sidebar-link {{ request()->is('orders*') ? 'active' : '' }}">
          <span class="icon">📋</span> Pesanan
        </a>
        <a href="{{ url('/wallet') }}" title="Dompet Digital" class="sidebar-link {{ request()->is('wallet*') ? 'active' : '' }}">
          <span class="icon">💰</span> Dompet Digital
        </a>
        <a href="{{ url('/trips') }}" title="Perjalanan" class="sidebar-link {{ request()->is('trips') ? 'active' : '' }}">
          <span class="icon">✈️</span> Perjalanan
        </a>

        <a href="{{ url('/settings') }}" title="Settings" class="sidebar-link {{ request()->is('settings') ? 'active' : '' }}">
          <span class="icon">âš™ï¸</span> Settings
        </a>
        <div class="sidebar-section">Ruang Kerja</div>
        <a href="{{ url('/dashboard/user-management') }}" class="sidebar-link {{ request()->is('dashboard/user-management') ? 'active' : '' }}">
          <span class="icon">ðŸ‘¥</span> Manajemen Pengguna
        </a>
        <a href="{{ url('/users') }}" title="Pengguna" class="sidebar-link {{ request()->is('users*') ? 'active' : '' }}">
          <span class="icon">👤</span> Pengguna
        </a>
        <a href="{{ url('/dashboard/courier') }}" class="sidebar-link {{ request()->is('dashboard/courier') ? 'active' : '' }}">
          <span class="icon">🚚</span> Kurir
        </a>
        <a href="{{ url('/dashboard/traveler') }}" class="sidebar-link {{ request()->is('dashboard/traveler') ? 'active' : '' }}">
          <span class="icon">✈️</span> Pelancong
        </a>
        <a href="{{ url('/dashboard/admin') }}" class="sidebar-link {{ request()->is('dashboard/admin') ? 'active' : '' }}">
          <span class="icon">🛡️</span> Admin
        </a>
      </nav>
      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="sidebar-avatar">TK</div>
          <div class="sidebar-user-info">
            <div class="sidebar-user-name">Titip Kilat</div>
            <div class="sidebar-user-role">Platform MVP</div>
          </div>
        </div>
      </div>
    </aside>

    <div class="main">
      <header class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
          <button class="menu-toggle" type="button" aria-label="Buka menu">☰</button>
          <span class="topbar-title">{{ $title ?? 'Titip Kilat' }}</span>
        </div>
        <div class="topbar-actions">
        </div>
      </header>
      <div class="content">
        @php
          $pageTitle = $title ?? (request()->is('orders*') ? 'Pesanan' : (request()->is('wallet*') ? 'Dompet Digital' : (request()->is('trips') ? 'Perjalanan' : (request()->is('users*') ? 'Manajemen Pengguna' : (request()->is('settings') ? 'Pengaturan' : 'Titip Kilat')))));
        @endphp
        @if(!request()->is('/'))
          <div class="page-banner"><div><div class="page-banner-kicker"><i class="bi bi-stars"></i> Titip Kilat / Workspace</div><h1>{{ $pageTitle }}</h1><p>Kelola aktivitas platform dengan lebih cepat, jelas, dan aman.</p></div><div class="page-banner-status"><i class="bi bi-broadcast-pin"></i><strong>LIVE OPERATIONS</strong><span>System online · {{ now()->format('d M Y') }}</span></div></div>
        @endif
        @yield('content')
      </div>
    </div>
  </div>

  <div class="login-modal" id="login-modal" role="dialog" aria-modal="true" aria-labelledby="login-modal-title"><div class="login-dialog"><button class="login-close" type="button" aria-label="Tutup">&times;</button><h2 id="login-modal-title">Login</h2><p>Masuk untuk melanjutkan ke Titip Kilat.</p><form class="login-form" method="POST" action="{{ route('login.authenticate') }}">@csrf<label>Email<input name="email" type="email" required autocomplete="email"></label><label>Password<input name="password" type="password" required autocomplete="current-password"></label><button class="btn btn-primary" type="submit" style="justify-content:center">Masuk <i class="bi bi-arrow-right"></i></button></form><div class="login-signup">Belum punya akun?</div><a class="btn btn-outline" href="{{ route('signup') }}" style="width:100%;justify-content:center;margin-top:10px">Sign up</a></div></div>
  <div class="toast-container" id="toast-container" aria-live="polite" aria-atomic="true">
    @if(session('success'))<div class="toast toast-success"><i class="bi bi-check-circle-fill"></i><div><strong>Berhasil</strong><span>{{ session('success') }}</span></div><button class="toast-close" type="button" aria-label="Tutup">&times;</button></div>@endif
    @if(session('error'))<div class="toast toast-error"><i class="bi bi-exclamation-circle-fill"></i><div><strong>Terjadi kesalahan</strong><span>{{ session('error') }}</span></div><button class="toast-close" type="button" aria-label="Tutup">&times;</button></div>@endif
    @if($errors->any())<div class="toast toast-error"><i class="bi bi-exclamation-circle-fill"></i><div><strong>Periksa kembali</strong><span>{{ $errors->first() }}</span></div><button class="toast-close" type="button" aria-label="Tutup">&times;</button></div>@endif
  </div>

  <div class="order-modal" id="order-modal" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
    <div class="order-dialog">
      <button class="order-modal-close" type="button" aria-label="Tutup">&times;</button>
      <h2 id="order-modal-title">Buat pesanan baru</h2>
      <p>Pilih jenis pesanan yang ingin kamu buat.</p>
      <div class="order-modal-options">
        <button class="order-modal-option" type="button" data-order-type="local">
          <i class="bi bi-box-seam"></i><strong>Antar Warga</strong><span>Kurir lokal membeli dan mengantar barangmu.</span>
        </button>
        <button class="order-modal-option" type="button" data-order-type="international">
          <i class="bi bi-airplane-engines"></i><strong>Pre-order Internasional</strong><span>Pilih traveler untuk menitip barang dari luar negeri.</span>
        </button>
      </div>
    </div>
  </div>
  <div class="order-form-modal" id="order-form-modal" role="dialog" aria-modal="true" aria-labelledby="order-form-title"><div class="order-form-dialog"><button class="order-form-close" type="button" aria-label="Tutup">&times;</button><h2 id="order-form-title">Buat pesanan Antar Warga</h2><p id="order-form-description">Isi detail pengiriman tanpa meninggalkan halaman ini.</p><form id="order-form"><div class="order-form-grid"><div class="order-form-field"><label for="order-origin">Zona asal</label><input id="order-origin" required placeholder="JAKARTA"></div><div class="order-form-field"><label for="order-destination">Zona tujuan</label><input id="order-destination" required placeholder="BANDUNG"></div><div class="order-form-field full" id="order-trip-field" hidden><label for="order-trip">Referensi perjalanan</label><input id="order-trip" placeholder="TRIP-DEMO001"></div><div class="order-form-field full" id="order-pickup-field"><label for="order-pickup">Alamat penjemputan</label><input id="order-pickup" required placeholder="Jl. Sudirman No.1"></div><div class="order-form-field full"><label for="order-delivery">Alamat pengiriman</label><input id="order-delivery" required placeholder="Jl. Buah Batu No.42"></div><div class="order-form-field full"><label for="order-description">Deskripsi barang</label><textarea id="order-description" required placeholder="Contoh: Laptop, 1 unit"></textarea></div><div class="order-form-field"><label for="order-weight">Berat (lbs)</label><input id="order-weight" type="number" min="0.1" step="0.1" required placeholder="2.5"></div><div class="order-form-field"><label for="order-count">Jumlah item</label><input id="order-count" type="number" min="1" value="1" required></div></div><div class="order-form-actions"><button class="btn btn-outline" id="order-form-cancel" type="button">Batal</button><button class="btn btn-primary" type="submit">Lanjutkan pesanan <i class="bi bi-arrow-right"></i></button></div></form></div></div>
  <script>
    // Mobile sidebar toggle
    document.addEventListener('DOMContentLoaded', () => {
      const toggle = document.querySelector('.menu-toggle');
      const sidebar = document.getElementById('sidebar');
      if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

      const setupModal = ({ modalId, triggerSelector, closeSelector, focusSelector = 'input' }) => {
        const modal = document.getElementById(modalId);
        const trigger = document.querySelector(triggerSelector);
        if (!modal || !trigger) return;
        const close = modal.querySelector(closeSelector);
        const hide = () => { modal.classList.remove('is-open'); document.body.style.overflow = ''; };
        trigger.addEventListener('click', event => { event.preventDefault(); modal.classList.add('is-open'); document.body.style.overflow = 'hidden'; modal.querySelector(focusSelector)?.focus(); });
        close?.addEventListener('click', hide);
        modal.addEventListener('click', event => { if (event.target === modal) hide(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal.classList.contains('is-open')) hide(); });
      };
      setupModal({ modalId: 'order-modal', triggerSelector: '[data-order-modal]', closeSelector: '.order-modal-close', focusSelector: '.order-modal-close' });
      const orderFormModal=document.getElementById('order-form-modal'); const orderForm=document.getElementById('order-form'); const orderTypeButtons=document.querySelectorAll('[data-order-type]'); const closeOrderForm=()=>{orderFormModal?.classList.remove('is-open');document.body.style.overflow='';}; orderTypeButtons.forEach(button=>button.addEventListener('click',()=>{const international=button.dataset.orderType==='international';document.getElementById('order-modal')?.classList.remove('is-open');orderFormModal?.classList.add('is-open');document.body.style.overflow='hidden';document.getElementById('order-form-title').textContent=international?'Buat Pre-order Internasional':'Buat pesanan Antar Warga';document.getElementById('order-form-description').textContent=international?'Pilih traveler dan isi detail barang tanpa meninggalkan halaman ini.':'Isi detail pengiriman tanpa meninggalkan halaman ini.';document.getElementById('order-trip-field').hidden=!international;document.getElementById('order-pickup-field').hidden=international;document.getElementById('order-trip').required=international;document.getElementById('order-pickup').required=!international;orderFormModal?.querySelector('input')?.focus();})); orderFormModal?.querySelector('.order-form-close')?.addEventListener('click',closeOrderForm);document.getElementById('order-form-cancel')?.addEventListener('click',closeOrderForm);orderFormModal?.addEventListener('click',event=>{if(event.target===orderFormModal)closeOrderForm();});orderForm?.addEventListener('submit',event=>{event.preventDefault();event.stopImmediatePropagation();closeOrderForm();window.showToast?.('Detail pesanan siap diproses.');});
      setupModal({ modalId: 'login-modal', triggerSelector: '[data-login-modal]', closeSelector: '.login-close' });
      const toastContainer = document.getElementById('toast-container');
      const dismissToast = toast => { toast.classList.add('is-leaving'); setTimeout(() => toast.remove(), 220); };
      if (toastContainer) toastContainer.querySelectorAll('.toast').forEach(toast => { toast.querySelector('.toast-close').addEventListener('click', () => dismissToast(toast)); setTimeout(() => dismissToast(toast), 4500); });
      window.showToast = (message, type = 'success') => { if (!toastContainer) return; const error = type === 'error'; const toast = document.createElement('div'); toast.className = `toast toast-${error ? 'error' : 'success'}`; toast.innerHTML = `<i class="bi bi-${error ? 'exclamation-circle-fill' : 'check-circle-fill'}"></i><div><strong>${error ? 'Terjadi kesalahan' : 'Berhasil'}</strong><span></span></div><button class="toast-close" type="button" aria-label="Tutup">&times;</button>`; toast.querySelector('span').textContent = message; toastContainer.appendChild(toast); toast.querySelector('.toast-close').addEventListener('click', () => dismissToast(toast)); setTimeout(() => dismissToast(toast), 4500); };
      document.querySelectorAll('input[type="password"]').forEach(input => { const wrapper=document.createElement('span'); wrapper.className='password-field'; input.parentNode.insertBefore(wrapper,input); wrapper.appendChild(input); const toggle=document.createElement('button'); toggle.type='button'; toggle.className='password-toggle'; toggle.setAttribute('aria-label','Tampilkan password'); toggle.innerHTML='<i class="bi bi-eye"></i>'; toggle.addEventListener('click',()=>{const visible=input.type==='text';input.type=visible?'password':'text';toggle.setAttribute('aria-label',visible?'Tampilkan password':'Sembunyikan password');toggle.innerHTML=`<i class="bi bi-${visible?'eye':'eye-slash'}"></i>`;}); wrapper.appendChild(toggle); });
      document.querySelectorAll('form').forEach(form => form.addEventListener('submit', event => { if (form.hasAttribute('data-delete-user-form')) return; if (form.dataset.submitting === 'true') { event.preventDefault(); return; } form.dataset.submitting = 'true'; form.setAttribute('aria-busy', 'true'); const deleting = form.querySelector('input[name="_method"][value="DELETE"]'); form.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = true; button.classList.add('form-loading'); button.dataset.originalText = button.innerHTML; button.innerHTML = deleting ? 'Menghapus...' : 'Menyimpan...'; }); }));
    });
  </script>
</body>
</html>
