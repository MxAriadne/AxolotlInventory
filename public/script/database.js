function appendItem(rows) {
   $("p").append("<tr><td>" + rows.id + "</td><td>" + rows.name + "</td><td>" + rows.desc + "</td><td>" + rows.date + "</td></tr>");
}

module.exports = {
  appendItem
};