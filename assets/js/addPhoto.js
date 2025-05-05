document.querySelector('#edit_account_form_picture').addEventListener("change", checkFile);

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

    // On vérifie si le fichier existe
    if(file){
        // On vérifie que le fichier est une image acceptée
        if(types.includes(file.type)){  
            reader.readAsDataURL(file);
        }
    }else{
        image.src = "";
        preview.style.display = "none";
    }

}