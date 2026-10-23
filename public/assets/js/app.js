
const newProjectBtn = document.getElementById("newProjectBtn");
const projectForm = document.getElementById("projectForm");
const cancelProjectBtn = document.getElementById("cancelProjectBtn");
const projectNameInput = document.getElementById("projectName");

projectForm.hidden = true;

newProjectBtn.addEventListener("click", () => {
    projectForm.hidden = false;
    projectForm.scrollIntoView({
        behavior: "smooth",
        block: "center"
    });

    projectNameInput.focus();
});

cancelProjectBtn.addEventListener("click", () => {
    projectForm.hidden = true;
});
