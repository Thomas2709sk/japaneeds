// Get the element by its ID
document.querySelector('#edit_account_form_picture').addEventListener("change", checkFile);

// Show preview of user profile pictures ( only jpeg, png or webp)
function checkFile(){
    let preview = document.querySelector(".preview");
    let image =preview.querySelector("img");
    let file = this.files[0];
    const types = ["image/jpeg", "image/png", "image/webp"];
    let reader = new FileReader();

    reader.onloadend = function(){
        image.src = reader.result;
        preview.style.display = "block";
    }

    // if file exist
    if(file){
        // if file type is correct > display
        if(types.includes(file.type)){  
            reader.readAsDataURL(file);
        }
    }else{
        image.src = "";
        preview.style.display = "none";
    }

}