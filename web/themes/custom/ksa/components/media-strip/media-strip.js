/**
 * Fotostrook as an endless carousel.
 * - Copies the photos until the strip is wider than the screen, then slides by one full set.
 * - Any number of photos (2 or more), always alternating up / down, also across the seam:
 *   with an odd number the loop is two sets long, so up / down keeps alternating.
 * - No pause button (Arne's choice); keyboard focus in the strip pauses it (CSS). Hovering does not.
 * - Reduced motion: no copies, no motion.
 */
((Drupal, once) => {
  const instances = new WeakMap();
  const SPEED = 40; // px per second at desktop size

  function init(root) {
    const rail = root.querySelector('[data-media-strip-rail]');
    const photos = [...rail.children].filter((el) => el.classList.contains('media-strip__photo'));
    if (photos.length < 2) return;

    const setOffsets = () => [...rail.children].forEach((photo, i) => { photo.dataset.offset = String(i % 2); });
    setOffsets();
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    let lastWidth = 0;

    function clear() {
      rail.querySelectorAll('[data-media-strip-copy]').forEach((el) => el.remove());
      root.classList.remove('is-looping');
    }

    function build() {
      clear();
      if (reduced.matches) return;
      const styles = getComputedStyle(rail);
      const gap = parseFloat(styles.columnGap) || 0;
      const scale = parseFloat(getComputedStyle(root).getPropertyValue('--strip-scale')) || 1;
      const setWidth = photos.reduce((sum, photo) => sum + photo.offsetWidth + gap, 0);
      if (!setWidth) return;
      // One loop = one set, or two sets when the number of photos is odd (so up / down keeps alternating).
      const loop = photos.length % 2 ? setWidth * 2 : setWidth;
      // Enough copies to cover the screen while one loop slides out.
      let total = setWidth;
      while (total < root.clientWidth + loop) {
        photos.forEach((photo) => {
          const copy = photo.cloneNode(true);
          copy.dataset.mediaStripCopy = '';
          copy.setAttribute('aria-hidden', 'true');
          copy.querySelectorAll('img').forEach((img) => { img.alt = ''; });
          rail.append(copy);
        });
        total += setWidth;
      }
      setOffsets();
      root.style.setProperty('--strip-loop', `${loop}px`);
      root.style.setProperty('--strip-duration', `${(loop / (SPEED * scale)).toFixed(1)}s`);
      root.classList.add('is-looping');
    }

    reduced.addEventListener('change', build);
    const observer = new ResizeObserver(() => {
      if (Math.abs(root.clientWidth - lastWidth) < 2) return;
      lastWidth = root.clientWidth;
      build();
    });
    observer.observe(root);
    return () => { observer.disconnect(); reduced.removeEventListener('change', build); clear(); };
  }

  Drupal.behaviors.ksaMediaStrip = {
    attach(context) {
      once('ksa-media-strip', '[data-media-strip]', context).forEach((root) => instances.set(root, init(root)));
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') once.remove('ksa-media-strip', '[data-media-strip]', context).forEach((root) => { instances.get(root)?.(); instances.delete(root); });
    },
  };
})(Drupal, once);
