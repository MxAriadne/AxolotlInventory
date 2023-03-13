function searchItems() {
  let input = document.getElementById("Search");
  let filter = input.value.toLowerCase();
  let nodes = document.getElementsByClassName('target');

  for (let i of nodes) {
    if (i.innerText.toLowerCase().includes(filter)) {
      i.style.display = "";
    } else {
      i.style.display = "none";
    }
  }
}

document.addEventListener('DOMContentLoaded', function () {
    $('.tab').on('click', function(){
        $(this).next().slideToggle(700);
    });
});