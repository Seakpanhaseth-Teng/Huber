# Huber

> Category: Ride-Sharing
> A warm, trustworthy ride-sharing brand. Combines professional reliability with human warmth — like a trusted driver who always arrives on time with a smile.

## Visual Theme & Atmosphere
Warm professionalism. Confident but not cold, friendly but not casual. Think of a premium hotel lobby — polished, welcoming, every detail intentional. The amber glow of a safe arrival at dusk. Clean lines with warm undertones. Trust is the first impression; warmth is what they remember.

## Color Palette & Roles
- **Primary (Amber):** `#E06810` — CTAs, active states, key highlights. Warm, energetic, trustworthy.
- **Primary Light:** `#FDE6D4` — subtle amber backgrounds, hover states, badges.
- **Primary Dark:** `#A84D08` — active/pressed states, dark mode accent.
- **Dark (Navy):** `#1E3A5F` — text, nav bars, footers, trust anchor. Deep, stable, reliable.
- **Dark Light:** `#E8EDF3` — subtle navy backgrounds, table headers, section dividers.
- **Background:** `#FFFAF5` — warm off-white page background. Never clinical white.
- **Surface:** `#FFFFFF` — cards, modals, dropdowns.
- **Muted:** `#6B7280` — secondary text, captions, labels.
- **Border:** `#E5E0D8` — subtle warm-toned borders, dividers.
- **Success:** `#10B981` — confirmed bookings, verified badges.
- **Warning:** `#F59E0B` — pending actions, alerts.
- **Danger:** `#EF4444` — cancellations, errors, destructive actions.
- **Hero Gradient:** Linear gradient from `#E06810` at 0% to `#1E3A5F` at 100%.

Never pure black; never pure white for backgrounds. The background should always carry a whisper of warmth.

## Typography Rules
- **Display / headings:** `'Inter', -apple-system, system-ui, sans-serif`, weight 700 for display, 600 for subheadings. Inter conveys professionalism and readability.
- **Body:** `'Inter', -apple-system, system-ui, sans-serif`, weight 400.
- **Mono:** `ui-monospace, 'JetBrains Mono', monospace` — for code, times, technical data.
- **Scale (px):** 12 · 14 · 16 · 18 · 20 · 24 · 32 · 40 · 48 · 64
- **Line-height:** 1.6 for body (readability), 1.15 for headings (tight but not cramped).
- **Letter-spacing:** -0.02em on display sizes ≥40px for a confident editorial feel.
- **Hero heading:** 56–64px desktop, 36–40px mobile. Always bold (700).

## Component Stylings
- **Buttons:** 12px radius, 12px padding-block, 24px padding-inline. 
  - Primary = amber fill `#E06810`, white label, weight 600. Hover: `#C05A0E`.
  - Secondary = 1.5px `#E06810` border, transparent fill, amber text. Hover: light amber fill.
  - Ghost = no border, amber text. Hover: amber fill at 10% opacity.
- **Cards:** white surface, 1px `#E5E0D8` border, 16px radius, 24px internal padding, no shadow by default. Hover: subtle elevation (0 4px 12px rgba(0,0,0,0.06)).
- **Inputs:** 1px `#E5E0D8` border, 10px radius, 12px vertical padding. Focus: `#E06810` border, ring offset, no shadow.
- **Links:** `#E06810`, no underline, underline on hover. In nav context: inherit color, amber on hover/active.
- **Badges:** 6px radius, 4px 10px padding. Success = green fill. Warning = amber fill. Danger = red fill. Info = navy fill.

## Layout Principles
- 12-column grid, 1200px max-width, 24px gutters.
- Hero: 70–90vh (content top-biased). Background uses the hero gradient or a full-bleed image overlay.
- Sections: 96px top+bottom spacing desktop, 64px tablet, 48px phone.
- Whitespace is the primary visual separator. Use thin dividers (`1px #E5E0D8`) only between unrelated top-level sections.
- Content should feel airy and uncrowded. When in doubt, add more padding.

## Depth & Elevation
Three levels:
- **Flat (0):** default page surface.
- **Raised (1):** cards, dropdowns. `0 2px 8px rgba(30, 58, 95, 0.06)`.
- **Floating (2):** modals, toasts, sticky nav. `0 8px 24px rgba(30, 58, 95, 0.10)`.

No neumorphism, no glassmorphism. Shadows use the navy channel for a warm-toned shadow instead of pure black.

## Iconography
- Use Font Awesome 6 (regular weight preferred, solid for navigation).
- Icons in the amber brand color for active states, navy for default.
- Icon containers (feature cards): 72px circle, amber-at-10% background, amber icon.
- Keep icon styles consistent — don't mix line and solid weights within the same section.

## Do's and Don'ts
- ✅ Warm whites (`#FFFAF5`) instead of cool grays.
- ✅ One amber accent element per section (don't compete with yourself).
- ✅ Sentence-case for headings; title case only for the brand name "Huber".
- ✅ Generous whitespace — let content breathe.
- ✅ Warm-toned shadows using navy channel instead of black.
- ❌ No gradients (except the hero gradient or a single CTA).
- ❌ No drop shadows on inputs.
- ❌ No more than three type sizes on one screen.
- ❌ No pure black text — use `#1E3A5F` (navy) instead for body text.
- ❌ Don't mix Bootstrap defaults with brand styles. Use brand tokens everywhere.

## Responsive Behavior
- **Desktop ≥ 1024px:** 12-col grid, full hero height.
- **Tablet 640–1023px:** 8-col grid, 16px gutters, hero drops to 60vh.
- **Phone < 640px:** 4-col grid, 12px gutters. Hero at 50vh or auto-height with 80px top padding.
- Navigation collapses to hamburger menu below 768px.
- Cards stack to single column below 640px.

## Agent Prompt Guide
- The brand is "Huber" — a ride-sharing platform. Think Uber + warmth.
- Brand voice: Trustworthy first, warm second. Professional but never corporate.
- Primary action color is amber (`#E06810`). Use it for the main CTA per page, and sparingly elsewhere.
- Backgrounds should always be warm off-white (`#FFFAF5`), never cold gray.
- Body text should be navy (`#1E3A5F`), not black — it's warmer and more readable.
- Use the hero gradient (amber → navy) on landing pages, not on interior pages.
- Do not invent hex values outside this palette. If a request needs a color not in this system, use the closest existing token and note the substitution.
- All generated HTML should use pure Tailwind classes (no Bootstrap) for consistency with the Laravel build pipeline. Use CSS variables for brand tokens.
