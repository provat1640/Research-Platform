# Co-Auth MCP: Smart Collaborative Research Platform

An advanced, real-time web application engineered to bridge collaboration barriers between university students and academic professors during research paper development. This platform leverages the **Model Context Protocol (MCP)** to plug AI agents directly into structural development files, local Zotero reference indices, and database layers safely.

---

## 🛠️ Architecture & Core Features

*   **Model Context Protocol (MCP) Node:** Operates as a native protocol client mapping contextual files, datasets, and citation workflows directly to LLM prompt environments to ensure hyper-accurate, non-hallucinated copy generation.
*   **Real-time Block Workspace:** Powered by **Laravel Reverb (WebSockets)** and a **TipTap / Editor.js** block layout framework to support simultaneous multi-user typing, instant change tracking, and graphic rendering loops.
*   **Topic Semantics Diversity Check:** Embeds native MySQL `FULLTEXT` matching constraints during manuscript initialization to structurally flag topic duplication against prior projects.
*   **Verified Academic Profile Management:** Integrates the public **ORCID OAuth 2.0 API** to securely manage professor/student system access points using officially registered ORCID IDs.
*   **Metered Trial Access:** Employs **Laravel Cashier (Stripe Sandbox)** configuration layers to grant individual accounts free 7-day trials for intensive AI operations before imposing subscription wall gates.
*   **Unified Review Panel:** Features a split-pane dashboard for academic evaluators to apply modular reviews, verify version records, and execute embedded code test cases.

---

## 🚀 Environment Infrastructure Setup

### Prerequisites
*   **PHP 8.x** & **Composer**
*   **Node.js (v18+)** & **NPM**
*   **MySQL Server Engine**

### Installation Guide
Clone the project repository workspace to your local device framework path:

```bash
# 1. Clone your project copy down
git clone https://github.com
cd Research-Platform

# 2. Build local environment settings
cp .env.example .env
php artisan key:generate

# 3. Pull development package dependencies
composer require laravel/cashier
composer install
npm install

# 4. Migrate structural database maps
php artisan migrate

# 5. Compile live assets
npm run dev
```

---

## 👥 Core Git Team Workflow Rules

To maintain high development velocities across our 3-person team, strictly observe the branching guidelines outlined below:

1. **Synchronize System States First:** At the beginning of any work session, always query updates directly from the main master branch to keep your environment fresh:
   ```bash
   git pull origin main
   ```
2. **Isolate Target Workloads:** Never dump raw updates straight to the default track. Always provision a feature branch name:
   ```bash
   git checkout -b feature/your-assigned-task
   ```
3. **Stage, Document and Push Change Components:** Save chunks of logical improvements systematically:
   ```bash
   git add .
   git commit -m "feat: added orcid registration logic controllers"
   git push origin feature/your-assigned-task
   ```
4. **Merge Protocols:** Open a clear **Pull Request (PR)** on the central GitHub dashboard page. Do not merge your own code until at least one other team partner inspects the code changes.
