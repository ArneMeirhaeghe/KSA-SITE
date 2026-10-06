/**
 * Steps and tabs for "Tekst met beeld" (Figma Desktop 103:836, Mobile 129:2373).
 * - Blocks in an Inschrijvingsstappen section with style "step" become a stepper (numbers 1..n, Previous / Next).
 *   Jos de vos stands on the current number and hops from number to number.
 * - Blocks in a Praktische tabbladen section with style "tab" become tabs (WAI-ARIA tabs, arrow keys).
 * - The label of a step or tab is the block title. Order = block order in Layout Builder.
 * - Without JavaScript, and in the Layout Builder editor, all blocks stay under each other.
 */
((Drupal, once) => {
  let uid = 0;
  const activeSteppers = new Map();
  const text = (el) => (el?.textContent || '').replace(/\s+/g, ' ').trim();

  // The section style is chosen explicitly in Layout Builder. Moving an
  // unrelated block no longer splits one stepper into accidental groups.
  function groups(region, kind) {
    const style = kind === 'step' ? '.ksa-section--steps' : '.ksa-section--tabs';
    if (!region.closest(style)) return [];
    const blocks = [...region.children].filter((child) => {
      const section = child.querySelector(':scope [data-text-media-group]');
      return section?.dataset.textMediaGroup === kind && section.closest('.text-media-block') === child;
    });
    return blocks.length > 1 ? [blocks] : [];
  }

  function button(className, label) {
    const el = document.createElement('button');
    el.type = 'button';
    el.className = className;
    el.textContent = label;
    return el;
  }


  // Jos (jos.svg) hops along the stepper numbers, one hop per step.
  const JOS_SVG = 'themes/custom/ksa/components/jos/jos.svg';
  const HOP_MS = 420;
  let josMarkup;
  function loadJos() {
    const base = (window.drupalSettings?.path?.baseUrl) || '/';
    josMarkup ??= fetch(base + JOS_SVG)
      .then((r) => (r.ok ? r.text() : ''))
      .then((svg) => svg.replace(/<metadata>[\s\S]*?<\/metadata>/, '').replace('class="ksa-jos__svg"', 'class="steps__jos-svg"'))
      .catch(() => '');
    return josMarkup;
  }

  function stepperJos(list, numbers) {
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const jos = document.createElement('div');
    jos.className = 'steps__jos';
    jos.setAttribute('aria-hidden', 'true');
    jos.innerHTML = '<span class="steps__jos-shadow"></span><span class="steps__jos-body"></span>';
    const body = jos.querySelector('.steps__jos-body');
    list.append(jos);
    loadJos().then((svg) => { if (svg) { body.innerHTML = svg; list.classList.add('has-jos'); } });

    let pos = 0; // number Jos stands on
    let target = 0;
    let running = false;
    const centre = (i) => {
      const n = numbers[i].getBoundingClientRect();
      return n.left + n.width / 2 - list.getBoundingClientRect().left;
    };
    const place = () => { jos.style.setProperty('--jos-x', `${centre(pos)}px`); };

    async function run() {
      if (running) return;
      running = true;
      while (pos !== target) {
        const next = pos + Math.sign(target - pos);
        const from = centre(pos);
        const to = centre(next);
        jos.style.setProperty('--jos-face', to > from ? '-1' : '1'); // jos.svg looks left; mirror to look right
        if (!reduced.matches && jos.isConnected && list.offsetParent) {
          jos.classList.add('is-jumping');
          const x = (t) => `${from + (to - from) * t}px`;
          const hop = jos.animate([
            { transform: `translateX(${x(0)})`, offset: 0 },
            { transform: `translateX(${x(0)})`, offset: 0.12 },
            { transform: `translate(${x(0.5)}, -48px)`, offset: 0.52, easing: 'ease-in' },
            { transform: `translateX(${x(1)})`, offset: 0.88 },
            { transform: `translateX(${x(1)})`, offset: 1 },
          ], { duration: HOP_MS, easing: 'ease-out' });
          // Squash before the jump, stretch in the air, squash on landing.
          body.animate([
            { scale: '1 1' }, { scale: '1.12 .84', offset: 0.12 }, { scale: '.94 1.08', offset: 0.45 },
            { scale: '1 1', offset: 0.8 }, { scale: '1.1 .88', offset: 0.9 }, { scale: '1 1' },
          ], { duration: HOP_MS });
          jos.querySelector('.steps__jos-shadow').animate([
            { scale: '1' }, { scale: '.55', offset: 0.52 }, { scale: '1' },
          ], { duration: HOP_MS });
          try { await hop.finished; } catch (e) { /* cancelled */ }
        }
        pos = next;
        place();
      }
      jos.classList.remove('is-jumping');
      running = false;
    }

    place();
    const observer = new ResizeObserver(place);
    observer.observe(list);
    activeSteppers.set(list, () => {
      observer.disconnect(); target = pos;
      list.getAnimations({ subtree: true }).forEach((animation) => animation.cancel());
    });
    return (index) => { target = index; run(); };
  }

  function initSteps(blocks) {
    const id = `ksa-steps-${uid += 1}`;
    const headings = blocks.map((b) => b.querySelector('.text-media__heading'));
    const nav = document.createElement('nav');
    nav.className = 'steps';
    nav.setAttribute('aria-label', Drupal.t('Stappen om in te schrijven'));
    const list = document.createElement('ol');
    list.className = 'steps__list';
    const items = blocks.map((block, i) => {
      const li = document.createElement('li');
      const b = button('steps__item', '');
      b.setAttribute('aria-controls', `${id}-${i}`);
      b.innerHTML = `<span class="steps__number">${i + 1}</span><span class="steps__label"></span>`;
      b.querySelector('.steps__label').textContent = text(headings[i]);
      li.append(b);
      list.append(li);
      return b;
    });
    nav.append(list);
    blocks[0].before(nav);
    const moveJos = stepperJos(list, items.map((b) => b.querySelector('.steps__number')));

    blocks.forEach((block, i) => {
      block.id = `${id}-${i}`;
      block.classList.add('is-step');
      headings[i].tabIndex = -1;
      // "Stap 3" above the title (visible on small screens, where the numbers are hidden).
      const label = document.createElement('p');
      label.className = 'text-media__step-label';
      label.textContent = Drupal.t('Stap @n van @total', { '@n': i + 1, '@total': blocks.length });
      headings[i].before(label);
      // Previous / next.
      const pager = document.createElement('div');
      pager.className = 'text-media__steps-nav';
      if (i > 0) {
        const prev = button('text-media__step-button text-media__step-button--prev', Drupal.t('Vorige stap'));
        prev.addEventListener('click', () => show(i - 1, true));
        pager.append(prev);
      }
      // The block's own link (e.g. "Naar Ravot") goes in the same row: previous, link, next.
      const action = block.querySelector('.text-media__action');
      if (action) {
        const link = action.querySelector('a');
        if (link) {
          link.classList.add('text-media__step-link');
          pager.append(link);
        }
        action.remove();
      }
      if (i < blocks.length - 1) {
        const next = button('text-media__step-button text-media__step-button--next', Drupal.t('Volgende stap'));
        next.addEventListener('click', () => show(i + 1, true));
        pager.append(next);
      }
      block.querySelector('.text-media__content').append(pager);
    });

    function show(index, moveFocus) {
      blocks.forEach((block, i) => { block.hidden = i !== index; });
      items.forEach((b, i) => {
        if (i === index) b.setAttribute('aria-current', 'step');
        else b.removeAttribute('aria-current');
        b.classList.toggle('is-done', i < index);
      });
      moveJos(index);
      if (moveFocus) headings[index].focus({ preventScroll: true });
    }
    items.forEach((b, i) => b.addEventListener('click', () => show(i, false)));
    show(0, false);
  }

  function initTabs(blocks) {
    const id = `ksa-tabs-${uid += 1}`;
    const list = document.createElement('div');
    list.className = 'tabs';
    list.setAttribute('role', 'tablist');
    list.setAttribute('aria-label', Drupal.t('Praktische informatie'));
    const tabs = blocks.map((block, i) => {
      const tab = button('tabs__tab', text(block.querySelector('.text-media__heading')));
      tab.id = `${id}-tab-${i}`;
      tab.setAttribute('role', 'tab');
      tab.setAttribute('aria-controls', `${id}-panel-${i}`);
      block.id = `${id}-panel-${i}`;
      block.classList.add('is-tab-panel');
      block.setAttribute('role', 'tabpanel');
      block.setAttribute('aria-labelledby', tab.id);
      block.tabIndex = 0;
      list.append(tab);
      return tab;
    });
    blocks[0].before(list);

    function select(index, moveFocus) {
      tabs.forEach((tab, i) => {
        const active = i === index;
        tab.setAttribute('aria-selected', String(active));
        tab.tabIndex = active ? 0 : -1;
        blocks[i].hidden = !active;
      });
      // Small screens: the tab row scrolls sideways; keep the active tab in view.
      const tab = tabs[index];
      if (tab.offsetLeft < list.scrollLeft || tab.offsetLeft + tab.offsetWidth > list.scrollLeft + list.clientWidth) {
        list.scrollTo({ left: tab.offsetLeft - 16, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
      }
      if (moveFocus) tab.focus({ preventScroll: true });
    }
    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => select(i, false));
      tab.addEventListener('keydown', (e) => {
        const keys = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
        if (!(e.key in keys)) return;
        e.preventDefault();
        select((keys[e.key] + tabs.length) % tabs.length, true);
      });
    });
    select(0, false);
  }

  Drupal.behaviors.ksaTextMediaGroups = {
    attach(context) {
      // Editors see every block in the Layout Builder editor.
      if (document.getElementById('layout-builder')) return;
      const regions = new Set();
      once('ksa-text-media-group', '[data-text-media-group]', context).forEach((section) => {
        const block = section.closest('.text-media-block');
        if (block?.parentElement) regions.add(block.parentElement);
      });
      regions.forEach((region) => {
        groups(region, 'step').forEach(initSteps);
        groups(region, 'tab').forEach(initTabs);
      });
    },
    detach(context, settings, trigger) {
      if (trigger !== 'unload') return;
      activeSteppers.forEach((destroy, list) => {
        if (context === list || context.contains(list)) { destroy(); activeSteppers.delete(list); }
      });
    },
  };
})(Drupal, once);
