# Mobile Responsiveness Plan — Forno Celeste

> Audit date: 2026-11-15
> Stack: Tailwind CSS 4, Vue 3, Inertia.js
> Breakpoints used: `sm` (640px), `lg` (1024px) — no `md` usage anywhere

---

## 1. Current State Summary

The app already has a foundation for mobile:
- `AppLayout.vue` has a collapsible sidebar with overlay for `lg:hidden`
- A mobile top bar with hamburger menu exists
- Main content uses `p-4 sm:p-6 lg:p-8` responsive padding
- Dashboard cards use `sm:grid-cols-2 lg:grid-cols-4` grids

**However, many screens have significant mobile problems.** The issues fall into seven categories documented below.

---

## 2. Issue Categories and Fixes

### 2.1 Tables Not Usable on Mobile

**Problem:** Every Index page and several Show/Dashboard pages render `<table>` elements with `min-w-full`. On a 375px screen, the horizontal scroll is hidden behind `overflow-hidden` on the wrapper, or requires `overflow-x-auto` which most tables have but the content is still too wide to be useful.

**Affected files:**
- `resources/js/Pages/Catalog/Products/Index.vue` — 6 columns (Nome, Categoria, Preco, CMV, CMV%, Acoes)
- `resources/js/Pages/Catalog/Ingredients/Index.vue`
- `resources/js/Pages/Catalog/Recipes/Index.vue`
- `resources/js/Pages/Sales/DailyClosings/Index.vue` — 6 columns (Data, Cartao, Dinheiro, Receita, Perdas, Acoes)
- `resources/js/Pages/Sales/DailyClosings/Show.vue` — Withdrawals table, Wastes table (4 cols)
- `resources/js/Pages/Finance/Bills/Index.vue` — DataTable with 8 columns for paid bills
- `resources/js/Pages/Dashboard.vue` — CMV por Produto table (4 cols)
- `resources/js/Pages/Catalog/Products/Show.vue` — Ingredients tables
- `resources/js/Pages/Catalog/Recipes/Show.vue` — Ingredients table
- `resources/js/Components/DataTable.vue` — shared table component

**Fix — card-based mobile layout pattern:**

For every table, implement a dual-render approach:
1. Keep the `<table>` for `sm:` and above (`hidden sm:block` on the table wrapper)
2. Add a mobile card list (`sm:hidden`) that shows each row as a stacked card

Example pattern for Products Index:
```vue
<!-- Mobile cards -->
<div class="space-y-3 sm:hidden">
  <Link
    v-for="product in products.data"
    :key="product.id"
    :href="route('produtos.show', product.id)"
    class="block rounded-xl bg-white p-4 shadow-sm active:bg-gray-50"
  >
    <div class="flex items-center justify-between">
      <span class="font-medium text-gray-900">{{ product.name }}</span>
      <CmvBadge v-if="product.cmv" :percent="product.cmv.cmv_percent" size="sm" />
    </div>
    <div class="mt-2 flex items-center justify-between text-sm text-gray-500">
      <span>{{ product.category?.name || '—' }}</span>
      <span class="font-semibold text-gray-900">{{ formatMoney(product.sale_price_cents) }}</span>
    </div>
  </Link>
</div>

<!-- Desktop table -->
<div class="hidden sm:block">
  <table>...</table>
</div>
```

**For DataTable.vue specifically:**
- Add a `mobileSlot` or `#mobile-row` slot so each page can define its own mobile card layout
- If no mobile slot is provided, fall back to a generic stacked key-value card

**Priority: HIGH** — Tables are the most-used screens and completely broken on mobile.

---

### 2.2 Form Layouts on DailyClosings/Create

**Problem:** The DailyClosings Create page uses `lg:grid-cols-3` with a 2/3 + 1/3 split. On mobile, the "Resumo do Dia" sidebar renders below all form sections, meaning the user fills the entire form without seeing the live calculation until they scroll past everything.

**Affected file:** `resources/js/Pages/Sales/DailyClosings/Create.vue`

**Fixes:**

A) **Sticky summary on mobile.** Convert the summary sidebar into a compact sticky bottom bar on mobile:
```vue
<!-- Mobile sticky summary bar -->
<div class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 bg-white px-4 py-3 shadow-lg lg:hidden">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-xs text-gray-500">Receita Total</p>
      <p class="text-lg font-bold text-indigo-700">{{ formatMoney(totalRevenueCents) }}</p>
    </div>
    <button type="submit" :disabled="form.processing"
      class="rounded-lg bg-indigo-700 px-5 py-2.5 text-sm font-semibold text-white">
      Salvar
    </button>
  </div>
</div>
```
Add `pb-24 lg:pb-0` to the form wrapper to prevent content from hiding behind the bar.

