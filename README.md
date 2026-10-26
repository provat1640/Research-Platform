# Co-Auth · Collaborative Academic Research Studio & MCP Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x%20%2F%2013.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Model Context Protocol](https://img.shields.io/badge/MCP-Enabled-00ADD8?style=for-the-badge)](https://modelcontextprotocol.io)
[![Tests](https://img.shields.io/badge/Tests-53%20Passing-brightgreen?style=for-the-badge)]()
[![Code Style](https://img.shields.io/badge/Code%20Style-Laravel%20Pint-blue?style=for-the-badge)]()

**Co-Auth** is an enterprise-grade collaborative research studio designed for graduate researchers, doctoral candidates, co-authors, and thesis advisors. It bridges academic writing pipelines with context-aware AI engines using the **Model Context Protocol (MCP)**, offering real-time multi-user synchronization, epistemological methodology theory differentiation, readymade paper outlines, in-context citation management, authentic paper sheet layouts, and instant BibTeX/Markdown exports.

---

## Architecture Overview

```
                               ┌───────────────────────────────────────────────┐
                               │           Browser Research Studio             │
                               │  (Multi-user Canvas, Layouts, Citations)      │
                               └──────────────┬───────────────────┬────────────┘
                                              │                   │
                                   REST / JSON APIs        Echo WebSockets
                                              │                   │
                                              ▼                   ▼
┌─────────────────────────┐       ┌───────────────────────┬────────────────────┐
│ External AI / MCP Agent │ <───> │ Laravel MCP Endpoint  │   Laravel Reverb   │
│ (Claude, Gemini, etc.)  │       │   (/mcp/research)     │ (Port 8080 Sockets)│
└─────────────────────────┘       └───────────┬───────────┴────────────────────┘
                                              │
                                  Eloquent ORM / SQLite / MySQL
                                              │
                                              ▼
                                 ┌─────────────────────────┐
                                 │ Relational Database     │
                                 │ - Projects & Members    │
                                 │ - Papers & Versions     │
                                 │ - Citations & Media     │
                                 │ - Tasks & Feedback      │
                                 └─────────────────────────┘
```

---

## Quickstart: 1-Step Setup

The platform includes an automated setup routine in `composer.json` that provisions the environment file, generates app keys, sets up the MySQL database, migrates tables, links storage for figure uploads, seeds demo users/projects, and builds the frontend bundle in a single command.

```bash
composer run setup
```

Once setup completes, launch the entire system (web server, Reverb WebSockets, queue worker, and Vite dev server) with:

```bash
composer run dev
```

*Or run with Artisan:*
```bash
php artisan dev
```

The application will be live at **`http://127.0.0.1:8000`**.

---

## Manual Step-by-Step Setup

If you prefer executing each setup step manually:

### 1. Prerequisites
- **PHP 8.4+** with `pdo_sqlite` (or `pdo_mysql`), `mbstring`, `fileinfo`, `curl`
- **Composer 2.x**
- **Node.js 18+** & **npm**

### 2. Clone & Install Dependencies
```bash
git clone https://github.com/provat1640/Research-Platform.git
cd Research-Platform

# Install PHP and JS dependencies
composer install
npm install
```

### 3. Environment & Storage Configuration
```bash
# Copy environment configuration
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database file (if using default SQLite)
touch database/database.sqlite

# Create symbolic link for uploaded media and figures
php artisan storage:link
```

### 4. Database Migration & Demo Data Seeding
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. Build Frontend Assets
```bash
# Build production bundle
npm run build

# Or launch Vite in development mode
npm run dev
```

---

## Pre-seeded Demo Accounts

The database seeder automatically provisions test accounts for key academic roles:

| Role | Email | Password | Purpose |
| :--- | :--- | :--- | :--- |
| **Student / Primary Author** | `test@example.com` | `password` | Drafts thesis, manages citations, inserts figures, writes manuscript |
| **Supervisor / Academic Advisor** | `alex@example.com` | `password` | Reviews drafts, adds feedback notes, validates methodology |
| **Co-Author / Research Partner** | `mira@example.com` | `password` | Collaborates on sections, uploads empirical media, manages milestones |

---

## Key Features & Implementations

### 1. Epistemological Methodology Theory Differentiation
- **Interactive Theory Card**: Automatically updates when choosing Qualitative, Quantitative, Mixed Methods, Design Science, PRISMA Literature Review, or Action Research.
- **Deep Comparison Modal**: Guides researchers on ontological foundations (Constructivism vs. Positivism vs. Pragmatism vs. Utility Theory vs. Critical Realism), data collection types, and academic rigor criteria (e.g. Lincoln & Guba trustworthiness vs. statistical p-values).

### 2. Academic Figures & Visual Evidence Gallery
- **Multi-Source Ingestion**: Add figures via image upload (stored in `storage/app/public/media`) or public URL / Unsplash.
- **Automatic Academic Attribution**: Automatically calculates `Figure X` sequential numbers, captures captions, and inserts responsive `<figure>` elements into the paper canvas.

### 3. Ready-to-Use Research Paper Templates
- **IMRaD Manuscript**: Standard Introduction, Methods, Results, and Discussion format.
- **IEEE / ACM Computer Science**: Tailored for algorithms, architectures, empirical benchmarks, and threats to validity.
- **Qualitative Case Study / Thesis**: Grounded in inductive inquiry, interview narratives, and thematic qualitative coding.
- **Systematic Literature Review (PRISMA)**: Scaffolding for search strings, inclusion/exclusion criteria, and gap identification.
- **Design Science Research (DSR)**: Focused on artifact creation, prototype demonstrations, and utility theory evaluations.

### 4. In-Context Citations & BibTeX (`.bib`) Export
- **In-Text Chips**: Seamlessly insert formatted reference tags like `[1]` or `[2]` right at your cursor position.
- **Automatic Bibliography Generator**: Compiles an APA/IEEE-formatted references list at the end of the manuscript in one click.
- **BibTeX Export**: Export references directly via `GET /api/v1/projects/{id}/citations/export?download=1` or copy BibTeX entries for direct integration into LaTeX, Overleaf, or Zotero.
- **Live Search Filter**: Filter the citation library instantly by author, title, or citation key.

### 5. Workspace Layouts & Typography
- **Layout Switcher**:
  - `Studio`: Full 3-pane workstation with manuscript, controls, and multi-tab collaboration sidebar.
  - `Focus (Zen)`: Centered distraction-free writing sheet.
  - `Reviewer Split`: Side-by-side manuscript and supervisor feedback desk.
- **Paper Margins**: Normal (1.0"), Narrow (0.5"), Moderate (0.75"), and Wide (1.25" with room for annotations).
- **Column Modes**: Single-column standard thesis vs. Two-column IEEE/ACM conference format.
- **Typefaces & Spacing**: Academic Serif (Georgia), Modern Sans (Manrope), and Technical Mono (DM Mono) with 1.0, 1.15, 1.5, and 2.0 line heights.
- **Themes**: Clean White Paper, Warm Sepia Manuscript, and Academic Dark Slate.

### 6. Seamless Multi-User Collaboration & Presence
- **Active Working Directory**: Enables an individual researcher or a group of 2-3 co-authors and advisors to work seamlessly without colliding.
- **Live Presence Heartbeat**: Broadcasts who is currently editing which section with live editor status banners.
- **Collaborator Invitations**: Assign custom roles (`student`, `co-author`, `supervisor`, `reviewer`).
- **Version Trail**: Every version snapshot is immutable and can be restored at any point.

### 7. Real-Time Manuscript Metrics & Markdown Export
- **Live Word & Character Count**: Continuously calculates total word count and characters as you type.
- **Estimated Reading Time**: Computes reader comprehension time based on academic reading speeds (220 wpm).
- **Instant Markdown (.md) Export**: One-click download of the complete manuscript converted to clean GitHub-flavored Markdown.
- **Print / PDF Engine**: Dedicated `@media print` stylesheets format the manuscript cleanly onto physical paper or PDF.

### 8. Model Context Protocol (MCP) Server
- **Endpoint**: `/mcp/research` (guarded by access middleware).
- **`ProjectContextTool`**: Exposes structured project data, research questions, methodology, collaborators, open tasks, unresolved supervisor feedback, cited papers, and media figures to external LLM agents (Claude, Cursor, Gemini).

---

## Testing & Code Quality

The repository includes a comprehensive automated test suite covering all APIs, access gates, collaboration endpoints, and formatting controls.

```bash
# Run all tests
php artisan test

# Run tests with filter
php artisan test --filter=CitationAndMediaApiTest

# Run code style formatting (Laravel Pint)
vendor/bin/pint --dirty --format agent
```

---

## Directory Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CitationController.php       # In-text citation library & BibTeX export
│   │   │   ├── ProjectMediaController.php   # Figure upload and gallery management
│   │   │   ├── ResearchProjectController.php# Workspace, formatting, presence & versions
│   │   │   └── ...
│   │   └── Middleware/                      # Project membership & MCP access controls
│   ├── Mcp/
│   │   ├── Servers/ResearchServer.php       # Model Context Protocol server
│   │   └── Tools/ProjectContextTool.php     # Comprehensive context extraction tool
│   └── Models/                              # Eloquent entities (Project, Citation, Media, etc.)
├── database/
│   ├── migrations/                          # Schema blueprints
│   └── seeders/DatabaseSeeder.php           # Pre-configured demo accounts and thesis projects
├── resources/
│   ├── css/app.css                          # Studio styling, paper viewport, themes & print rules
│   ├── js/workspace.js                      # Real-time studio engine, presence, templates, stats
│   └── views/
│       ├── dashboard.blade.php              # Research overview & project listings
│       └── project-workspace.blade.php      # Three-pane authentic paper studio & dialogs
├── routes/
│   ├── api.php                              # Versioned REST API endpoints
│   ├── ai.php                               # Model Context Protocol routes
│   └── web.php                              # Application views & authentication
└── tests/
    └── Feature/                             # Automated PHPUnit feature test suite
```

---

## License

This project is open-sourced software licensed under the [MIT License](LICENSE).
