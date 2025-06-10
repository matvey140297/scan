(function(){
  // здесь 900000 мс = 15 минут
  let idleTimer = null;

  function resetTimer() {
    // сброс локального таймера
    clearTimeout(idleTimer);
    idleTimer = setTimeout(onIdle, 15 * 60 * 1000);
  }

  function onIdle() {
    alert('Превышено время ожидания')
    // шлём запрос на разлогин
    fetch('logout.php', {
      method: 'POST',
      credentials: 'include'
    }).finally(() => {
      // сразу выкидываем на логин
      window.location.href = 'login_page.php?timeout=1';
    });
  }

  // слушаем активность пользователя
  ['mousemove','mousedown','keydown','scroll','touchstart'].forEach(evt => {
    document.addEventListener(evt, resetTimer, {passive:true});
  });

  // сразу стартуем первый таймер
  resetTimer();
})();
