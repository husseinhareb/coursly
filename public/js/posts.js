// assets/js/posts.js

document.addEventListener('DOMContentLoaded', () => {
  const list = document.querySelector('.posts-list');
  if (!list) return;

  // — PIN / UNPIN
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

        // Toggle the CSS class
        li.classList.toggle('pinned', pinned);

        // Update icon
        const iconsContainer = li.querySelector('.post-icons');
        let icon = iconsContainer.querySelector('.pin-icon');
        if (pinned) {
          if (!icon) {
            icon = document.createElement('span');
            icon.className = 'pin-icon';
            icon.title = btn.title = btn.textContent = 'Unpin';
            icon.textContent = '📌';
            iconsContainer.prepend(icon);
          }
          btn.textContent = 'Unpin';
        } else {
          icon && icon.remove();
          btn.textContent = 'Pin';
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
          body: JSON.stringify({ before: beforeVal })
        });
        if (!res.ok) throw res;
        await res.json();

        // Reorder in DOM
        if (beforeVal === null) {
          list.appendChild(li);
        } else {
          const target = Array.from(list.children)
            .find(el => Number(el.dataset.position) === beforeVal);
          target
            ? list.insertBefore(li, target)
            : list.appendChild(li);
        }

        // Reset positions
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

  // — INLINE EDIT
  list.querySelectorAll('.btn-edit-inline').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.postId;
      const li = document.getElementById(`post-${id}`);
      // prevent multiple edit forms
      if (li.querySelector('.edit-form')) return;

      const titleEl   = li.querySelector('.post-title');
      const contentEl = li.querySelector('.post-content');
      const origTitle   = titleEl.textContent.trim();
      const origContent = contentEl.innerHTML.trim();

      // hide existing
      titleEl.style.display   = 'none';
      contentEl.style.display = 'none';
      btn.disabled            = true;

      // build form
      const form = document.createElement('div');
      form.className = 'edit-form';
      form.innerHTML = `
        <input type="text" class="edit-title-input" value="${origTitle}">
        <textarea class="edit-content-input" rows="4">${origContent}</textarea>
        <button class="btn-save-edit">Save</button>
        <button class="btn-cancel-edit">Cancel</button>
      `;
      li.querySelector('.post-actions').appendChild(form);

      // Cancel handler
      form.querySelector('.btn-cancel-edit').addEventListener('click', () => {
        titleEl.style.display   = '';
        contentEl.style.display = '';
        btn.disabled            = false;
        form.remove();
      });

      // Save handler
      form.querySelector('.btn-save-edit').addEventListener('click', async () => {
        const newTitle   = form.querySelector('.edit-title-input').value.trim();
        const newContent = form.querySelector('.edit-content-input').value.trim();

        try {
          const res = await fetch(`/posts/${id}/inline-edit`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ title: newTitle, content: newContent })
          });
          if (!res.ok) throw res;
          const data = await res.json();

          // update DOM
          titleEl.textContent   = data.title;
          contentEl.innerHTML   = data.content;
          titleEl.style.display   = '';
          contentEl.style.display = '';
          btn.disabled            = false;
          form.remove();
        } catch (err) {
          console.error(err);
          alert('Unable to save changes.');
        }
      });
    });
  });

});
