// Fetch notes from server
function loadNotes() {
    $.get(window.location.href, function (data) {
        const notelist = $(data).find('#notelist').html();
        $('#notelist').html(notelist);
    });
}

// Create or edit a note
function createNote(noteId = null) {
    const noteText = $("#note-text").val().trim();
    if (noteText === "") {
        alert("Note text cannot be empty!");
        return;
    }

    const action = noteId ? "edit" : "create";
    $.post(window.location.href, { action: action, id: noteId, text: noteText }, function () {
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
    const popupHTML = `
        <div id="popupContainer" class="card shadow-sm">
            <div class="card-body">
                <h5>${noteId ? "Edit Note" : "New Note"}</h5>
                <textarea id="note-text" class="form-control" placeholder="Enter your note...">${existingText}</textarea>
                <div class="mt-3">
                    <button class="btn btn-success" onclick="createNote(${noteId})">${noteId ? "Save Changes" : "Create Note"}</button>
                    <button class="btn btn-secondary" onclick="closePopup()">Close</button>
                </div>
            </div>
        </div>`;
    $("#popupContainer").remove();
    $("#notelist").append(popupHTML);
}

// Close popup
function closePopup() {
    $("#popupContainer").remove();
}