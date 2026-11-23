/**
 * The Google month calendar loads only when the visitor asks for it:
 * no Google scripts or cookies before that click.
 */
((Drupal, once) => {
  Drupal.behaviors.ksaAgendaCalendar = {
    attach(context) {
      once('ksa-agenda-calendar', '[data-agenda-calendar-load]', context).forEach((link) => {
        link.addEventListener('click', (event) => {
          // Cmd, Ctrl or Shift + click still opens the calendar in a new tab.
          if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
          event.preventDefault();
          const frame = document.createElement('iframe');
          frame.src = link.href;
          frame.title = link.dataset.calendarTitle;
          link.closest('.agenda__calendar-placeholder').replaceWith(frame);
          frame.focus();
        });
      });
    },
  };
})(Drupal, once);
