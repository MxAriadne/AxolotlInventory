const express = require('express');
const http = require('http');
const fs = require('fs');
const path = require('path');
const app = express();
const axios = require("axios");
const cheerio = require("cheerio");
const pretty = require("pretty");
const bodyParser = require('body-parser');
const port = 5000;

app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));

const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('item_info.db');

db.run("CREATE TABLE IF NOT EXISTS items (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, desc MEMO, timestamp TEXT)");
db.run("CREATE TABLE IF NOT EXISTS notes (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, timestamp TEXT, note TEXT, FOREIGN KEY (user_id) REFERENCES users(id))");

function addItem(name, desc) {
    const date = new Date();
    let currentDate = `${date.getDate()}-${date.getMonth()+1}-${date.getFullYear()}`;
    db.run("INSERT INTO items (name, desc, timestamp) VALUES (?, ?, ?)", [name, desc, currentDate]);
}

function removeItem(id) {
    db.run("DELETE FROM items WHERE id=?", [id]);

    const html = fs.readFileSync('items.html');
    const $ = cheerio.load(html);

    $('button[name="' + id + '"]').parent().parent().remove();
    fs.writeFileSync('items.html', $.html());
}

function addNote(id, name, note) {
    db.run("INSERT INTO notes (user_id, timestamp, note) VALUES (?, ?, ?)", [id, new Date().toISOString(), note]);
}

app.post('/add', function (req, res) {
    const { name, desc } = req.body;
    addItem(name, desc);
    console.log("Added: " + name + " / " + desc + "to database!");
});

app.post('/remove', function (req, res) {
    const { id } = req.body;
    console.log("Removed: " + id + " from database!");
    removeItem(id);
});

app.post('/show', function (req, res) {
    const html = fs.readFileSync('items.html');
    const $ = cheerio.load(html);

    db.all("SELECT * FROM items", (err, rows) => {
      if (err) {
        throw err;
      }
      for (let i = 0; i < rows.length; i++) {
        // Create a new row to append
        const newRow = $('<tr class=\"target\">')
          .append($('<td><button class=\"remove\" name=\"' + rows[i].id + '\" onClick=\"window.location.reload()\">🗑️</button></td>'))
          .append($('<td>').text(rows[i].id))
          .append($('<td>').text(rows[i].name))
          .append($('<td>').text(rows[i].desc))
          .append($('<td>').text(rows[i].timestamp));

        // Append the new row to the table
        $('#item-grid').append(newRow);
    }
    fs.writeFileSync('items.html', $.html());
    });
});

app.use('/css', express.static(__dirname + '/public/style'));
app.use('/js', express.static(__dirname + '/public/script'));
app.use('/img', express.static(__dirname + '/public/images'));

app.get('*', function(request, response){
  var filePath = '.' + request.url;
  if (filePath == './') {
    filePath = './index.html';
  }

  var extname = String(path.extname(filePath)).toLowerCase();
  var mimeTypes = {
    '.html': 'text/html',
    '.js': 'text/javascript',
    '.css': 'text/css',
    '.json': 'application/json',
    '.png': 'image/png',
    '.jpg': 'image/jpg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.wav': 'audio/wav',
    '.mp4': 'video/mp4',
    '.woff': 'application/font-woff',
    '.ttf': 'application/font-ttf',
    '.eot': 'application/vnd.ms-fontobject',
    '.otf': 'application/font-otf',
    '.wasm': 'application/wasm'
  };

  var contentType = mimeTypes[extname] || 'application/octet-stream';

  fs.readFile(filePath, function(error, content) {
    if (error) {
      if (error.code == 'ENOENT') {
        fs.readFile('./public/404.html', function(error, content) {
          response.writeHead(404, { 'Content-Type': contentType });
          response.end(content, 'utf-8');
        });
      } else {
        response.writeHead(500);
        response.end('Sorry, check with the site admin for error: ' + error.code + ' ..\n');
        response.end();
      }
    } else {
      response.writeHead(200, { 'Content-Type': contentType });
      response.end(content, 'utf-8');
    }
  });
});

app.listen(port);

console.log('Server running at http://127.0.0.1:' + port + '/');