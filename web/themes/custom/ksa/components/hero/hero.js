/** Dezelfde waslijn voor Drupal en de losse componentpreview. */
((Drupal, once) => {
  const instances = new WeakMap();
  const translate = (text, args = {}) => Drupal ? Drupal.t(text, args) : Object.entries(args).reduce((value, [key, replacement]) => value.replace(key, replacement), text);

  function init(root) {
    const stage = root.querySelector('.ksa-hero__stage');
    const rail = root.querySelector('.ksa-hero__rail');
    const viewport = root.querySelector('.ksa-hero__window');
    const originals = [...rail.children];
    const count = originals.length;
    if (!count) return () => {};
    const controls = root.querySelector('.ksa-hero__controls');
    const play = root.querySelector('[data-hero-play]');
    const label = root.querySelector('[data-hero-label]');
    const ring = root.querySelector('[data-hero-progress]');
    const value = root.querySelector('[data-hero-countdown]');
    const status = root.querySelector('[data-hero-status]');
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const events = new AbortController();
    const on = (target, type, handler) => target.addEventListener(type, handler, { signal: events.signal });
    const interval = 5000;
    let index = count, visible = 1, step = 0, elapsed = 0, lastFrame = 0;
    let paused = reduced.matches, hovered = false, focused = false, moving = false;
    let frame, finishTimer, pointerStart;

    // Alleen de weergave wordt herhaald; Drupal blijft de bron van elke foto.
    const clone = (card) => {
      const copy = card.cloneNode(true);
      copy.querySelectorAll('[id]').forEach((element) => element.removeAttribute('id'));
      copy.removeAttribute('id');
      copy.querySelectorAll('.contextual').forEach((element) => element.remove());
      copy.querySelectorAll('[data-contextual-id]').forEach((element) => element.removeAttribute('data-contextual-id'));
      copy.removeAttribute('data-contextual-id');
      return copy;
    };
    originals.forEach((card, i) => card.style.setProperty('--tilt', `${[-7, 5, -4, 8][i % 4]}deg`));
    rail.prepend(...originals.map(clone));
    rail.append(...originals.map(clone));
    root.classList.add('is-enhanced');
    // Met Jos in de hero is de pauzeknop ook bij één foto nodig: Jos beweegt.
    controls.hidden = count < 2 && !root.querySelector('[data-ksa-jos]');

    function clock() {
      // Jos (components/jos) volgt deze klasse om mee te pauzeren.
      root.classList.toggle('is-paused', paused);
      ring.style.strokeDashoffset = String(100 * Math.min(1, elapsed / interval));
      value.textContent = String(Math.max(1, Math.ceil((interval - elapsed) / 1000)));
      label.textContent = paused ? translate('Afspelen') : hovered || focused ? translate('Gepauzeerd') : translate('Pauzeren');
      play.setAttribute('aria-label', paused ? translate('Automatisch doorschuiven starten') : translate('Automatisch doorschuiven pauzeren'));
    }
    function schedule() {
      cancelAnimationFrame(frame);
      clock();
      if (count > 1 && !paused && !hovered && !focused && !document.hidden) {
        lastFrame = performance.now();
        frame = requestAnimationFrame(tick);
      }
    }
    function tick(now) {
      elapsed += now - lastFrame;
      lastFrame = now;
      if (elapsed >= interval && !moving) { slide(1); return; }
      clock();
      frame = requestAnimationFrame(tick);
    }
    function position() {
      rail.style.transform = `translate3d(${-index * step}px, 0, 0)`;
      [...rail.children].forEach((card, i) => {
        const shown = i >= index && i < index + visible;
        card.inert = !shown;
        card.setAttribute('aria-hidden', String(!shown));
        card.querySelector('.ksa-hero__photo').tabIndex = shown ? 0 : -1;
      });
    }
    function normalize() {
      // Behoud toetsenbordfocus wanneer een herhaalde kaart wordt teruggezet.
      const activeCard = document.activeElement?.closest('.ksa-hero__hanger');
      const offset = activeCard ? [...rail.children].indexOf(activeCard) - index : -1;
      index = count + ((index % count) + count) % count;
      position();
      if (offset >= 0 && offset < visible) rail.children[index + offset].querySelector('.ksa-hero__photo').focus({ preventScroll: true });
    }
    function finish() {
      clearTimeout(finishTimer);
      rail.classList.remove('is-moving');
      moving = false;
      normalize();
      schedule();
    }
    function slide(direction, manual = false) {
      if (moving || count < 2) return;
      moving = true;
      elapsed = 0;
      // Leg de startpositie vast vóór de CSS-overgang.
      rail.getBoundingClientRect();
      rail.classList.add('is-moving');
      index += direction;
      position();
      if (manual) status.textContent = translate('Foto @number van @count', { '@number': ((index % count) + count) % count + 1, '@count': count });
      if (reduced.matches) finish();
      else finishTimer = setTimeout(finish, 900);
      schedule();
    }
    function layout() {
      clearTimeout(finishTimer);
      moving = false;
      rail.classList.remove('is-moving');
      const style = getComputedStyle(stage);
      const width = parseFloat(style.getPropertyValue('--card-width'));
      const gap = parseFloat(style.getPropertyValue('--gap'));
      const edge = parseFloat(style.getPropertyValue('--edge-space'));
      step = width + gap;
      visible = Math.max(1, Math.min(count, 4, Math.floor((stage.clientWidth - 2 * edge + gap) / step)));
      stage.style.setProperty('--window-width', `${visible * step - gap + 2 * edge}px`);
      normalize();
      schedule();
    }
    on(rail, 'transitionend', (event) => { if (event.target === rail && event.propertyName === 'transform' && moving) finish(); });
    on(play, 'click', () => { paused = !paused; schedule(); });
    on(stage, 'pointerover', (event) => {
      if (event.pointerType === 'mouse' && event.target.closest('.ksa-hero__photo')) { hovered = true; schedule(); }
    });
    on(stage, 'pointerout', (event) => {
      hovered = !!event.relatedTarget?.closest?.('.ksa-hero__photo') && stage.contains(event.relatedTarget);
      schedule();
    });
    on(stage, 'focusin', () => { focused = true; schedule(); });
    on(stage, 'focusout', (event) => { focused = stage.contains(event.relatedTarget); schedule(); });
    on(viewport, 'pointerdown', (event) => {
      if (event.pointerType === 'mouse') return;
      pointerStart = { x: event.clientX, y: event.clientY };
      viewport.setPointerCapture(event.pointerId);
    });
    on(viewport, 'pointerup', (event) => {
      if (pointerStart) {
        const dx = event.clientX - pointerStart.x;
        const dy = event.clientY - pointerStart.y;
        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) slide(dx < 0 ? 1 : -1, true);
      }
      pointerStart = undefined;
    });
    on(viewport, 'pointercancel', () => { pointerStart = undefined; });
    on(document, 'visibilitychange', schedule);
    on(reduced, 'change', () => { paused = reduced.matches; finish(); });
    const observer = new ResizeObserver(layout);
    observer.observe(stage);
    layout();
    return () => {
      events.abort();
      observer.disconnect();
      cancelAnimationFrame(frame);
      clearTimeout(finishTimer);
      rail.replaceChildren(...originals);
      originals.forEach((card) => { card.inert = false; card.removeAttribute('aria-hidden'); card.querySelector('.ksa-hero__photo').removeAttribute('tabindex'); });
      rail.classList.remove('is-moving');
      rail.style.removeProperty('transform');
      root.classList.remove('is-enhanced');
      controls.hidden = true;
    };
  }
  if (Drupal && once) {
    Drupal.behaviors.ksaHero = {
      attach(context) { once('ksa-hero', '[data-ksa-hero]', context).forEach((root) => instances.set(root, init(root))); },
      detach(context, settings, trigger) {
        if (trigger === 'unload') once.remove('ksa-hero', '[data-ksa-hero]', context).forEach((root) => { instances.get(root)?.(); instances.delete(root); });
      },
    };
  } else {
    document.querySelectorAll('[data-ksa-hero]').forEach(init);
  }
})(window.Drupal, window.once);
