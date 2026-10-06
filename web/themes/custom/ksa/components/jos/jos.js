/**
 * Jos de vos: de regisseur.
 * - Kiest volgens het uur van de bezoeker wat Jos doet (PERIODS).
 * - Wandelt tussen links en rechts; props bepalen soms de kant (SCENES.side).
 * - Klik op Jos: meteen het volgende scenario.
 * - Pauzeknop van de hero, verborgen tabblad en minder-bewegen worden gerespecteerd.
 * De tekening staat in jos.svg, alle beweging in jos.css.
 */
((Drupal, once) => {
  const instances = new WeakMap();

  // hold = hoe lang (ms) Jos iets doet. side = waar de props passen.
  // face: -1 = Jos kijkt naar rechts (gespiegeld).
  // mobile: false = niet op gsm (daar staat Jos vast rechts in de hoek).
  const SCENES = {
    rust: { hold: 8000 },
    zwaaien: { hold: 7000 },
    dansen: { hold: 8400 },
    springen: { hold: 7000 },
    kampvuur: { hold: 10000, side: 'left', face: -1 }, // vuur tussen Jos en de foto's
    vlag: { hold: 12000, side: 'left' },
    tent: { hold: 8000, side: 'left', mobile: false },
    slapen: { hold: 15000, side: 'right' }, // in zijn bedje, voor het clubhuis
  };

  // Dagschema, in het uur van de bezoeker. De nacht loopt over middernacht.
  const PERIODS = [
    { name: 'ochtend', from: 7, to: 11, pool: ['vlag', 'zwaaien', 'rust', 'springen'] },
    { name: 'middag', from: 11, to: 17, pool: ['tent', 'springen', 'dansen', 'zwaaien'] },
    { name: 'avond', from: 17, to: 22, pool: ['kampvuur', 'dansen', 'rust'] },
    { name: 'nacht', from: 22, to: 7, pool: ['slapen'] },
  ];

  const WALK_SPEED = 90; // px per second
  const SWITCH_SIDE_AFTER = 2; // after this many scenarios on one side, walk to the other

  const inPeriod = (period, hour) => (period.from < period.to
    ? hour >= period.from && hour < period.to
    : hour >= period.from || hour < period.to);

  function init(root) {
    const hero = root.closest('.ksa-hero');
    const walker = root.querySelector('.ksa-jos__walker');
    const button = root.querySelector('[data-jos-next]');
    const svg = root.querySelector('.ksa-jos__svg');
    if (!walker || !button || !svg) return () => {};

    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const mobile = matchMedia('(width < 700px)');
    const events = new AbortController();
    const on = (target, type, handler) => target.addEventListener(type, handler, { signal: events.signal });

    let side = 'left';
    let current = '';
    let playsHere = 0;
    let timer;
    let due = 0; // when the current scenario ends (performance.now)
    let remaining = 0; // left-over time while paused
    let walking = null; // { target, then }
    let paused = false;

    // Demo override: data-jos-time="ochtend|middag|avond|nacht" on Jos or the hero.
    const period = () => {
      const forced = root.dataset.josTime || hero?.dataset.josTime;
      const hour = new Date().getHours();
      return PERIODS.find((p) => p.name === forced) || PERIODS.find((p) => inPeriod(p, hour));
    };
    const allowed = (name) => !mobile.matches || SCENES[name].mobile !== false;
    const spot = (name) => {
      const value = getComputedStyle(root).getPropertyValue(`--jos-spot-${name}`).trim();
      const width = root.clientWidth;
      const x = value.endsWith('%') ? (parseFloat(value) / 100) * width : parseFloat(value) || 0;
      return Math.max(0, Math.min(width - walker.offsetWidth, x));
    };
    const currentX = () => new DOMMatrixReadOnly(getComputedStyle(walker).transform).m41;
    const setX = (x, seconds = 0) => {
      root.style.setProperty('--jos-walk-time', `${seconds}s`);
      root.style.setProperty('--jos-x', `${Math.round(x)}px`);
    };

    function schedule(ms) {
      clearTimeout(timer);
      due = performance.now() + ms;
      if (!paused && !reduced.matches) timer = setTimeout(next, ms);
    }

    function play(name) {
      const again = name === current; // e.g. sleeping all night: no bounce in bed
      current = name;
      root.dataset.scenario = name;
      root.style.setProperty('--jos-face', String(SCENES[name].face || 1));
      playsHere += 1;
      if (!reduced.matches && !again) {
        // Small "boing" whenever Jos starts something new.
        svg.animate([
          { transform: 'scale(1.05, .93)' },
          { transform: 'scale(.97, 1.04)', offset: 0.4 },
          { transform: 'scale(1.01, .99)', offset: 0.7 },
          { transform: 'none' },
        ], { duration: 500, easing: 'ease-out' });
      }
      schedule(SCENES[name].hold);
    }

    function walkTo(target, then) {
      const from = currentX();
      const to = spot(target);
      const seconds = Math.abs(to - from) / WALK_SPEED;
      walking = { target, then };
      side = target;
      playsHere = 0;
      current = 'lopen';
      root.dataset.scenario = 'lopen';
      root.style.setProperty('--jos-face', to > from ? '-1' : '1');
      setX(to, seconds);
      clearTimeout(timer);
      due = performance.now() + seconds * 1000;
      if (!paused) timer = setTimeout(arrive, seconds * 1000 + 60);
    }

    function arrive() {
      const done = walking;
      walking = null;
      setX(spot(side));
      done?.then();
    }

    function pick() {
      let pool = period().pool.filter(allowed);
      if (pool.length > 1) pool = pool.filter((name) => name !== current);
      return pool[Math.floor(Math.random() * pool.length)] || 'rust';
    }

    function next() {
      if (walking) return;
      const name = pick();
      let target = side;
      if (mobile.matches) target = 'right';
      else if (SCENES[name].side) target = SCENES[name].side;
      else if (playsHere >= SWITCH_SIDE_AFTER) target = side === 'left' ? 'right' : 'left';

      if (target !== side && !mobile.matches && !reduced.matches) {
        walkTo(target, () => play(name));
      }
      else {
        side = target;
        setX(spot(side));
        play(name);
      }
    }

    function pause() {
      if (paused) return;
      paused = true;
      root.classList.add('is-paused');
      clearTimeout(timer);
      remaining = Math.max(0, due - performance.now());
      if (walking) setX(currentX()); // freeze mid-walk
    }

    function resume() {
      if (!paused) return;
      paused = false;
      root.classList.remove('is-paused');
      if (reduced.matches) return;
      if (walking) {
        const { target, then } = walking;
        walking = null;
        walkTo(target, then);
      }
      else {
        schedule(remaining || 1000);
      }
    }

    const heroPaused = () => !!hero?.classList.contains('is-paused');
    const sync = () => (heroPaused() || document.hidden ? pause() : resume());

    function start() {
      clearTimeout(timer);
      walking = null;
      const name = pick();
      side = mobile.matches ? 'right' : (SCENES[name].side || 'left');
      playsHere = 0;
      setX(spot(side));
      play(name);
      sync();
    }

    on(button, 'click', () => {
      if (walking) return;
      if (paused && !reduced.matches) return; // paused means: nothing moves
      next();
    });
    on(document, 'visibilitychange', sync);
    on(reduced, 'change', start);
    on(mobile, 'change', start);

    // The hero pause button toggles .is-paused on the hero (see hero.js).
    const heroObserver = new MutationObserver(sync);
    if (hero) heroObserver.observe(hero, { attributes: true, attributeFilter: ['class'] });

    // Keep Jos on his spot when the hero changes size.
    const resize = new ResizeObserver(() => {
      if (walking) {
        const { target, then } = walking;
        walking = null;
        setX(currentX());
        if (!paused) walkTo(target, then);
        else walking = { target, then };
      }
      else setX(spot(side));
    });
    resize.observe(root);

    start();

    return () => {
      events.abort();
      heroObserver.disconnect();
      resize.disconnect();
      clearTimeout(timer);
      root.classList.remove('is-paused');
      root.dataset.scenario = 'rust';
      ['--jos-x', '--jos-face', '--jos-walk-time'].forEach((prop) => root.style.removeProperty(prop));
    };
  }

  if (Drupal && once) {
    Drupal.behaviors.ksaJos = {
      attach(context) {
        once('ksa-jos', '[data-ksa-jos]', context).forEach((root) => instances.set(root, init(root)));
      },
      detach(context, settings, trigger) {
        if (trigger === 'unload') {
          once.remove('ksa-jos', '[data-ksa-jos]', context).forEach((root) => {
            instances.get(root)?.();
            instances.delete(root);
          });
        }
      },
    };
  }
  else {
    document.querySelectorAll('[data-ksa-jos]').forEach(init);
  }
})(window.Drupal, window.once);
