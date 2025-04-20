// assets/js/posts.js
document.addEventListener('DOMContentLoaded', () => {
    const list = document.querySelector('.posts-list');
    if (!list) return;
  
    // — PIN/UNPIN
    list.querySelectorAll('.btn-pin').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.dataset.postId;
        const li = document.getElementById(`post-${id}`);
        try {
          const res = await fetch(`/posts/${id}/toggle-pin`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          });
          if (!res.ok) throw res;
          const { pinned } = await res.json();
  
          // toggle li class
          li.classList.toggle('pinned', pinned);
  
          // toggle icon
          let icon = li.querySelector('.pin-icon');
          if (pinned) {
            if (!icon) {
              icon = document.createElement('span');
              icon.className = 'pin-icon';
              icon.title = 'Pinned';
              icon.textContent = '📌';
              li.querySelector('.post-icons').prepend(icon);
            }
            btn.textContent = btn.textContent.replace(/Pin/, 'Unpin');
          } else {
            if (icon) icon.remove();
            btn.textContent = btn.textContent.replace(/Unpin/, 'Pin');
          }
        } catch (err) {
          console.error(err);
          alert('Unable to toggle pin.');
        }
      });
    });
  
    // — MOVE
    list.querySelectorAll('.post-move').forEach(sel => {
      sel.addEventListener('change', async () => {
        const id = sel.dataset.postId;
        const beforeVal = sel.value === 'end' ? null : Number(sel.value);
        const li = document.getElementById(`post-${id}`);
        try {
          const res = await fetch(`/posts/${id}/move`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ before: beforeVal }),
          });
          if (!res.ok) throw res;
          await res.json();
  
          // reorder in DOM
          if (beforeVal === null) {
            list.appendChild(li);
          } else {
            // find the element whose data-position matches beforeVal
            const target = Array.from(list.children)
              .find(el => Number(el.dataset.position) === beforeVal);
            if (target) list.insertBefore(li, target);
          }
  
          // reset and re-number data-position
          Array.from(list.children).forEach((el, idx) => {
            el.dataset.position = idx + 1;
          });
        } catch (err) {
          console.error(err);
          alert('Unable to move post.');
        } finally {
          sel.value = 'end';
        }
      });
    });
  
    // — DELETE
    list.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.dataset.postId;
        if (!confirm('Are you sure you want to delete this post?')) return;
        const li = document.getElementById(`post-${id}`);
        try {
          const res = await fetch(`/posts/${id}/delete`, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          });
          if (!res.ok) throw res;
          await res.json();
          li.remove();
        } catch (err) {
          console.error(err);
          alert('Unable to delete post.');
        }
      });
    });
  });
  