B) **Withdrawal and waste inline rows.** The withdrawal rows use `flex items-start gap-3` with a `w-40 shrink-0` money input + flex-1 description + remove button. On a 375px screen, this overflows. Fix:
- Stack the money input and description vertically on mobile: `flex flex-col sm:flex-row`
- Make the remove button absolutely positioned or inline:
```html
<div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:gap-3 ...">
  <div class="w-full sm:w-40 sm:shrink-0">
    <MoneyInput ... />
  </div>
  <div class="flex-1">
    <input ... />
  </div>
  <button class="self-end sm:self-auto ...">...</button>
</div>
```

C) Same pattern for waste rows — the `w-24 shrink-0` quantity + `w-28 shrink-0` cost line overflows on mobile.

**Also applies to:** `DailyClosings/Edit.vue` (same layout)

**Priority: HIGH** — This is the daily-use screen Pedro and Joyce fill from their phones.

---

### 2.3 Dashboard Cards and Charts

**Problem areas in `resources/js/Pages/Dashboard.vue`:**

1. **Row 2 chart layout** uses `lg:grid-cols-5` (3/5 chart + 2/5 bills). On mobile this stacks fine, but the SVG chart (`viewBox="0 0 800 200"`) with `preserveAspectRatio="none"` becomes very squished on narrow screens. The date labels overlap.

2. **Upcoming Bills cards** inside the dashboard have small "Pagar" buttons (touch target ~32x28px, below the 44x44px minimum).

3. **Forecast section** uses `grid-cols-1 sm:grid-cols-3` for the three stat boxes. The text "Media diaria" / "Dias p/ cobrir" / "Falta receber" with `text-center` looks fine.

**Fixes:**

A) **Chart minimum height on mobile.** Wrap the SVG in a container with `min-h-[160px]` and change `preserveAspectRatio` to `xMidYMid meet` on mobile, or keep the current approach but add horizontal scroll for the date labels:
```html
<div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
  <div class="min-w-[500px]">
    <svg viewBox="0 0 800 200" class="w-full" preserveAspectRatio="none">...</svg>
  </div>
</div>
```

B) **Increase touch targets on "Pagar" button** in the upcoming bills section — change from `px-2.5 py-1` to `px-3 py-2` (minimum 44px tall).

C) **Sales Dashboard bar chart** (`resources/js/Pages/Sales/Dashboard.vue`) already has `overflow-x-auto` with `min-w-[600px]` — this is acceptable.

**Priority: MEDIUM**

---

### 2.4 Finance Dashboard Density

**Problem:** `resources/js/Pages/Finance/Dashboard.vue` has:

1. **Period selector** with date inputs + 3 preset buttons in `flex flex-wrap items-end gap-4`. On mobile the date inputs take full width but the preset buttons wrap awkwardly.

2. **Summary cards** use `sm:grid-cols-2 lg:grid-cols-4` — fine, but with the icon + text layout, the text gets cramped on narrow screens.

3. **Cost composition** pie chart uses `flex flex-col items-center gap-8 lg:flex-row` — stacks fine on mobile. The legend list is readable.

4. **Upcoming bills list** — the `flex items-center justify-between` rows with description + badge + due date + amount compress too tightly. Text truncation may cut important info.

**Fixes:**

A) Period selector: Make preset buttons stack below the date inputs:
```html
<div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:gap-4">
  <div class="grid grid-cols-2 gap-3 sm:contents">
    <!-- date from -->
    <!-- date to -->
  </div>
  <div class="flex gap-2">
    <!-- preset buttons -->
  </div>
</div>
```

B) Summary cards: On very narrow screens (below 380px), the `flex items-center gap-4` layout with the 48x48 icon squeezes text. Add `min-w-0` on the text container and consider hiding the icon below a certain width, or stacking: `flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:gap-4`.

C) Upcoming bills row: Stack the description and amount vertically on mobile instead of side-by-side.

**Priority: MEDIUM**

---

### 2.5 Touch Targets Too Small

**Problem:** Multiple interactive elements are below the 44x44px recommended minimum:

