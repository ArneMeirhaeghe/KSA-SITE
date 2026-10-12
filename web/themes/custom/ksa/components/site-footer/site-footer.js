/** Jos volgt de aanwijzer binnen de footer, zonder de navigatie te bedekken. */
((Drupal, once) => {
  const instances = new WeakMap();
  Drupal.behaviors.ksaFooterJos = {
    attach(context, settings) {
      once('ksa-footer-jos', '[data-footer-jos]', context).forEach((root) => {
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
        const fox = stage.querySelector('[data-ksa-jos]');
        // De footer heeft een eigen regisseur; de hero behoudt zijn dagschema.
        fox.removeAttribute('data-ksa-jos');
        const walker = fox.querySelector('.ksa-jos__walker');
        const foxButton = fox.querySelector('[data-jos-next]');
        foxButton.setAttribute('aria-label', Drupal.t('Laat Jos naar de andere kant lopen'));
        const footer = root.closest('.site-footer__card');
        let x = 0;
        let target = 0;
        let frame = 0;
        let previous = 0;
        let visible = false;
        const limit = () => Math.max(0, fox.clientWidth - walker.offsetWidth);
        const stop = () => {
          cancelAnimationFrame(frame);
          frame = 0;
          previous = 0;
          fox.dataset.scenario = 'rust';
        };
        const tick = (now) => {
          frame = 0;
          if (paused || reduced.matches || document.hidden || !visible) { stop(); return; }
          const delta = target - x;
          const step = Math.min((now - (previous || now)) / 1000, 0.05) * 180;
          previous = now;
          if (Math.abs(delta) < 1) { stop(); return; }
          x += Math.sign(delta) * Math.min(Math.abs(delta), step);
          fox.dataset.scenario = 'lopen';
          fox.style.setProperty('--jos-face', delta > 0 ? '-1' : '1');
          fox.style.setProperty('--jos-x', `${x}px`);
          frame = requestAnimationFrame(tick);
        };
        const start = () => {
          fox.classList.toggle('is-paused', paused || reduced.matches);
          if (paused || reduced.matches || document.hidden || !visible) { stop(); return; }
          if (!frame) frame = requestAnimationFrame(tick);
        };
        const follow = (event) => {
          target = Math.max(0, Math.min(limit(), event.clientX - fox.getBoundingClientRect().left - walker.offsetWidth / 2));
          start();
        };
        footer.addEventListener('pointermove', follow, { signal: events.signal });
        footer.addEventListener('pointerdown', (event) => {
          if (!event.target.closest('a, button')) follow(event);
        }, { signal: events.signal });
        foxButton.addEventListener('click', () => {
          target = x < limit() / 2 ? limit() : 0;
          start();
        }, { signal: events.signal });
        const observer = new MutationObserver(start);
        observer.observe(root, { attributes: true, attributeFilter: ['class'] });
        const viewport = new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; start(); });
        viewport.observe(root);
        const resize = new ResizeObserver(() => {
          x = Math.min(x, limit());
          target = Math.min(target, limit());
          fox.style.setProperty('--jos-x', `${x}px`);
        });
        resize.observe(fox);
        document.addEventListener('visibilitychange', start, { signal: events.signal });
        button.addEventListener('click', () => { paused = !paused; update(); }, { signal: events.signal });
        reduced.addEventListener('change', () => { paused = reduced.matches; update(); }, { signal: events.signal });
        instances.set(root, () => {
          events.abort();
          stop();
          observer.disconnect();
          viewport.disconnect();
          resize.disconnect();
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
