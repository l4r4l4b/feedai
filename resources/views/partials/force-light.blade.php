{{--
    FeedAI is always-light by design.

    The Flux UI library otherwise auto-manages a `dark` class on <html> based
    on the user's OS theme or a stored preference, which causes its dark-mode
    Tailwind variants to fire on our light-canvas backgrounds, producing
    light text on light backgrounds.

    This snippet runs as early as possible to:
      1. Remove any pre-existing `.dark` class on the documentElement
      2. Clear the persisted `flux.appearance` preference

    The `@fluxAppearance` directive must NOT be included anywhere in the app.
--}}
<script>
    document.documentElement.classList.remove('dark');
    try { window.localStorage.removeItem('flux.appearance'); } catch (e) {}
</script>
