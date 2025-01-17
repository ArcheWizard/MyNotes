// Fetch notes from server and render them in the DOM
function loadNotes() {
    $('#notelist').html('<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>');

    $.get('logged_in.php?action=fetchNotes', function (notes) {
        let noteHTML = '';
        notes.forEach(note => {
            noteHTML += `
                <div class="note card mb-3 shadow-sm" id="note-${note.id}">
                    <div class="card-body">
                        <div class="note-content">
                            ${sanitizeHTML(note.content)}
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            <button class="btn btn-primary me-2" onclick="popup('${escapeHTML(note.content)}', ${note.id})">Edit</button>
                            <button class="btn btn-danger" onclick="deleteNote(${note.id})">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#notelist').html(noteHTML);
    });
}





// Create or edit a note
function createNote(noteId = null) {
    // Fetch content from the Quill editor
    const noteContent = window.quillInstance.root.innerHTML.trim();
    if (noteContent === "<p><br></p>") { // Check for empty content
        alert("Note text cannot be empty!");
        return;
    }

    // Determine action type
    const action = noteId ? "edit" : "create";

    // Send the content to the server
    $.post(window.location.href, { action: action, id: noteId, text: encodeURIComponent(noteContent) }, function () {
        loadNotes();
        closePopup();
    });
}





// Delete a note
function deleteNote(noteId) {
    if (confirm("Are you sure you want to delete this note?")) {
        $.post(window.location.href, { action: "delete", id: noteId }, function () {
            loadNotes();
        });
    }
}

// Display popup for note
function popup(existingText = "", noteId = null) {
    // HTML for the popup
    const popupHTML = `
        <div id="popupContainer" class="card shadow-sm">
            <div class="card-body">
                <h5>${noteId ? "Edit Note" : "New Note"}</h5>
                <!-- Quill editor container -->
                <div id="editor" style="min-height: 150px;"></div>
                <div class="mt-3">
                    <button class="btn btn-success" onclick="createNote(${noteId})">
                        ${noteId ? "Save Changes" : "Create Note"}
                    </button>
                    <button class="btn btn-secondary" onclick="closePopup()">Close</button>
                </div>
            </div>
        </div>`;

    // Remove any existing popup
    $("#popupContainer").remove();

    // Append the popup to the DOM
    $("#notelist").append(popupHTML);

    // Initialize Quill editor
    const quill = new Quill("#editor", {
        theme: "snow",
    });

    // Populate the editor with existing text if editing
    if (existingText) {
        quill.clipboard.dangerouslyPasteHTML(existingText);
    }

    // Save the Quill instance globally for later use
    window.quillInstance = quill;
}




// Close popup
function closePopup() {
    $("#popupContainer").remove();
}