//Variables booléennes
let pseudo = false
let email = false;
let pass = false;

// check input
document.querySelector("#add_staff_form_pseudo").addEventListener('input', checkPseudo);
document.querySelector("#add_staff_form_email").addEventListener('input', checkEmail);
document.querySelector("#add_staff_form_plainPassword").addEventListener('input', checkPass);

// Pseudo need more than 2 characters
function checkPseudo(){
    pseudo = this.value.length > 2;
    checkAll();
}

// Regex to check email characters
function checkEmail(){
    let regex = new RegExp("\\S+@\\S+\\.\\S+");
    email = regex.test(this.value);
    checkAll();
}

// If all input are coorect you can register
function checkAll(){
    document.querySelector("#submit-button").setAttribute("disabled", "disabled");
    if(email && pseudo && pass){
        document.querySelector("#submit-button").removeAttribute("disabled");
    }
}

// Password strength translation in french
const PasswordStrength = {
    STRENGTH_VERY_WEAK: 'Très faible',
    STRENGTH_WEAK: 'Faible',
    STRENGTH_MEDIUM: 'Moyen',
    STRENGTH_STRONG: 'Fort',
    STRENGTH_VERY_STRONG: 'Très fort',
}

function checkPass(){
    // get the password
    let mdp = this.value;

    // get the entropy
    let entropyElement = document.querySelector("#entropy");

    // check password strength
    let entropy = evaluatePasswordStrength(mdp);

    // remove class colors
    entropyElement.classList.remove("text-red", "text-orange", "text-green");

    // change colors depending of entropy strength
    switch(entropy){
        case 'Très faible' :
            entropyElement.classList.add("text-red");
            pass = false;
            break;
        case 'Faible' :
            entropyElement.classList.add("text-red");
            pass = false;
            break;
        case 'Moyen' :
            entropyElement.classList.add("text-orange");
            pass = false;
            break;
        case 'Fort' :
            entropyElement.classList.add("text-green");
            pass = true;
            break;
        case 'Très fort' :
            entropyElement.classList.add("text-green");
            pass = true;
            break;
        default:
            entropyElement.classList.add("text-red");
            pass = false;
            break;
    }

    entropyElement.textContent = entropy;

    checkAll();
}

function evaluatePasswordStrength(password){
    // check password length
    let length = password.length;

    // if password is empty
    if(!length){
        return PasswordStrength.STRENGTH_VERY_WEAK;
    }

    // create object with characters and numbers
    let passwordChars = {};

    for(let index = 0; index < password.length; index++){
        let charCode = password.charCodeAt(index);
        passwordChars[charCode] = (passwordChars[charCode] || 0) + 1;
    }

    // count password strength
    let chars = Object.keys(passwordChars).length;

    // Initialize variables for the character types
    let control = 0, digit = 0, upper = 0, lower = 0, symbol = 0, other = 0;

    for(let [chr, count] of Object.entries(passwordChars)){
        chr = Number(chr);
        if(chr < 32 || chr === 127){
            // Caractère de contrôle
            control = 33;
        }else if(chr >= 48 && chr <= 57){
            // Numbers
            digit = 10;
        }else if(chr >= 65 && chr <= 90){
            // Upper cases
            upper = 26;
        }else if (chr >= 97 && chr <= 122){
            // lower cases
            lower = 26;
        }else if (chr >= 128){
            // other characters
            other = 128;
        }else{
            // Symbols
            symbol = 33;
        }
    }

    // count characters
    let pool =  control + digit + upper + lower + other + symbol;

    // Formule de calcul de l'entropie
    let entropy = chars * Math.log2(pool) + (length - chars) * Math.log2(chars);

    if(entropy >= 120){
        return PasswordStrength.STRENGTH_VERY_STRONG;
    }else if(entropy >= 100){
        return PasswordStrength.STRENGTH_STRONG;
    }else if(entropy >= 80){
        return PasswordStrength.STRENGTH_MEDIUM;
    }else if(entropy >= 60){
        return PasswordStrength.STRENGTH_WEAK;
    }else {
        return PasswordStrength.STRENGTH_VERY_WEAK;
    }
}