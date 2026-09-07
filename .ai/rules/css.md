---
paths:
  - 'resources/css/**'
---

# Css

## Flux CSS is vendored into resources/, not imported from vendor/
resources/css/app.css imports resources/css/vendor/flux.css (a committed copy), not vendor/livewire/flux/dist/flux.css directly. The Vercel deploy for this app only runs `npm install && npm run build` — there is no Composer step, so the gitignored vendor/ directory doesn't exist there and a direct import fails to resolve. After running `composer update livewire/flux`, re-copy vendor/livewire/flux/dist/flux.css to resources/css/vendor/flux.css so the two stay in sync.
