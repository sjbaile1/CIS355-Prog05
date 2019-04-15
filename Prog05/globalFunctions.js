// Code taken from https://canvas.svsu.edu/courses/28460/files/folder/_file_upload
var _validFileExtensions = [".jpg", ".jpeg", ".gif", ".png"];

function loadDoc(url, method, form) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var redirect = false;
            try {
                var response = JSON.parse(this.responseText);
                if (response.location) {
                    window.location.href = response.location;
                    redirect = true;
                }
            } catch {

            }

            if (!redirect)
                document.getElementById("htmlDiv").innerHTML = this.responseText;
        }
    };

    xhttp.open(method, "http://csis.svsu.edu/~sjbaile1/CIS355/Prog05/" + url, true);

    if (form != null && form != undefined){
        xhttp.send(new FormData(form));
    } else {
        xhttp.send();
    }
    return false;
}

function submitForm(formElement){
    if (Validate(formElement)) {
        formElement.submit(function (e) {

            e.preventDefault();

            $.ajax({
                type: formElement.attr('method'),
                url: formElement.attr('action'),
                data: formElement.serialize(),
                success: function (data) {
                    window.location.href = "login.html";
                    console.log('Submission was successful.');
                    console.log(data);
                },
                error: function (data) {
                    window.location.href = "login.html";
                    console.log('An error occurred.');
                    console.log(data);
                },
            });
        });
    } else {
        return false;
    }
}

function readURL(input) {
    if (input.files[0].size > 2000000) {
        input.value = null;
        alert("The picture cannot be larger than 2MB in size!");
    }
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#imgDisplay').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $('#imgDisplay').attr('src', null);
    }
}

function Validate(oForm) {
    var arrInputs = oForm.getElementsByTagName("input");
    for (var i = 0; i < arrInputs.length; i++) {
        var oInput = arrInputs[i];
        if (oInput.type == "file") {
            var sFileName = oInput.value;
            if (sFileName.length > 0) {
                var blnValid = false;
                for (var j = 0; j < _validFileExtensions.length; j++) {
                    var sCurExtension = _validFileExtensions[j];
                    if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
                        blnValid = true;
                        break;
                    }
                }

                if (!blnValid) {
                    alert("Sorry, " + sFileName + " is invalid, allowed extensions are: " + _validFileExtensions.join(", "));
                    return false;
                }

            }
        }
    }

    return true;
}