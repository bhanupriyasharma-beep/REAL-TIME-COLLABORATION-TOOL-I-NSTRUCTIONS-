<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Collaborative Editor</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="toolbar">
    <button onclick="format('bold')">Bold</button>
    <button onclick="format('italic')">Italic</button>
    <button onclick="format('underline')">Underline</button>
    <button onclick="format('strikeThrough')">Strike</button>

    <input type="color" onchange="format('foreColor', this.value)">
    <input type="color" onchange="format('hiliteColor', this.value)">

    <select onchange="format('fontName', this.value)">
      <option value="Arial">Arial</option>
      <option value="Georgia">Georgia</option>
      <option value="Courier New">Courier</option>
    </select>

    <select onchange="format('fontSize', this.value)">
      <option value="3">Normal</option>
      <option value="4">Large</option>
      <option value="5">Extra Large</option>
    </select>

    <button onclick="document.execCommand('undo')">Undo</button>
    <button onclick="document.execCommand('redo')">Redo</button>
    <button onclick="document.execCommand('cut')">Cut</button>
    <button onclick="document.execCommand('copy')">Copy</button>
    <button onclick="document.execCommand('paste')">Paste</button>

    <button onclick="newFile()">New</button>
    <button onclick="saveFile()">Save</button>
    <button onclick="toggleFileList()">Open</button>
    <button onclick="deleteFile()">Delete</button>
  </div>

  <div id="file-list" style="display: none;"></div>

  <div id="editor" contenteditable="true"></div>

  <div id="connection-status"
       style="position: fixed; bottom: 10px; right: 10px; padding: 6px 12px;
              background: #eee; border: 1px solid #ccc; font-size: 13px;
              border-radius: 5px; z-index: 100;">
    Connecting...
  </div>

  <script>
    let currentFileId = null;

    // WebSocket Setup
    const ws = new WebSocket("ws://localhost:8080");
    const editor = document.getElementById("editor");
    const status = document.getElementById("connection-status");

    function updateStatus(text, bg) {
      status.textContent = text;
      status.style.backgroundColor = bg;
    }

    ws.onopen = () => updateStatus("🟢 Connected", "#d4edda");
    ws.onclose = () => updateStatus("🔴 Disconnected", "#f8d7da");
    ws.onerror = () => updateStatus("⚠️ Error", "#fff3cd");

    ws.onmessage = (event) => {
      if (document.activeElement !== editor) {
        editor.innerHTML = event.data;
      }
    };

    editor.addEventListener("input", () => {
      if (ws.readyState === WebSocket.OPEN) {
        ws.send(editor.innerHTML);
      }
    });

    function format(cmd, value = null) {
      document.execCommand(cmd, false, value);
    }

    function newFile() {
      currentFileId = null;
      editor.innerHTML = '';
      editor.focus();
    }

    function saveFile() {
      const content = editor.innerHTML;

      if (!currentFileId) {
        const name = prompt("Enter filename:");
        if (!name) return;

        const formData = new FormData();
        formData.append("name", name);
        formData.append("content", content);

        fetch("api.php?action=create", {
          method: "POST",
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.id) {
            currentFileId = data.id;
            alert("File saved.");
            loadFiles();
          } else {
            alert("Failed to save file.");
          }
        });
      } else {
        updateContent(currentFileId, content);
      }
    }

    function updateContent(id, content) {
      const formData = new FormData();
      formData.append("id", id);
      formData.append("content", content);

      fetch("api.php?action=save", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === "ok") {
          alert("File updated.");
        } else {
          alert("Failed to update file.");
        }
      });
    }

    function toggleFileList() {
      const list = document.getElementById("file-list");
      list.style.display = list.style.display === "none" ? "block" : "none";
      if (list.style.display === "block") loadFiles();
    }

    function loadFiles() {
      fetch("api.php?action=list")
        .then(res => res.json())
        .then(data => {
          if (!data.length) {
            document.getElementById("file-list").innerHTML = '<p>No files available.</p>';
            return;
          }
          let html = data.map(doc =>
            `<div><a href="#" onclick="openFile(${doc.id}, this)">${doc.name}</a></div>`
          ).join("");
          document.getElementById("file-list").innerHTML = html;
        });
    }

    function openFile(id, element = null) {
      fetch(`api.php?action=load&id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (!data || !data.id) return alert("Failed to load file.");
          currentFileId = data.id;
          editor.innerHTML = data.content;
          editor.focus();

          document.querySelectorAll("#file-list a").forEach(a => a.style.fontWeight = "normal");
          if (element) element.style.fontWeight = "bold";

          document.getElementById("file-list").style.display = "none";
        });
    }

    function deleteFile() {
      if (!currentFileId) return alert("No file loaded");

      if (!confirm("Are you sure you want to delete this file?")) return;

      const formData = new FormData();
      formData.append("id", currentFileId);

      fetch("api.php?action=delete", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === "deleted") {
          alert("File deleted");
          newFile();
          loadFiles();
          document.getElementById("file-list").style.display = "none";
        } else {
          alert("Failed to delete file");
        }
      });
    }
    <script src="client.js"></script> 
</script>
</body>
</html>

