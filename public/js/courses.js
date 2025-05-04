// assets/js/courses.js
console.log('[courses.js] loaded');

document.addEventListener('click', async e => {
  const btn = e.target.closest('button.btn-delete');
  if (!btn) return;

  e.preventDefault();
  if (!confirm('Are you sure you want to delete this course?')) {
    console.log('[courses.js] user cancelled delete');
    return;
  }

  const form = btn.closest('form.delete-course-form');
  const url  = form.action;
  const formData = new FormData(form);

  try {
    const res = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: formData,
    });
    console.log('[courses.js] response status', res.status);

    const text = await res.text();
    console.log('[courses.js] raw response text:', text);

    // try to extract the first {...} block in case Symfony's debug toolbar
    // or stray whitespace got tacked on
    const match = text.match(/^\s*(\{[\s\S]*\})/);
    if (!match) {
      console.error('[courses.js] no JSON object found in response');
      throw new Error('Invalid server response');
    }

    const data = JSON.parse(match[1]);
    console.log('[courses.js] parsed JSON:', data);

    if (!res.ok || data.success !== true) {
      throw new Error(data.error || `HTTP ${res.status}`);
    }

    // success!
    const card = form.closest('.courses-card');
    if (card) {
      console.log('[courses.js] removing card for course', card.dataset.courseId);
      card.remove();
    }
  } catch (err) {
    console.error('[courses.js] delete failed', err);
    alert(`Could not delete course: ${err.message}`);
  }
});