document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  document.body.addEventListener('click', async e => {
    const btn = e.target.closest('.btn-dismiss-alert');
    if (!btn) return;

    const alertId  = btn.dataset.alertId;
    const courseId = btn.dataset.courseId;

    try {
      await fetch(`/alerts/${alertId}/ack`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type':     'application/json',
          'X-CSRF-Token':     csrf
        },
        body: '{}'
      });

      // 1) Remove the alert from the list
      btn.closest('.alert-item').remove();

      // 2) If no more alerts, remove the banner entirely
      if (!document.querySelector('.alert-item')) {
        document.querySelector('.alert-banner').remove();
      }

      // 3) Also update the little badge on the course card, if present:
      const badge = document.querySelector(`#badge-${courseId}`);
      if (badge) {
        let n = parseInt(badge.textContent, 10) - 1;
        if (n > 0) badge.textContent = n;
        else       badge.remove();
      }

    } catch (err) {
      console.error('Could not acknowledge alert', err);
    }
  });
});
