/*  assets/js/posts.js
 *  Everything happens client-side except the real swap APIs:
 *      /posts/{id}/move-up
 *      /posts/{id}/move-down
 *      /posts/{id}/toggle-pin     (does **not** reorder on the server)
 *      /posts/{id}/inline-edit
 *      /posts/{id}/delete
 */

document.addEventListener('DOMContentLoaded', () => {
  const list = document.querySelector('.posts-list');
  if (!list) return;

  /* ---------- Helpers --------------------------------------------------- */

  /** resort UL so that pinned items stay on top, the rest by position ASC */
  function reorderDOM () {
    Array.from(list.querySelectorAll('.post-item'))
      .sort((a, b) => {
        // pinned first
        const aPinned = a.classList.contains('pinned') ? 0 : 1;
        const bPinned = b.classList.contains('pinned') ? 0 : 1;
        if (aPinned !== bPinned) return aPinned - bPinned;
        // then numeric position
        return a.dataset.position - b.dataset.position;
      })
      .forEach(li => list.appendChild(li));
  }

  /** refresh data-position attributes so they are a dense 1…N sequence */
  function renumber () {
    Array.from(list.querySelectorAll('.post-item'))
      .forEach((li, idx) => (li.dataset.position = idx + 1));
  }

  /** tiny helper that always sends X-Requested-With */
  function jsonFetch (url, opts = {}) {
    return fetch(url, {
      ...opts,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        ...(opts.headers || {})
      }
    });
  }

  /* ---------- PIN / UNPIN ---------------------------------------------- */

  list.querySelectorAll('.btn-pin').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.postId;
      const li = document.getElementById(`post-${id}`);

      try {
        const res = await jsonFetch(`/posts/${id}/toggle-pin`, { method: 'POST' });
        if (!res.ok) throw res;
        const { pinned } = await res.json();

        // toggle visuals
        li.classList.toggle('pinned', pinned);
        let icon = li.querySelector('.pin-icon');
        if (pinned && !icon) {
          icon = document.createElement('span');
          icon.className = 'pin-icon';
          icon.textContent = '📌';
          li.querySelector('.post-icons').prepend(icon);
        }
        if (!pinned && icon) icon.remove();
        btn.textContent = pinned ? 'Unpin' : 'Pin';

        // just resort locally – no extra round-trip
        reorderDOM();
      } catch (e) {
        console.error(e);
        alert('Could not toggle pin.');
      }
    });
  });

  /* ---------- MOVE UP / DOWN  ------------------------------------------ */

  function hookMove(selector, endpoint) {
    list.querySelectorAll(selector).forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.dataset.postId;
        try {
          const res = await jsonFetch(`/posts/${id}/${endpoint}`, { method: 'POST' });
          if (!res.ok) throw res;
          const { order } = await res.json();   // [{id, position}, …]

          // update positions from server response
          order.forEach(({ id, position }) => {
            const li = document.getElementById(`post-${id}`);
            if (li) li.dataset.position = position;
          });

          reorderDOM();
        } catch (e) {
          console.error(e);
          alert(`Could not move ${endpoint === 'move-up' ? 'up' : 'down'}.`);
        }
      });
    });
  }

  hookMove('.btn-move-up',   'move-up');
  hookMove('.btn-move-down', 'move-down');

  /* ---------- DELETE ---------------------------------------------------- */

  list.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Delete this post?')) return;
      const id = btn.dataset.postId;
      try {
        const res = await jsonFetch(`/posts/${id}/delete`, { method: 'DELETE' });
        if (!res.ok) throw res;
        document.getElementById(`post-${id}`).remove();
        renumber();
      } catch (e) {
        console.error(e);
        alert('Could not delete.');
      }
    });
  });

  /* ---------- INLINE EDIT ---------------------------------------------- */

  list.querySelectorAll('.btn-edit-inline').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.postId;
      const li = document.getElementById(`post-${id}`);
      if (li.querySelector('.edit-form')) return;   // already open

      const titleEl   = li.querySelector('.post-title');
      const contentEl = li.querySelector('.post-content');

      const form = document.createElement('div');
      form.className = 'edit-form';
      form.innerHTML = `
        <input  class="edit-title"   value="${titleEl.textContent.trim() }">
        <textarea class="edit-body" rows="4">${contentEl.innerHTML.trim()}</textarea>
        <button class="btn-save">Save</button>
        <button class="btn-cancel">Cancel</button>
      `;
      li.querySelector('.post-actions').append(form);
      titleEl.hidden = contentEl.hidden = true;
      btn.disabled = true;

      form.querySelector('.btn-cancel').onclick = () => {
        form.remove();
        titleEl.hidden = contentEl.hidden = false;
        btn.disabled = false;
      };

      form.querySelector('.btn-save').onclick = async () => {
        const payload = {
          title   : form.querySelector('.edit-title').value.trim(),
          content : form.querySelector('.edit-body').value.trim()
        };
        try {
          const res = await jsonFetch(`/posts/${id}/inline-edit`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify(payload)
          });
          if (!res.ok) throw res;
          const data = await res.json();
          titleEl.textContent  = data.title;
          contentEl.innerHTML  = data.content;
          form.remove();
          titleEl.hidden = contentEl.hidden = false;
          btn.disabled = false;
        } catch (e) {
          console.error(e);
          alert('Could not save.');
        }
      };
    });
  });
});