| Element | File | Current size | Fix |
|---------|------|-------------|-----|
| Edit/Delete icon buttons in tables | Products/Index, Closings/Index, etc. | `p-1.5` with 16px icon = ~28px | Increase to `p-2.5` on mobile = 36px, or use min-h/min-w-[44px] |
| Sidebar nav group toggle | AppLayout.vue | `px-3 py-2` text button | Already 40px+ tall, acceptable |
| Sidebar nav items | AppLayout.vue | `px-3 py-2` = ~36px tall | Increase to `py-2.5` for 40px+ |
| "Pagar" button on dashboard bills | Dashboard.vue | `px-2.5 py-1` = ~28px tall | Increase to `py-2 px-3` |
| "Editar"/"Excluir" text buttons on bill cards | Bills/Index.vue | `px-2 py-1` = ~28px tall | Increase to `py-2 px-3 min-h-[44px]` |
| Month navigation arrows | Bills/Index.vue | `p-2` with 20px icon = ~36px | Increase to `p-2.5` |
| Pagination links | DailyClosings/Index.vue | `px-3 py-2` = ~36px | Add `min-h-[44px] min-w-[44px] flex items-center justify-center` |

**Fix pattern:** Create a utility approach — anywhere a `p-1.5` icon button exists, use:
```html
class="rounded-lg p-1.5 sm:p-1.5 min-h-[44px] min-w-[44px] flex items-center justify-center ..."
```
Or simply increase padding to `p-2.5` on mobile.

**Priority: HIGH** — These are the buttons Pedro taps dozens of times daily.

---

### 2.6 Modal Sizing on Mobile

**Problem:** `resources/js/Components/Modal.vue` uses `fixed inset-0 z-50 flex items-center justify-center p-4` with modal content using `max-w-md` (448px). On a 375px screen with 16px padding on each side, the usable width is 343px — this is fine for max-w-sm/md modals.

**However:**
1. The modal has no max-height constraint. If content is long, it will overflow the viewport with no scroll.
2. The `DeleteConfirmModal` buttons are small (`px-4 py-2` = ~36px tall) — acceptable but could be larger on mobile.

**Fixes:**

A) Add `max-h-[90vh] overflow-y-auto` to the modal content container:
```html
<div
  v-if="show"
  class="relative w-full max-h-[90vh] overflow-y-auto rounded-xl bg-white p-6 shadow-xl"
  :class="maxWidthClass[maxWidth] || 'max-w-md'"
>
```

B) On mobile, DeleteConfirmModal buttons should be full-width and stacked:
```html
<div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-center sm:gap-3">
  <button class="w-full sm:w-auto ...">Cancelar</button>
  <button class="w-full sm:w-auto ...">Excluir</button>
</div>
```

**Priority: LOW** — Modals are used infrequently (only for delete confirmation).

---

### 2.7 Navigation and Sidebar

**Current state:** The sidebar implementation in `AppLayout.vue` is already well done:
- Hidden off-screen on mobile with `-translate-x-full`
- Opens via hamburger button in the mobile top bar
- Has an overlay backdrop
- Links close the sidebar on tap (`@click="sidebarOpen = false"`)

**Remaining issues:**

1. **No swipe-to-close.** On mobile, users expect to swipe left to dismiss the sidebar. This is a nice-to-have, not critical.

2. **Sidebar scroll area.** The nav uses `overflow-y-auto` which is correct, but on very small screens (iPhone SE at 568px height minus top bar), the nav items may not all be visible. The current 3 groups with ~12 items total should fit, but barely.

3. **Active state visibility.** The `bg-indigo-700` active state is visible, good.

**Fixes:**

A) Optional: Add touch swipe handler using a simple `@touchstart`/`@touchend` listener on the sidebar to detect left swipe and close.

B) Add `safe-area-inset` support for devices with notches:
```html
<!-- In AppLayout.vue, add to the sidebar -->
<aside class="... pb-[env(safe-area-inset-bottom)]">
```
And to the mobile top bar:
```html
<header class="... pt-[env(safe-area-inset-top)]">
```

**Priority: LOW**

---

### 2.8 Flash Message Positioning

**Problem:** `FlashMessage.vue` is positioned `fixed top-4 right-4` with `max-w-sm`. On mobile, this overlaps with the hamburger menu area and may be too wide.

**Fix:**
```html
class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 z-50 sm:max-w-sm rounded-lg px-4 py-3 shadow-lg"
```
This makes it full-width (minus padding) on mobile, and right-aligned on desktop.

**Priority: LOW**

---

## 3. Global Patterns to Implement

### 3.1 Responsive Table Component

Enhance `DataTable.vue` to accept a mobile card template:

```vue
<DataTable :columns="columns" :rows="rows">
  <template #mobile-row="{ row }">
    <!-- Custom mobile card layout per page -->
  </template>
  <template #row="{ row }">
    <!-- Desktop table row, already exists -->
  </template>
</DataTable>
```

The component internally renders:
- `<div class="sm:hidden">` with mobile cards
- `<div class="hidden sm:block">` with the table

### 3.2 Page Header Pattern

