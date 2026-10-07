/** Eén Jos per pagina: bestaande hero-, stappen- en accountanimaties gaan voor. */
((Drupal, once) => {
  const instances = new WeakMap();
  Drupal.behaviors.ksaFooterJos = {
    attach(context, settings) {
      once('ksa-footer-jos', '[data-footer-jos]', context).forEach((root) => {
        const main = root.closest('.site')?.querySelector('.site-main');
        const hasJos = main?.querySelector('[data-ksa-jos], [data-jos-login], .steps__jos')
          || [...(main?.querySelectorAll('.ksa-section--steps') || [])].some((section) =>
            section.querySelectorAll('[data-text-media-group="step"]').length > 1);
        if (hasJos) return;

        const stage = root.querySelector('[data-footer-jos-stage]');
        const button = root.querySelector('[data-footer-jos-pause]');
        const reduced = matchMedia('(prefers-reduced-motion: reduce)');
        const events = new AbortController();
        let paused = reduced.matches;
        const update = () => {
          root.classList.toggle('is-paused', paused);
          button.setAttribute('aria-pressed', String(paused));
          button.textContent = Drupal.t(paused ? 'Jos afspelen' : 'Jos pauzeren');
          button.hidden = reduced.matches;
        };
        update();
        stage.append(root.querySelector('template').content.cloneNode(true));
        root.hidden = false;
        Drupal.attachBehaviors(stage, settings);
        button.addEventListener('click', () => { paused = !paused; update(); }, { signal: events.signal });
        reduced.addEventListener('change', () => { paused = reduced.matches; update(); }, { signal: events.signal });
        instances.set(root, () => {
          events.abort();
          Drupal.detachBehaviors(stage, settings, 'unload');
          stage.replaceChildren();
          root.hidden = true;
          root.classList.remove('is-paused');
        });
      });
    },
    detach(context, settings, trigger) {
      if (trigger !== 'unload') return;
      once.remove('ksa-footer-jos', '[data-footer-jos]', context).forEach((root) => {
        instances.get(root)?.();
        instances.delete(root);
      });
    },
  };
})(Drupal, once);
