document.addEventListener('click', e => {
  if (e.target.matches('.btn-ajax')) {
    e.preventDefault();
    fetch(e.target.href, { credentials: 'same-origin' })
      .then(r => r.text()).then(t => alert('Action complete'))
  }
})
