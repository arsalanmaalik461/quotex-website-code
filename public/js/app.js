// ===== QUOTEX APP.JS (INTERACTIVE BEHAVIORS) =====
document.addEventListener('DOMContentLoaded', () => {
  // 1. FAQ Accordion for .qx-faq-item
  document.querySelectorAll('.qx-faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.qx-faq-item');
      item.classList.toggle('open');
    });
  });

  // 2. Fallback FAQ Accordion for other pages
  document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      item.classList.toggle('open');
    });
  });
});
