// assets/js/courses.js
console.log('[courses.js] loaded');

document.addEventListener('submit', async e => {
  // only intercept our delete forms
  if (!e.target.matches('.delete-course-form')) return;
  e.preventDefault();

  console.log('[courses.js] delete form submitted', e.target);

  if (!confirm('Are you sure you want to delete this course?')) {
    console.log('[courses.js] user cancelled delete');
    return;
  }

  try {
    const form = e.target;
    const url  = form.action;
    const res  = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form),
    });

    console.log('[courses.js] response status', res.status);

    let data;
    try {
      data = await res.json();
      console.log('[courses.js] JSON payload', data);
    } catch (jsonErr) {
      console.error('[courses.js] failed to parse JSON', jsonErr);
      throw new Error('Invalid server response');
    }

    if (!res.ok || data.success !== true) {
      const msg = data.error || `HTTP ${res.status}`;
      console.error('[courses.js] delete failed:', msg);
      return alert(`Could not delete course: ${msg}`);
    }

    // success!
    const card = form.closest('.courses-card');
    if (card) {
      console.log('[courses.js] removing card for course', card.dataset.courseId);
      card.remove();
    }
  } catch (err) {
    console.error('[courses.js] fetch error', err);
    alert(`Could not delete course: ${err.message}`);
  }
});
