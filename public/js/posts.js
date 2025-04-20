// public/js/posts.js
document.addEventListener('DOMContentLoaded', () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    const csrf  = meta ? meta.getAttribute('content') : '';
  
    // PIN/UNPIN
    document.querySelectorAll('.btn-pin').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.postId;
        fetch(`/posts/${id}/toggle-pin`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf }
        })
        .then(r => r.json())
        .then(json => {
          if (json.pinned) {
            btn.textContent = btn.title = 'Unpin';
            document.getElementById(`post-${id}`).classList.add('pinned');
          } else {
            btn.textContent = btn.title = 'Pin';
            document.getElementById(`post-${id}`).classList.remove('pinned');
          }
        });
      });
    });
  
    // MOVE
    document.querySelectorAll('.post-move').forEach(sel => {
      sel.addEventListener('change', () => {
        const id     = sel.dataset.postId;
        const before = parseInt(sel.value, 10) || null;
        fetch(`/posts/${id}/move`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({ before })
        })
        .then(r => r.json())
        .then(json => {
          if (json.success) window.location.reload();
        });
      });
    });
  
    // INLINE EDIT
    document.querySelectorAll('.btn-edit-inline').forEach(btn => {
      btn.addEventListener('click', () => {
        const id        = btn.dataset.postId;
        const container = document.getElementById(`post-${id}`);
        const titleEl   = container.querySelector('.post-title');
        const contentEl = container.querySelector('.post-content');
  
        // Create inputs
        const titleIn = document.createElement('input');
        titleIn.value = titleEl.textContent;
        titleIn.className = 'inline-title';
        titleEl.replaceWith(titleIn);
  
        const contentIn = document.createElement('textarea');
        contentIn.textContent = contentEl.textContent.trim();
        contentIn.className = 'inline-content';
        contentEl.replaceWith(contentIn);
  
        // Switch button to “Save”
        btn.textContent = 'Save';
        btn.classList.replace('btn-edit-inline','btn-save-inline');
  
        btn.addEventListener('click', () => {
          fetch(`/posts/${id}/inline-edit`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({
              title:   titleIn.value,
              content: contentIn.value
            })
          })
          .then(r => r.json())
          .then(json => {
            // restore markup
            const newTitle = document.createElement('strong');
            newTitle.className = 'post-title';
            newTitle.textContent = json.title;
            titleIn.replaceWith(newTitle);
  
            const newContent = document.createElement('div');
            newContent.className = 'post-content';
            newContent.textContent = json.content;
            contentIn.replaceWith(newContent);
  
            btn.textContent = 'Edit';
            btn.classList.replace('btn-save-inline','btn-edit-inline');
          });
        }, { once: true });
      });
    });
  });
  