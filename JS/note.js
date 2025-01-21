// Sort notes by title alphabetically
function sortNotes() {
    const order = $("#sortTitle").val(); // Get selected sorting order (asc/desc)

    // Send AJAX request to fetch sorted notes
    $.get(window.location.href, { action: "fetch-notes", sort: order }, function (data) {
        $("#notelist").html(data); // Replace the note list with the sorted data
    }).fail(function () {
        alert("An error occurred while sorting notes. Please try again.");
    });
}

// Filter notes by title
function filterNotes() {
    const filterText = $("#titleFilter").val().trim(); // Get the text entered in the filter input

    $.get(window.location.href, { 
        action: "fetch-notes", 
        filter: filterText 
    }, function (data) {
        $("#notelist").html(data); // Replace the note list with the filtered data
    }).fail(function () {
        alert("An error occurred while filtering notes. Please try again.");
    });
}



// Fetch notes from server and render them in the DOM
function loadNotes(){
    $.get(window.location.href, function (data) {
        const notelist = $(data).find('#notelist').html();
        $('#notelist').html(notelist);
    });
}

// Create or edit a note
function createNote(noteId = null) {
    const noteContent = window.quillInstance.root.innerHTML.trim();
    const noteTitle = $("#titleInput").val().trim();

    if (!noteTitle) {
        alert("Title cannot be empty!");
        return;
    }

    if (noteContent === "<p><br></p>") {
        alert("Note text cannot be empty!");
        return;
    }

    const action = noteId ? "edit" : "create";

    $.post(window.location.href, 
        { 
            action: action, 
            id: noteId, 
            title: noteTitle, 
            text: encodeURIComponent(noteContent) 
        },
        function () {
            loadNotes(); // Ensure notes are reloaded after creating/editing
            closePopup();
    }).fail(function() {
        alert("An error occurred while saving the note. Please try again.");
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
function popup(existingText = "", noteId = null, existingTitle = "") {
    // HTML for the popup
    const popupHTML = `
        <div id="popupContainer" class="card shadow-sm">
            <div class="card-body">
                <h5>${noteId ? "Edit Note" : "New Note"}</h5>
                <!-- Title input -->
                <div class="mt-3">
                    <input type="text" id="titleInput" class="form-control" placeholder="Title" value="${existingTitle}" required>
                </div>
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
    //$("#notelist").append(popupHTML);
    $("#notelist").prepend(popupHTML);

    $("#titleInput").focus();

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