const express = require('express');
const http = require('http');
const fs = require('fs');
const path = require('path');
const app = express();
const axios = require("axios");
const cheerio = require("cheerio");
const pretty = require("pretty");
const bodyParser = require('body-parser');
const jsdom = require("jsdom");
const { JSDOM } = jsdom;
const { document } = (new JSDOM(`...`)).window;
const { window } = new JSDOM(`...`);
const port = 5000;

app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));
app.use('/css', express.static(__dirname + '/public/style'));
app.use('/js', express.static(__dirname + '/public/script'));
app.use('/img', express.static(__dirname + '/public/images'));

const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('item_info.db');

db.run("CREATE TABLE IF NOT EXISTS items (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, desc MEMO, timestamp TEXT)");
db.run("CREATE TABLE IF NOT EXISTS notes (id INTEGER PRIMARY KEY AUTOINCREMENT, user TEXT, item_id INTEGER, timestamp TEXT, note TEXT, FOREIGN KEY (item_id) REFERENCES items(id))");

function addItem(name, desc) {
    const currentDate = new Date().toLocaleDateString('en-US');
    db.run("INSERT INTO items (name, desc, timestamp) VALUES (?, ?, ?)", [name, desc, currentDate]);
}

function removeItem(id) {
    db.run("DELETE FROM items WHERE id=?", [id]);

    const html = fs.readFileSync('items.html');
    const $ = cheerio.load(html);

    $('button[name="' + id + '"]').parent().parent().remove();
    fs.writeFileSync('items.html', $.html());
}

function addNote(id, user, note) {
    db.run("INSERT INTO notes (user, item_id, timestamp, note) VALUES (?, ?, ?, ?)", [user, id, new Date().toLocaleDateString('en-US'), note]);
}

function removeNote(id) {
    db.run("DELETE FROM notes WHERE id=?", [id]);

    const html = fs.readFileSync('items.html');
    const $ = cheerio.load(html);

    $('button[name="note-' + id + '"]').parent().parent().remove();
    fs.writeFileSync('items.html', $.html());
}

function showItems () {
    const html = fs.readFileSync('items.html');
    const $ = cheerio.load(html);

    db.all("SELECT * FROM items", (err, rows) => {
    if (err) {
        throw err;
    }
    for (let i = 0; i < rows.length; i++) {
    // Create a new row to append
        if (!$(`#${rows[i].id}`).length)
        {
            const newRow = $('<tr class=\"target\" id=\"' + rows[i].id + '\">')
                .append($('<td><button class=\"view\">🔎</button><button class=\"remove-item\" name=\"' + rows[i].id + '\" onClick=\"window.location.reload()\">🗑️</button></td>'))
                .append($('<td>').text(rows[i].id))
                .append($('<td>').text(rows[i].name))
                .append($('<td>').text(rows[i].desc))
                .append($('<td>').text(rows[i].timestamp));
                // Append the new row to the table
                    $('#item-grid').append(newRow);
                console.log("Item " + rows[i].id + " has been added to the item grid!")
        } else
        {
            console.log("Item " + rows[i].id + " is already in the item grid!")
        }
    }
        fs.writeFileSync('items.html', $.html());
    });
}

function showNotes () {
    const html = fs.readFileSync('items.html');
    const $ = cheerio.load(html);

    db.all("SELECT * FROM notes", (err, rows) => {
    if (err) {
        throw err;
    }
    for (let i = 0; i < rows.length; i++) {
    // Create a new row to append
        if (!$(`#${rows[i].id}`).length)
        {
            const newRow = $('<tr id=\"note-' + rows[i].id + '\">')
                .append($('<td><button class=\"remove-note\" name=\"name-' + rows[i].id + '\" onClick=\"window.location.reload()\">🗑️</button></td>'))
                .append($('<td>').text(rows[i].note))
                .append($('<td>').text(rows[i].timestamp));
                // Append the new row to the table
                $('#note-grid').append(newRow);
                console.log("Note " + rows[i].id + " has been added to the note grid!")
        } else
        {
            console.log("Note " + rows[i].id + " is already in the Note grid!")
        }
    }
        fs.writeFileSync('items.html', $.html());
    });
}

app.post('/add-item', function (req, res) {
    const { name, desc } = req.body;
    console.log("Added: " + name + " / " + desc + "to database!");
    addItem(name, desc);
});

app.post('/add-note', function (req, res) {
    const { id, user, note } = req.body;
    console.log("Added: " + user + " / " + note + "to database!");
    addNote(id, user, note);
});

app.post('/remove-item', function (req, res) {
    const { id } = req.body;
    console.log("Removed item: " + id + " from database!");
    removeItem(id);
});

app.post('/remove-note', function (req, res) {
    const { id } = req.body;
    console.log("Removed note: " + id + " from database!");
    removeNote(id);
});

app.get('/modal', function (req, res) {
    const { id } = req.query;
    let data = [id];

    db.each("SELECT * FROM items WHERE id=?", id ,function(err, row) {
        if (err) {
            throw err;
        }
            data.push(row.name);
            data.push(row.desc);
            data.push(row.timestamp);
        });

    db.all("SELECT * FROM notes WHERE item_id=?", id ,function(err, notes) {
        if (err) {
            throw err;
        }
            data.push(notes);
            res.json(JSON.stringify(data));
        });
});

app.get('*', function(request, response){
    let filePath = `.${request.url}`;
    if (filePath == './') {
        filePath = './index.html';
    } else if (filePath === './items.html') {
        showItems();
    }

    var extname = String(path.extname(filePath)).toLowerCase();
    var mimeTypes =
        {
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

            }
            else {
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