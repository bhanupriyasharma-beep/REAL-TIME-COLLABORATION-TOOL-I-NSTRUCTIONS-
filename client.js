let currentFileId = null;
const editor = document.getElementById("editor");

// 🧠 Initialize WebSocket connection
function initClient() {
  const ws = new WebSocket("ws://localhost:8080");

  ws.onopen = () => {
    document.getElementById("status").textContent = "✅ Connected to WebSocket.";
  };

  ws.onmessage = (event) => {
    if (event.data !== editor.value) {
      editor.value = event.data;
    }
  };

  editor.addEventListener("input", () => {
    if (ws.readyState === WebSocket.OPEN) {
      ws.send(editor.value);
    }
  });
}

// 📄 Populate file list
function loadFileList() {
  fetch("files.php")
    .then(res => res.json())
    .then(files => {
      const fileSelect = document.getElementById("fileSelect");
      fileSelect.innerHTML = '<option value="">-- Select File --</option>';
      files.forEach(file => {
        const option = document.createElement("option");
        option.value = file.id;
        option.textContent = file.name;
        fileSelect.appendChild(option);
      });
    });
}

// ➕ New File
function handleNew() {
  editor.value = "";
  currentFileId = null;
  document.getElementById("fileSelect").value = "";
}

// 💾 Save As (new file)
function handleSaveAs(content) {
  const name = prompt("Enter file name:");
  if (!name) return;

  fetch("create.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ name, content })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert("✅ File saved successfully.");
        currentFileId = data.id;
        loadFileList();
      } else {
        alert("❌ Failed to save file.");
      }
    });
}

// ✅ Save (update current file)
function handleSave(content) {
  if (!currentFileId) {
    alert("Use 'Save As' to create a file first.");
    return;
  }

  fetch("save.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id: currentFileId, content })
  })
    .then(res => res.json())
    .then(data => {
      alert(data.success ? "✅ File updated." : "❌ Update failed.");
    });
}

// 📂 Open
function handleOpen() {
  const selectedId = document.getElementById("fileSelect").value;
  if (!selectedId) {
    alert("Please select a file.");
    return;
  }

  fetch(`load.php?id=${selectedId}`)
    .then(res => res.json())
    .then(data => {
      if (data.content !== undefined) {
        editor.value = data.content;
        currentFileId = data.id;
      } else {
        alert("❌ File not found.");
      }
    });
}

// 🗑️ Delete File
function handleDelete() {
  const selectedId = document.getElementById("fileSelect").value;
  if (!selectedId || !confirm("Delete this file?")) return;

  fetch(`delete.php?id=${selectedId}`, { method: "DELETE" })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert("✅ File deleted.");
        handleNew();
        loadFileList();
      } else {
        alert("❌ Deletion failed.");
      }
    });
}

// ✏️ Editing Actions
function execCommand(command, value = null) {
  document.execCommand(command, false, value);
  editor.focus();
}

// ⌨️ Event Bindings
document.getElementById("newFile").addEventListener("click", (e) => { e.preventDefault(); handleNew(); });
document.getElementById("saveAsFile").addEventListener("click", (e) => { e.preventDefault(); handleSaveAs(editor.value); });
document.getElementById("saveFile").addEventListener("click", (e) => { e.preventDefault(); handleSave(editor.value); });
document.getElementById("openFile").addEventListener("click", (e) => { e.preventDefault(); handleOpen(); });
document.getElementById("deleteFile").addEventListener("click", (e) => { e.preventDefault(); handleDelete(); });

document.getElementById("undoBtn").addEventListener("click", () => execCommand("undo"));
document.getElementById("redoBtn").addEventListener("click", () => execCommand("redo"));
document.getElementById("cutBtn").addEventListener("click", () => execCommand("cut"));
document.getElementById("copyBtn").addEventListener("click", () => execCommand("copy"));
document.getElementById("pasteBtn").addEventListener("click", () => execCommand("paste"));

document.getElementById("boldBtn").addEventListener("click", () => execCommand("bold"));
document.getElementById("italicBtn").addEventListener("click", () => execCommand("italic"));
document.getElementById("underlineBtn").addEventListener("click", () => execCommand("underline"));
document.getElementById("strikeBtn").addEventListener("click", () => execCommand("strikeThrough"));

document.getElementById("fontSelect").addEventListener("change", (e) => execCommand("fontName", e.target.value));
document.getElementById("fontSize").addEventListener("change", (e) => {
  editor.style.fontSize = e.target.value;
  editor.focus();
});
document.getElementById("fontColor").addEventListener("change", (e) => execCommand("foreColor", e.target.value));
document.getElementById("highlightColor").addEventListener("change", (e) => execCommand("hiliteColor", e.target.value));

// 🚀 Init
initClient();
loadFileList();

const ws = new WebSocket("ws://localhost:8080");
const editor = document.getElementById("editor");
const status = document.getElementById("connection-status");

function updateStatus(text, bg) {
  if (status) {
    status.textContent = text;
    status.style.backgroundColor = bg;
  }
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

