// Alleen demo-inhoud. Alle beweging staat in het echte hero-component.
(() => {
  const names = ['leeuwkes-astene', 'knim', 'joro-deinze', 'sjo', 'plus16', 'dolfijntjes', 'leeuwkes-deinze', 'joro-astene'];
  const select = document.querySelector('#count');
  const count = Math.min(8, Math.max(1, Number(new URLSearchParams(location.search).get('count')) || 4));
  select.value = String(count);
  const rail = document.querySelector('.ksa-hero__rail');
  const sample = rail.firstElementChild.cloneNode(true);
  rail.replaceChildren(...names.slice(0, count).map((name) => {
    const card = sample.cloneNode(true);
    const image = card.querySelector('img');
    image.src = `../../sites/default/files/ploegen/${name}.jpg`;
    image.alt = `Groepsfoto ${name.replaceAll('-', ' ')}`;
    return card;
  }));
  select.addEventListener('change', () => select.form.requestSubmit());

  // Jos: dezelfde jos.svg en jos.js als in Drupal. De tijdkeuze is alleen voor de demo.
  const hero = document.querySelector('[data-ksa-hero]');
  const time = document.querySelector('#jos-time');
  time.value = new URLSearchParams(location.search).get('tijd') || '';
  hero.dataset.josTime = time.value;
  time.addEventListener('change', () => time.form.requestSubmit());
  fetch('../../themes/custom/ksa/components/jos/jos.svg')
    .then((response) => response.text())
    .then((svg) => {
      document.querySelector('[data-jos-next]').insertAdjacentHTML('beforeend', svg);
      const script = document.createElement('script');
      script.src = '../../themes/custom/ksa/components/jos/jos.js';
      document.body.append(script);
    });
  let windTimer;
  document.querySelector('#wind').addEventListener('click', () => {
    clearTimeout(windTimer);
    document.querySelector('[data-ksa-hero]').classList.add('is-windy');
    windTimer = setTimeout(() => document.querySelector('[data-ksa-hero]').classList.remove('is-windy'), 3000);
  });
})();
