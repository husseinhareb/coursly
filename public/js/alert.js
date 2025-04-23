document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  
    document.body.addEventListener('click', async e => {
      const btn = e.target.closest('.btn-dismiss-alert');
      if (!btn) return;
  
      const alertId  = btn.dataset.alertId;
      const courseId = btn.dataset.courseId;
      const badge    = document.querySelector(`#badge-${courseId}`);
  
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
  
        // 1) remove the per-post alert tag
        const tag = btn.closest('.admin-alert-tag');
        tag?.remove();
  
        // 2) update the course badge count, if present
        if (badge) {
          const n = parseInt(badge.textContent, 10) - 1;
          if (n > 0) badge.textContent = n;
          else       badge.remove();
        }
  
        // 3) hide the top banner if no more alert tags remain
        if (!document.querySelector('.admin-alert-tag')) {
          document.querySelector('.alert-banner')?.remove();
        }
  
      } catch (err) {
        console.error('Could not acknowledge alert', err);
      }
    });
  });
  