((Drupal, once) => {
  const instances = new WeakMap();
  Drupal.behaviors.ksaHeader = {
    attach(context) {
      once('ksa-header', '[data-ksa-header]', context).forEach((header) => {
        const events = new AbortController();
        const on = (target, event, handler) => target.addEventListener(event, handler, { signal: events.signal });
        const toggle = header.querySelector('.ksa-header__toggle');
        if (!header.querySelector('nav a')) return;
        const blocks = header.querySelector('.ksa-header__blocks');
        const dialog = header.querySelector('.ksa-mobile-menu');
        const panel = dialog.querySelector('.ksa-mobile-menu__panel');
        const closeButton = dialog.querySelector('.ksa-mobile-menu__close');
        const desktop = window.matchMedia('(min-width: 70rem)');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let closing = false;
        let closeTimer;

        // Dezelfde Drupal-blokken op beide schermgroottes; geen kopieën.
        const finishClose = () => {
          window.clearTimeout(closeTimer);
          if (!dialog.open) return;
          dialog.classList.remove('is-open', 'is-closing');
          header.insertBefore(blocks, dialog);
          dialog.close();
          document.documentElement.classList.remove('ksa-menu-open');
          toggle.setAttribute('aria-expanded', 'false');
          closing = false;
          if (desktop.matches) {
            blocks.querySelector('a')?.focus();
          } else {
            toggle.focus();
          }
        };

        const close = () => {
          if (!dialog.open || closing) return;
          closing = true;
          dialog.classList.add('is-closing');
          dialog.classList.remove('is-open');
          if (reducedMotion.matches) {
            finishClose();
          } else {
            const duration = Number.parseFloat(getComputedStyle(panel).transitionDuration) * 1000;
            closeTimer = window.setTimeout(finishClose, duration + 50);
          }
        };

        toggle.hidden = false;
        header.classList.add('is-enhanced');
        on(toggle, 'click', () => {
          if (desktop.matches || dialog.open) return;
          panel.append(blocks);
          panel.scrollTop = 0;
          document.documentElement.classList.add('ksa-menu-open');
          toggle.setAttribute('aria-expanded', 'true');
          dialog.showModal();
          // Leg de startpositie vast zonder twee frames wachttijd.
          panel.getBoundingClientRect();
          dialog.classList.add('is-open');
        });
        on(closeButton, 'click', close);
        on(dialog, 'cancel', (event) => {
          event.preventDefault();
          close();
        });
        on(dialog, 'keydown', (event) => {
          if (event.key !== 'Tab') return;
          const focusable = [...dialog.querySelectorAll('a[href], button:not([disabled]), [tabindex="0"]')]
            .filter((element) => element.tabIndex >= 0 && element.getClientRects().length
              && getComputedStyle(element).visibility !== 'hidden');
          const first = focusable[0];
          const last = focusable[focusable.length - 1];
          if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
          } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
          }
        });
        on(dialog, 'click', (event) => {
          if (event.target === dialog || event.target.closest('a')) close();
        });
        on(panel, 'transitionend', (event) => {
          if (closing && event.target === panel && event.propertyName === 'transform') finishClose();
        });
        on(desktop, 'change', () => {
          if (desktop.matches && dialog.open) finishClose();
        });
        on(window, 'pageshow', () => {
          if (dialog.open) finishClose();
        });
        instances.set(header, () => {
          events.abort();
          window.clearTimeout(closeTimer);
          if (dialog.open) dialog.close();
          header.insertBefore(blocks, dialog);
          document.documentElement.classList.remove('ksa-menu-open');
          header.classList.remove('is-enhanced');
          toggle.hidden = true;
          toggle.setAttribute('aria-expanded', 'false');
        });
        on(dialog, 'close', () => {
          // Ook herstellen als de browser het dialoogvenster zelf sluit.
          if (blocks.parentElement === panel) {
            header.insertBefore(blocks, dialog);
            document.documentElement.classList.remove('ksa-menu-open');
            toggle.setAttribute('aria-expanded', 'false');
            closing = false;
          }
        });
      });
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') once.remove('ksa-header', '[data-ksa-header]', context).forEach((root) => { instances.get(root)?.(); instances.delete(root); });
    },
  };
})(Drupal, once);
