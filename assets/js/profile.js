
const pictureInput = document.getElementById("picture-input");
const profilePicture = document.getElementById("profile-picture");

pictureInput.addEventListener("change", function () {
    const file = pictureInput.files[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith("image/")) {
        alert("Silakan pilih file gambar.");
        pictureInput.value = "";
        return;
    }

    const imageURL = URL.createObjectURL(file);

    profilePicture.src = imageURL;
});
