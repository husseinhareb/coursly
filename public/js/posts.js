/*  assets/js/posts.js
 *  Client side helpers around the real APIs :
 *      /posts/{id}/move-up
 *      /posts/{id}/move-down
 *      /posts/{id}/toggle-pin
 *      /posts/{id}/inline-edit
 *      /posts/{id}/delete
 */

document.addEventListener('DOMContentLoaded', () => {
  const list = document.querySelector('.posts-list');
  if (!list) return;

  /* ─────────────────── Helpers ─────────────────────────────────────── */

  /** resort UL so that pinned items stay on top, the rest by position ASC */
  function reorderDOM () {
    Array.from(list.querySelectorAll('.post-item'))
      .sort((a, b) => {
        const aPinned = a.classList.contains('pinned') ? 0 : 1;
        const bPinned = b.classList.contains('pinned') ? 0 : 1;
        if (aPinned !== bPinned) return aPinned - bPinned;
        return (+a.dataset.position) - (+b.dataset.position);
      })
      .forEach(li => list.appendChild(li));
  }

  /** refresh data-position attributes so they remain a dense 1…N sequence */
  function renumberPositions () {
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

  /** build and return a “move” button ; caller must attach listener */
  function createMoveButton (direction, postId) {
    const btn = document.createElement('button');
    btn.className   = `btn-move-${direction}`;
    btn.dataset.postId = postId;
    btn.title       = direction === 'up' ? 'Move up' : 'Move down';
    btn.innerHTML   = `<i class="fa-solid fa-arrow-${direction}"></i>`;
    return btn;
  }

  /* ─────────────────── Pin / Un-pin ────────────────────────────────── */

  list.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-pin');
    if (!btn) return;

    const id      = btn.dataset.postId;
    const li      = document.getElementById(`post-${id}`);
    const icon    = btn.querySelector('i');

    try {
      const res = await jsonFetch(`/posts/${id}/toggle-pin`, { method: 'POST' });
      if (!res.ok) throw res;
      const { pinned } = await res.json();

      /* 1.  Visuel général du <li> */
      li.classList.toggle('pinned', pinned);

      /* 2.  Petit badge 📌 à côté du titre */
      let badge = li.querySelector('.pin-icon');
      if (pinned && !badge) {
        badge           = document.createElement('span');
        badge.className = 'pin-icon';
        badge.innerHTML = '<i class="fa-solid fa-thumbtack"></i>';
        li.querySelector('.post-icons').prepend(badge);
      } else if (!pinned && badge) {
        badge.remove();
      }

      /* 3.  Icône du bouton et tooltip */
      icon.className  = pinned ? 'fa-solid fa-thumbtack-slash'
                               : 'fa-solid fa-thumbtack';
      btn.title       = pinned ? 'Unpin' : 'Pin';
      btn.dataset.isPinned = pinned ? '1' : '0';

      /* 4.  Afficher / masquer les boutons MoveUp / MoveDown */
      const actions = li.querySelector('.post-actions');
      if (pinned) {
        actions.querySelectorAll('.btn-move-up, .btn-move-down').forEach(b => b.remove());
      } else {
        // recrée uniquement s’ils n’existent plus
        if (!actions.querySelector('.btn-move-up')) {
          const up = createMoveButton('up', id);
          actions.insertBefore(up, btn.nextSibling);
        }
        if (!actions.querySelector('.btn-move-down')) {
          const down = createMoveButton('down', id);
          actions.insertBefore(down, btn.nextSibling);
        }
      }

      /* 5.  Resort localement */
      reorderDOM();
    } catch (err) {
      console.error(err);
      alert('Could not toggle pin.');
    }
  });

  /* ─────────────────── Move Up / Down ──────────────────────────────── */

  async function handleMove (postId, endpoint) {
    const res = await jsonFetch(`/posts/${postId}/${endpoint}`, { method: 'POST' });
    if (!res.ok) throw res;
    const { order } = await res.json();            // [{id, position}, …]
    order.forEach(({ id, position }) => {
      const li = document.getElementById(`post-${id}`);
      if (li) li.dataset.position = position;
    });
    reorderDOM();
  }

  list.addEventListener('click', (e) => {
    const up   = e.target.closest('.btn-move-up');
    const down = e.target.closest('.btn-move-down');
    if (!up && !down) return;

    const id = (up || down).dataset.postId;
    const endpoint = up ? 'move-up' : 'move-down';

    handleMove(id, endpoint).catch(err => {
      console.error(err);
      alert(`Could not move ${endpoint === 'move-up' ? 'up' : 'down'}.`);
    });
  });

  /* ─────────────────── Delete ──────────────────────────────────────── */

  list.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;
    if (!confirm('Delete this post?')) return;

    const id = btn.dataset.postId;
    try {
      const res = await jsonFetch(`/posts/${id}/delete`, { method: 'DELETE' });
      if (!res.ok) throw res;
      document.getElementById(`post-${id}`).remove();
      renumberPositions();
    } catch (err) {
      console.error(err);
      alert('Could not delete.');
    }
  });

  /* ─────────────────── Inline-edit ─────────────────────────────────── */

  list.addEventListener('click', (e) => {
    const trigger = e.target.closest('.btn-edit-inline');
    if (!trigger) return;

    const id  = trigger.dataset.postId;
    const li  = document.getElementById(`post-${id}`);
    if (li.querySelector('.edit-form')) return;      // already open

    const titleEl   = li.querySelector('.post-title');
    const contentEl = li.querySelector('.post-content');

    const form = document.createElement('div');
    form.className = 'edit-form';
    form.innerHTML = `
      <input  class="edit-title" value="${titleEl.textContent.trim()}">
      <textarea class="edit-body" rows="4">${contentEl.innerHTML.trim()}</textarea>
      <button class="btn-save">Save</button>
      <button class="btn-cancel">Cancel</button>
    `;
    li.querySelector('.post-actions').append(form);
    titleEl.hidden = contentEl.hidden = true;
    trigger.disabled = true;

    /* cancel */
    form.querySelector('.btn-cancel').onclick = () => {
      form.remove();
      titleEl.hidden = contentEl.hidden = false;
      trigger.disabled = false;
    };

    /* save */
    form.querySelector('.btn-save').onclick = async () => {
      const payload = {
        title  : form.querySelector('.edit-title').value.trim(),
        content: form.querySelector('.edit-body').value.trim()
      };
      try {
        const res = await jsonFetch(`/posts/${id}/inline-edit`, {
          method : 'POST',
          headers: { 'Content-Type': 'application/json' },
          body   : JSON.stringify(payload)
        });
        if (!res.ok) throw res;
        const data = await res.json();
        titleEl.textContent = data.title;
        contentEl.innerHTML = data.content;
        form.remove();
        titleEl.hidden = contentEl.hidden = false;
        trigger.disabled = false;
      } catch (err) {
        console.error(err);
        alert('Could not save.');
      }
    };
  });
});
