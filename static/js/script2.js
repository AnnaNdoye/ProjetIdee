function formatText(command) {
    document.execCommand(command, false, null);
}

function toggleFormat(command) {
    formatText(command);
    const button = document.getElementById(`${command}Button`);
    if (document.queryCommandState(command)) {
        button.classList.add('active');
    } else {
        button.classList.remove('active');
    }
}

function syncContent() {
    const contentEditableDiv = document.getElementById('contenu');
    const hiddenTextarea = document.getElementById('hiddenContent');
    hiddenTextarea.value = contentEditableDiv.innerHTML;
}

function displayFileName() {
    const input = document.getElementById('fichier');
    const fileName = input.files[0].name;
    document.getElementById('file-name').textContent = `Selected file: ${fileName}`;
}

document.getElementById('fichier').addEventListener('change', displayFileName);