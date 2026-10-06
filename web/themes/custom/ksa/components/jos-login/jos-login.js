/**
 * Jos on the login page.
 * - Mouse: head and pupils follow the cursor (up to 20 degrees up, 11 down).
 * - Name field focused: Jos reads along, following the text caret.
 * - Password field focused: paws over the eyes (head still), but he peeks through his fingers.
 * - Login error: he shakes his head, the tail droops and he looks at the message.
 * The calm idle animation and the tail wagging are pure CSS (jos-login.css).
 */
((Drupal, once) => {
  const instances = new WeakMap();
  const EYES = { x: 186, y: 205 };   // between both eyes, SVG units
  const IDLE_AFTER = 4000;           // ms without mouse movement: look back at the visitor
  const clamp = (v, min, max) => Math.min(max, Math.max(min, v));

  function init(root) {
    const svg = root.querySelector('svg');
    const scope = root.closest('.login-page') || document;
    const nameInput = scope.querySelector('input[name="name"]');
    const passInput = scope.querySelector('input[name="pass"]');
    if (!svg || !nameInput || !passInput) return;

    const events = new AbortController();
    const on = (target, event, handler) => target.addEventListener(event, handler, { signal: events.signal });
    let frameId;
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    let mode = 'mouse';               // mouse | name | pass
    let errorTarget = null;           // element Jos looks at after a failed login
    let lookUntil = 0;
    let mouse = null;
    let lastMove = 0;
    const now = { rot: 0, x: 0, px: 0, py: 0 };
    const goal = { rot: 0, x: 0, px: 0, py: 0 };

    // SVG point -> screen point, so the maths works at any size.
    function eyesOnScreen() {
      const m = svg.getScreenCTM();
      return { x: m.a * EYES.x + m.c * EYES.y + m.e, y: m.b * EYES.x + m.d * EYES.y + m.f, unit: Math.abs(m.a) || 1 };
    }

    // Where the text caret is in an input, in client px (mirror span).
    const mirror = document.createElement('span');
    mirror.setAttribute('aria-hidden', 'true');
    mirror.style.cssText = 'position:absolute;visibility:hidden;white-space:pre;left:-9999px;top:0';
    document.body.append(mirror);
    function caretPoint(input) {
      const cs = getComputedStyle(input);
      const end = input.selectionEnd ?? input.value.length;
      const text = input.value.slice(0, end);
      mirror.style.font = cs.font;
      mirror.style.letterSpacing = cs.letterSpacing;
      mirror.textContent = input.type === 'password' ? '•'.repeat(text.length) : text;
      const r = input.getBoundingClientRect();
      const left = r.left + parseFloat(cs.paddingLeft) + parseFloat(cs.borderLeftWidth);
      const x = clamp(left + mirror.offsetWidth - input.scrollLeft, left, r.right - parseFloat(cs.paddingRight));
      return { x, y: r.top + r.height / 2 };
    }

    // Target point -> pose. Jos faces left: looking up = rotating clockwise.
    function aim(target) {
      if (!target) { Object.assign(goal, { rot: 0, x: 0, px: 0, py: 0 }); return; }
      const e = eyesOnScreen();
      const dx = (target.x - e.x) / e.unit;   // in SVG units
      const dy = (target.y - e.y) / e.unit;
      // Looking up may go further than looking down (the chin would hit the collar).
      goal.rot = clamp(-dy * (dy < 0 ? .07 : .045), -11, 20);
      goal.x = clamp(dx * .012, -6, 3);
      goal.px = clamp(dx * .018, -3.5, 3);
      goal.py = clamp(dy * (dy < 0 ? .025 : .015), -4, 3);
    }

    function update() {
      const moving = mouse && performance.now() - lastMove < IDLE_AFTER;
      const center = (el) => { const r = el.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; };
      // Right after an error he looks at the message first (not while hiding his eyes).
      if (errorTarget && mode !== 'pass' && performance.now() < lookUntil) aim(center(errorTarget));
      else if (mode === 'name') aim(caretPoint(nameInput));
      else if (mode === 'pass') {
        // Hiding: the head keeps still, so the arms stay attached to the paws.
        aim(null);
      }
      else aim(moving ? mouse : null);
      root.classList.toggle('is-idle', mode === 'mouse' && !moving);
    }

    function frame() {
      update();
      const k = reduced.matches ? 1 : .18;   // smoothing
      for (const key in now) now[key] += (goal[key] - now[key]) * k;
      root.style.setProperty('--head-rot', `${now.rot.toFixed(2)}deg`);
      root.style.setProperty('--head-x', `${now.x.toFixed(2)}px`);
      root.style.setProperty('--px', `${now.px.toFixed(2)}px`);
      root.style.setProperty('--py', `${now.py.toFixed(2)}px`);
      frameId = requestAnimationFrame(frame);
    }

    // Peeking: short looks through the fingers, and always one when typing.
    let peekTimer;
    function peek(ms) {
      clearTimeout(peekTimer);
      root.classList.add('is-peeking');
      peekTimer = setTimeout(() => {
        root.classList.remove('is-peeking');
        if (mode === 'pass') peekTimer = setTimeout(() => peek(1400), 1800);
      }, ms);
    }
    function stopPeeking() {
      clearTimeout(peekTimer);
      root.classList.remove('is-peeking');
    }

    function readName() { mode = 'name'; root.classList.add('is-reading'); }
    function hide() {
      mode = 'pass';
      root.classList.add('is-hiding');
      stopPeeking();
      peekTimer = setTimeout(() => peek(1400), 900);  // first peek once the paws are up
    }

    // Errors: Drupal renders them in the messages area (role="alert") and marks the
    // field with .error / aria-invalid. Checked on load and when messages appear later.
    const ERROR_MESSAGE = '[data-drupal-messages] [role="alert"]';
    const ERROR_FIELD = 'input.error, input[aria-invalid="true"]';
    let shakeTimer;
    function oops() {
      const target = scope.querySelector(ERROR_MESSAGE) || scope.querySelector(ERROR_FIELD);
      if (!target || target === errorTarget) return;
      errorTarget = target;
      lookUntil = performance.now() + 2600;
      root.classList.add('is-error', 'is-shaking');
      clearTimeout(shakeTimer);
      shakeTimer = setTimeout(() => root.classList.remove('is-shaking'), 1000);
    }
    // A new attempt: Jos cheers up again.
    function retry() {
      if (!root.classList.contains('is-error')) return;
      root.classList.remove('is-error', 'is-shaking');
      lookUntil = 0;
    }
    const observer = new MutationObserver(oops);
    observer.observe(scope, { childList: true, subtree: true });

    on(window, 'pointermove', (e) => { mouse = { x: e.clientX, y: e.clientY }; lastMove = performance.now(); });
    on(nameInput, 'focus', readName);
    on(nameInput, 'blur', () => { mode = 'mouse'; root.classList.remove('is-reading'); });
    on(passInput, 'focus', hide);
    on(passInput, 'blur', () => { mode = 'mouse'; root.classList.remove('is-hiding'); stopPeeking(); });
    on(passInput, 'input', () => peek(900));
    // Real typing only: browser autofill fires input events too, and Tab should not count.
    const typed = (e) => { if (e.type === 'paste' || (e.key || '').length === 1 || e.key === 'Backspace' || e.key === 'Delete') retry(); };
    for (const input of [nameInput, passInput]) {
      on(input, 'keydown', typed);
      on(input, 'paste', typed);
    }

    // Drupal autofocuses the name field: start in the right mode.
    if (document.activeElement === nameInput) readName();
    if (document.activeElement === passInput) hide();
    oops();

    frameId = requestAnimationFrame(frame);
    return () => {
      events.abort(); observer.disconnect(); cancelAnimationFrame(frameId);
      clearTimeout(peekTimer); clearTimeout(shakeTimer); mirror.remove();
    };
  }

  Drupal.behaviors.ksaJosLogin = {
    attach(context) {
      once('ksa-jos-login', '[data-jos-login]', context).forEach((root) => instances.set(root, init(root)));
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') once.remove('ksa-jos-login', '[data-jos-login]', context).forEach((root) => { instances.get(root)?.(); instances.delete(root); });
    },
  };
})(Drupal, once);