Standardize the page header (title + action button) across all Index pages. Currently they all use `flex items-center justify-between` which works, but the button text wraps on narrow screens when the title is long.

Fix: Allow the button to shrink its text or go icon-only on mobile:
```html
<div class="flex items-center justify-between gap-3">
  <h1 class="min-w-0 truncate text-2xl font-bold text-[#1e293b]">Title</h1>
  <Link class="... shrink-0">
    <svg class="h-4 w-4" .../>
    <span class="hidden sm:inline">Novo Produto</span>
  </Link>
</div>
```

### 3.3 Form Bottom Action Bar

For all Create/Edit forms, on mobile the submit button should be reachable without scrolling to the bottom. Pattern:

```vue
<!-- Sticky mobile submit bar -->
<div class="fixed bottom-0 left-0 right-0 z-20 border-t bg-white p-4 shadow-lg sm:hidden">
  <button type="submit" class="w-full rounded-lg bg-indigo-700 py-3 text-sm font-semibold text-white">
    Salvar
  </button>
</div>
<!-- Desktop actions stay inline -->
<div class="hidden sm:flex items-center justify-end gap-3 mt-6">
  ...
</div>
```

Applies to: All Create.vue and Edit.vue pages (~14 files).

---

## 4. Implementation Priority

### Phase 1 — Critical (daily-use screens)
| # | Task | Files |
|---|------|-------|
| 1 | Mobile card layout for DailyClosings/Index | `Sales/DailyClosings/Index.vue` |
| 2 | Sticky summary bar on DailyClosings/Create | `Sales/DailyClosings/Create.vue`, `Edit.vue` |
| 3 | Stack withdrawal/waste rows on mobile | `Sales/DailyClosings/Create.vue`, `Edit.vue` |
| 4 | Increase touch targets on all icon buttons | All Index pages, Dashboard |
| 5 | Mobile card layout for Products/Index | `Catalog/Products/Index.vue` |

### Phase 2 — Important (weekly-use screens)
| # | Task | Files |
|---|------|-------|
| 6 | Mobile card layout for Bills/Index unpaid cards | Already card-based, fix button sizes |
| 7 | DataTable mobile slot for paid bills table | `Components/DataTable.vue`, `Finance/Bills/Index.vue` |
| 8 | Finance Dashboard period selector mobile layout | `Finance/Dashboard.vue` |
| 9 | Dashboard upcoming bills touch targets | `Pages/Dashboard.vue` |
| 10 | Mobile card layout for Ingredients/Recipes Index | `Catalog/Ingredients/Index.vue`, `Catalog/Recipes/Index.vue` |

### Phase 3 — Polish
| # | Task | Files |
|---|------|-------|
| 11 | Modal max-height and button stacking | `Components/Modal.vue`, `Components/DeleteConfirmModal.vue` |
| 12 | Flash message full-width on mobile | `Components/FlashMessage.vue` |
| 13 | Safe-area-inset support | `Layouts/AppLayout.vue` |
| 14 | Form submit sticky bar pattern on all forms | All Create/Edit pages |
| 15 | Page header icon-only button on mobile | All Index pages |

---

## 5. Screens Already Working Well on Mobile

These need no changes or only minor tweaks:
- **Login page** (`Auth/Login.vue`) — simple centered form
- **Product Show** (`Catalog/Products/Show.vue`) — uses `lg:grid-cols-2` stacking, readable
- **Recipe Show** (`Catalog/Recipes/Show.vue`) — uses `lg:grid-cols-3`, stacks on mobile
- **Dashboard main cards** — 4-card grid collapses properly
- **Sidebar/navigation** — already has mobile hamburger + overlay
- **PricingSimulator** — single-column card layout, works well
- **Sales Dashboard summary cards** — `sm:grid-cols-2 lg:grid-cols-5` collapses
- **Bill Create/Edit forms** — simple single-column forms

---

## 6. Testing Checklist

After implementation, test on:
- [ ] iPhone SE (375x667) — smallest common screen
- [ ] iPhone 14/15 (390x844) — most common size
- [ ] Android medium (360x800)
- [ ] iPad Mini (768px) — tests the sm/lg breakpoint gap
- [ ] Landscape phone (667x375) — edge case

Key interactions to verify:
- [ ] Can complete a daily closing from start to finish on phone
- [ ] Can browse and tap into any product
- [ ] Can view and pay a bill from the dashboard
- [ ] Can navigate all sections via sidebar
- [ ] All buttons/links are easily tappable (44px min)
- [ ] No horizontal scroll on any page (except intentional chart scroll)
- [ ] Flash messages are visible and dismissable
- [ ] Modals don't overflow the viewport
