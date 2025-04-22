/**
 * Client-side helper for “Admin Alerts”
 * – displays / hides per-post flags
 * – POSTs acknowledgement → /alerts/{id}/ack
 *
 * Works out-of-the-box with Twig markup produced in course.html.twig
 * and the <span class="alert-badge"> on the course list.
 */

document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  
    /* ─── Dismiss single alert (per post) ─── */
    document.body.addEventListener('click', async e => {
      const btn = e.target.closest('.btn-dismiss-alert');
      if (!btn) return;
  
      const alertId   = btn.dataset.alertId;
      const courseId  = btn.dataset.courseId;
      const badge     = document.querySelector(`#badge-${courseId}`);
  
      try {
        await fetch(`/alerts/${alertId}/ack`, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type':     'application/json',
            'X-CSRF-Token':     csrf
          },
          body: JSON.stringify({})
        });
  
        /* 1) remove flag + button on that post */
        const wrapper = btn.closest('.admin-alert-wrapper');
        wrapper?.remove();
  
        /* 2) update course badge count if present */
        if (badge) {
          const n = parseInt(badge.textContent, 10) - 1;
          if (n > 0) badge.textContent = n;
          else       badge.remove();
        }
  
        /* 3) hide banner if no more flags on page */
        if (!document.querySelector('.admin-alert-flag')) {
          document.querySelector('.admin-alert-banner')?.remove();
        }
  
      } catch (err) {
        console.error('Could not acknowledge alert', err);
      }
    });
  });
  