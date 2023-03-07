function itemObj(name, desc) {
    this.name = name;
    this.desc = desc;
}

function addElement() {
    var item = new itemObj("this is a name", "testing");
    const key = Date.now().toString(); // Generate a unique key
    localStorage.setItem(key, JSON.stringify(item));
    location.reload();
}

function showItems() {
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        const item = JSON.parse(localStorage.getItem(key));
        console.log(`${key}: ${item.name} - ${item.desc}`);
    }
}

function searchItems() {
  var input = document.getElementById("Search");
  var filter = input.value.toLowerCase();
  var nodes = document.getElementsByClassName('target');

  for (i = 0; i < nodes.length; i++) {
    if (nodes[i].innerText.toLowerCase().includes(filter)) {
      nodes[i].style.display = "block";
    } else {
      nodes[i].style.display = "none";
    }
  }
}

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('right-data-box');
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        const item = JSON.parse(localStorage.getItem(key));
        const div = document.createElement('div');
        div.innerHTML = `<p>${item.name}</p><p>${item.desc}</p>`;
        container.appendChild(div);
    }

    $('.tab').on('click', function(){
        $(this).next().slideToggle(700);

        });
});