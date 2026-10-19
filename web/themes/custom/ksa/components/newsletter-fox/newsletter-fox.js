((Drupal, once) => {
  const instances = new WeakMap();
  Drupal.behaviors.newsletterFox = {
    attach(context) {
      once('newsletter-fox', '[data-newsletter-fox]', context).forEach((root) => {
        const events = new AbortController();
        const reduced = matchMedia('(prefers-reduced-motion: reduce)');
        let frame;
        const reset = () => {
          cancelAnimationFrame(frame);
          root.style.removeProperty('--fox-tilt');
          root.style.removeProperty('--fox-eye-x');
          root.style.removeProperty('--fox-eye-y');
        };
        const move = (event) => {
          if (event.pointerType !== 'mouse' || reduced.matches || document.hidden) return;
          const r = root.getBoundingClientRect();
          if (r.bottom < 0 || r.top > innerHeight) return;
          cancelAnimationFrame(frame);
          frame = requestAnimationFrame(() => {
            const m = root.querySelector('svg').getScreenCTM();
            if (!m) return;
            const unit = Math.abs(m.a) || 1;
            const dx = (event.clientX - (m.a * 186 + m.e)) / unit;
            const dy = (event.clientY - (m.d * 205 + m.f)) / unit;
            const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
            root.style.setProperty('--fox-tilt', `${clamp(-dy * .04, -8, 12)}deg`);
            root.style.setProperty('--fox-eye-x', `${clamp(dx * .018, -3.5, 3)}px`);
            root.style.setProperty('--fox-eye-y', `${clamp(dy * .018, -4, 3)}px`);
          });
        };
        window.addEventListener('pointermove', move, { passive: true, signal: events.signal });
        window.addEventListener('blur', reset, { signal: events.signal });
        document.documentElement.addEventListener('pointerleave', reset, { signal: events.signal });
        reduced.addEventListener('change', reset, { signal: events.signal });
        instances.set(root, () => { events.abort(); reset(); });
      });
      once('newsletter-received', '.newsletter-received', context).forEach((notice) => notice.focus({ preventScroll: true }));
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') once.remove('newsletter-fox', '[data-newsletter-fox]', context).forEach((root) => { instances.get(root)?.(); instances.delete(root); });
    },
  };
})(Drupal, once);
