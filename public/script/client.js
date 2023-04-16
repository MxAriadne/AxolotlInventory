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
    const modal = document.getElementById("item-modal");
    const span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }

    document.onkeydown = function(evt) {
        evt = evt || window.event;
        let isEscape = false;
        if ("key" in evt) {
            isEscape = (evt.key === "Escape" || evt.key === "Esc");
        } else {
            isEscape = (evt.keyCode === 27);
        }
        if (isEscape && modal.style.display == "block") {
            modal.style.display = "none";
        }
    };

    let elements = document.getElementsByClassName("view");
    let viewItem = function() {
        const id = $(this).parent().parent().attr('id');
        $.ajax(
            {
            type: 'GET',
            url: '/modal',
            data: { id },
            success: function(response)
            {
            if (response != null) {
                modal.style.display = "block";
                let data = JSON.parse(response);

                $("#item-id").text(data[0]);
                $("#modal-title").text(data[1]);
                $("#modal-desc").text(data[2]);

                for (let i = 0; i < data[4].length; i++) {
                    console.log(data[4][i]);
                }

            } else {
                alert("ERROR: This item has no content or does not exist.")
            }
            }
            });
    };

    for (let i = 0; i < elements.length; i++) {
        elements[i].addEventListener('click', viewItem, false);
    }

    $('.tab').on('click', function(){
        $(this).next().slideToggle(700);
    });

    document.querySelector('#add-form').addEventListener('submit', (event) => {
        event.preventDefault(); // prevent the form from submitting normally
        const values = $(event.target).serializeArray();
        const name = values.find(field => field.name === 'name').value;
        const desc = values.find(field => field.name === 'desc').value;
        $.ajax(
            {
            type: 'POST',
            url: '/add-item',
            data: { name, desc },
            success: function(response) {
            console.log(response); // log the server response
            }
        });
    });

    document.querySelector('#add-note-form').addEventListener('submit', (event) => {
        event.preventDefault(); // prevent the form from submitting normally
        const values = $(event.target).serializeArray();
        const id = document.getElementById('item-id').value;
        const user = values.find(field => field.name === 'user').value;
        const note = values.find(field => field.name === 'note').value;
        $.ajax(
            {
            type: 'POST',
            url: '/add-note',
            data: { id, user, note },
            success: function(response) {
            console.log(response); // log the server response
            }
        });
    });

    $('.remove-item').on('click', (event) => {
        const id = $(event.target).attr('name');
        $.ajax(
            {
            type: 'POST',
            url: '/remove-item',
            data: { id },
            success: function(response)
            {
            console.log(response); // log the server response
            }
            });
        });

    $('.remove-note').on('click', (event) => {
        const id = $(event.target).attr('name');
        $.ajax(
            {
            type: 'POST',
            url: '/remove-note',
            data: { id },
            success: function(response)
            {
            console.log(response); // log the server response
            }
            });
        });

    });