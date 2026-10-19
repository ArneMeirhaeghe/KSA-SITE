/** A small CKEditor using Drupal's bundled assets, scoped to the guarded form. */
((Drupal, once, CKEditor5) => {
  Drupal.behaviors.newsletterEditor = {
    attach(context) {
      once('newsletter-editor', '[data-newsletter-editor]', context).forEach((textarea) => {
        const mode = textarea.form.elements.body_is_html;
        const initial = document.createElement('div');
        initial.textContent = textarea.value;
        CKEditor5.editorClassic.ClassicEditor.create(textarea, {
          licenseKey: 'GPL',
          plugins: [CKEditor5.essentials.Essentials, CKEditor5.paragraph.Paragraph, CKEditor5.basicStyles.Bold, CKEditor5.basicStyles.Italic, CKEditor5.heading.Heading, CKEditor5.list.List, CKEditor5.link.Link, CKEditor5.removeFormat.RemoveFormat],
          toolbar: { items: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo', 'removeFormat'], shouldNotGroupWhenFull: true },
          heading: { options: [
            { model: 'paragraph', title: 'Gewone tekst', class: 'ck-heading_paragraph' },
            { model: 'heading2', view: 'h2', title: 'Tussenkop', class: 'ck-heading_heading2' },
          ] },
          link: { defaultProtocol: 'https://', allowedProtocols: ['http', 'https', 'mailto'] },
          language: 'nl',
          initialData: mode.value === '1' ? textarea.value : initial.innerHTML.replace(/\n/g, '<br>'),
        }).then((editor) => {
          // The hidden source cannot receive browser required-field focus.
          // Server validation still rejects empty or oversized contributions.
          textarea.required = false;
          mode.value = '1';
          const editable = editor.ui.view.editable.element;
          editable.setAttribute('aria-label', 'Je tekst');
          editable.setAttribute('aria-required', 'true');
          editable.setAttribute('aria-describedby', textarea.getAttribute('aria-describedby') || '');
          const sync = () => { textarea.value = editor.getData(); };
          editor.model.document.on('change:data', sync);
          textarea.form.addEventListener('submit', sync);
          textarea.form.querySelector(`label[for="${textarea.id}"]`)?.addEventListener('click', () => editor.editing.view.focus());
          sync();
        }).catch(() => {
          // Keep the ordinary textarea usable if the editor cannot load.
          mode.value = '0';
        });
      });
    },
  };
})(Drupal, once, CKEditor5);
