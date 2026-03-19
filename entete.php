<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Tribune — Actualités</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Source+Serif+4:ital,wght@0,300;0,400;0,600;1,300;1,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* ========= RESET & BASE ========= */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:        #1a1208;
            --ink-light:  #4a3f2e;
            --ink-faint:  #8a7d6b;
            --paper:      #f5f0e8;
            --paper-dark: #ede6d6;
            --paper-rule: #c8bfaa;
            --accent:     #c0392b;
            --accent2:    #1a6bcc;
            --col-gap:    2rem;
            --font-head:  'Playfair Display', Georgia, serif;
            --font-body:  'Source Serif 4', Georgia, serif;
            --font-mono:  'JetBrains Mono', monospace;
        }

        html { font-size: 16px; scroll-behavior: smooth; }

        body {
            background-color: var(--paper);
            color: var(--ink);
            font-family: var(--font-body);
            font-weight: 300;
            line-height: 1.7;
            /* subtle paper texture */
            background-image:
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 27px,
                    rgba(180,160,120,.08) 28px
                );
        }

        a { color: inherit; text-decoration: none; }
        a:hover { color: var(--accent); }
        img { display: block; max-width: 100%; }

        /* ========= MASTHEAD ========= */
        .masthead {
            border-bottom: 3px double var(--ink);
            padding: 0 2rem;
            background: var(--paper);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(26,18,8,.10);
        }

        .masthead__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5rem 0 .4rem;
            border-bottom: 1px solid var(--paper-rule);
            font-family: var(--font-mono);
            font-size: .72rem;
            color: var(--ink-faint);
            letter-spacing: .05em;
        }

        .masthead__date { text-transform: uppercase; }

        .masthead__auth a {
            font-family: var(--font-mono);
            font-size: .72rem;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: .08em;
            border: 1px solid var(--accent);
            padding: .15rem .55rem;
            transition: background .2s, color .2s;
        }
        .masthead__auth a:hover {
            background: var(--accent);
            color: #fff;
        }

        .masthead__logo {
            text-align: center;
            padding: .8rem 0 .6rem;
        }

        .masthead__logo h1 {
            font-family: var(--font-head);
            font-size: clamp(2.4rem, 5vw, 4rem);
            font-weight: 900;
            letter-spacing: -.02em;
            line-height: 1;
            color: var(--ink);
        }

        .masthead__logo h1 span {
            color: var(--accent);
        }

        .masthead__tagline {
            font-family: var(--font-mono);
            font-size: .68rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--ink-faint);
            margin-top: .25rem;
        }

        /* ========= NAV CATÉGORIES ========= */
        .cat-nav {
            border-top: 1px solid var(--paper-rule);
            padding: .5rem 0;
            display: flex;
            gap: .25rem;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .cat-nav::-webkit-scrollbar { display: none; }

        .cat-nav a {
            font-family: var(--font-mono);
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .3rem .8rem;
            border: 1px solid transparent;
            white-space: nowrap;
            transition: all .2s;
            color: var(--ink-light);
        }
        .cat-nav a:hover,
        .cat-nav a.active {
            border-color: var(--ink);
            color: var(--ink);
            background: var(--ink);
            color: var(--paper);
        }

        /* ========= LAYOUT PRINCIPAL ========= */
        .wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem;
        }

        .section-head {
            display: flex;
            align-items: baseline;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: .6rem;
            border-bottom: 3px solid var(--ink);
        }
        .section-head h2 {
            font-family: var(--font-head);
            font-size: 1.3rem;
            font-weight: 700;
            font-style: italic;
        }
        .section-head .count {
            font-family: var(--font-mono);
            font-size: .7rem;
            color: var(--ink-faint);
            letter-spacing: .1em;
        }

        /* ========= GRILLE ARTICLES ========= */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 0;
        }

        /* Séparateurs de colonnes via pseudo-éléments */
        .article-card {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--paper-rule);
            position: relative;
            transition: background .2s;
            animation: fadeUp .4s ease both;
        }
        .article-card:hover { background: rgba(192,57,43,.04); }

        /* Article principal (featured) */
        .article-card--featured {
            grid-column: span 8;
            border-right: 1px solid var(--paper-rule);
            padding: 1.5rem 2rem 1.5rem 1.5rem;
        }
        .article-card--featured .card__title {
            font-size: 1.9rem;
            line-height: 1.2;
        }
        .article-card--featured .card__desc {
            font-size: 1rem;
            -webkit-line-clamp: 4;
        }

        /* Article secondaire (colonne droite) */
        .article-card--secondary {
            grid-column: span 4;
        }
        .article-card--secondary:not(:last-child) {
            border-bottom: 1px solid var(--paper-rule);
        }

        /* Articles standard (ligne 2+) */
        .article-card--standard {
            grid-column: span 4;
            border-right: 1px solid var(--paper-rule);
        }
        .article-card--standard:nth-child(3n) {
            border-right: none;
        }

        /* Fallback colonnes différentes tailles écran */
        @media (max-width: 900px) {
            .article-card--featured,
            .article-card--secondary,
            .article-card--standard {
                grid-column: span 12;
                border-right: none;
            }
        }

        /* ========= CONTENU CARTE ========= */
        .card__badge {
            display: inline-block;
            font-family: var(--font-mono);
            font-size: .62rem;
            font-weight: 500;
            letter-spacing: .12em;
            text-transform: uppercase;
            padding: .15rem .55rem;
            margin-bottom: .6rem;
            color: #fff;
        }

        .card__title {
            font-family: var(--font-head);
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: .55rem;
            color: var(--ink);
            transition: color .2s;
        }
        .article-card:hover .card__title { color: var(--accent); }

        .card__desc {
            font-size: .88rem;
            color: var(--ink-light);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
            margin-bottom: .8rem;
        }

        .card__meta {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-family: var(--font-mono);
            font-size: .65rem;
            color: var(--ink-faint);
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .card__meta .sep { color: var(--paper-rule); }

        .card__lire {
            font-family: var(--font-mono);
            font-size: .65rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--accent);
            border-bottom: 1px solid transparent;
            transition: border-color .2s;
        }
        .card__lire:hover { border-color: var(--accent); color: var(--accent); }

        /* ========= PAGINATION ========= */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 3px double var(--ink);
        }

        .pagination__info {
            font-family: var(--font-mono);
            font-size: .72rem;
            letter-spacing: .1em;
            color: var(--ink-faint);
            text-transform: uppercase;
        }

        .btn-page {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-family: var(--font-mono);
            font-size: .72rem;
            font-weight: 500;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .5rem 1.2rem;
            border: 1.5px solid var(--ink);
            color: var(--ink);
            background: transparent;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-page:hover:not(:disabled) {
            background: var(--ink);
            color: var(--paper);
        }
        .btn-page:disabled {
            opacity: .3;
            cursor: not-allowed;
            pointer-events: none;
        }
        .btn-page svg { width: 14px; height: 14px; fill: currentColor; }

        /* ========= ÉTAT VIDE ========= */
        .empty-state {
            grid-column: span 12;
            text-align: center;
            padding: 5rem 2rem;
            color: var(--ink-faint);
        }
        .empty-state__icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: .4;
        }
        .empty-state p {
            font-family: var(--font-head);
            font-size: 1.2rem;
            font-style: italic;
        }

        /* ========= FOOTER ========= */
        .footer {
            border-top: 3px double var(--ink);
            padding: 1.5rem 2rem;
            text-align: center;
            font-family: var(--font-mono);
            font-size: .65rem;
            letter-spacing: .12em;
            color: var(--ink-faint);
            text-transform: uppercase;
        }

        /* ========= ANIMATION ========= */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .article-card:nth-child(1)  { animation-delay: .05s; }
        .article-card:nth-child(2)  { animation-delay: .10s; }
        .article-card:nth-child(3)  { animation-delay: .15s; }
        .article-card:nth-child(4)  { animation-delay: .20s; }
        .article-card:nth-child(5)  { animation-delay: .25s; }
        .article-card:nth-child(6)  { animation-delay: .30s; }
        .article-card:nth-child(7)  { animation-delay: .35s; }
        .article-card:nth-child(8)  { animation-delay: .40s; }
        .article-card:nth-child(9)  { animation-delay: .45s; }
        .article-card:nth-child(10) { animation-delay: .50s; }
    </style>
</head>
<body>
