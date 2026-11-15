---
name: Project overview
description: Forno Celeste is a bakery management app — inventory control, sales tracking, waste/discharge logging, price adjustments, financial overview
type: project
---

Forno Celeste — bakery management system.

**Core problems to solve:**
- Track how many products are left in stock
- Track discharged/wasted products
- Record sales per product
- Manage product prices and price adjustments
- Financial overview (income, expenses, profit)

**Stack:** Laravel 12, PHP 8.4, MySQL, Inertia + Vue 3, Redis, Docker

**Why:** Pedro has no visibility into inventory or per-product sales. The credit card machine only shows total earnings. Spreadsheets don't scale and can't show real-time stock.

**How to apply:** Every feature should be simple and practical for a small bakery operation. Avoid enterprise complexity.

**Pricing model (from spreadsheets, 2026-10-30):**
- Ingredients have a cost derived from package price / package weight
- Ingredient quantities can be in **grams** or **units** (e.g., eggs, Biscoito Lotus)
- Each product has a recipe (list of ingredients + quantities)
- Each product also has a packaging cost
- CMV (Custo de Mercadoria Vendida) = ingredient cost + packaging cost per unit
- CMV % = CMV / sale price — health thresholds: <45% healthy, 45-60% tight, >60% critical
- Ideal CMV for bakery/cookeria: 25-40%

**Product categories (3):** Cookies, Pães Salgados (savory breads), Pães Doces (sweet breads)

**Current menu (13 products):**
- Cinnamons: Puro, Cream Cheese, Doce de Leite, Nutella
- Amanteigado Ninhotella
- Cookies: Classico, Red Velvet, Nutella, Ninhotella, Triplo Chocolate, Choco Maracuja
- Focaccias: Classica, 3 Queijos

**Seed data source:** Google Sheets (link pending) + two CSVs in Downloads
