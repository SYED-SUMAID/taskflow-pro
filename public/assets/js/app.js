
const newProjectBtn = document.getElementById("newProjectBtn");

newProjectBtn.addEventListener("click", () => {
    const projectName = prompt("Enter your new project name:");

    if (projectName === null) {
        return;
    }

    if (projectName.trim() === "") {
        alert("Project name cannot be empty.");
        return;
    }

    alert(
        `Project "${projectName.trim()}" will be added when project creation is connected to the backend.`
    );
});
