document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('#add-form').addEventListener('submit', (event) => {
    event.preventDefault(); // prevent the form from submitting normally
    const values = $(event.target).serializeArray();
    const name = values.find(field => field.name === 'name').value;
    const desc = values.find(field => field.name === 'desc').value;
    $.ajax({
      type: 'POST',
      url: '/add',
      data: { name, desc },
      success: function(response) {
        console.log(response); // log the server response
      }
    });
  });

    $('.remove').on('click', (event) => {
      const id = $(event.target).attr('name');
      $.ajax({
        type: 'POST',
        url: '/remove',
        data: { id },
        success: function(response) {
          console.log(response); // log the server response
        }
      });
    });

    $('#show').click(function(event) {
      $.ajax({
        type: 'POST',
        url: '/show',
        success: function(response) {
          console.log("Successfully loaded item database."); // log the server response
        }
      });
    });
  });