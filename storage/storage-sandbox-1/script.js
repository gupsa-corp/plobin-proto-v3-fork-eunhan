document.addEventListener('DOMContentLoaded', function() {
  const title = document.querySelector('.title');
  if (title) {
    title.addEventListener('click', function() {
      alert('제목을 클릭했습니다!');
    });
  }
});