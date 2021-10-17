
(function () {
    const SUPPRESS_CONSOLE = true;
    const URL = 'src/captcha.php';

    function _POST_REQUEST(url, params, response) {
        var xhttp;
        if (window.XMLHttpRequest) {
            xhttp = new XMLHttpRequest();
        } else {
            xhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                response(this.responseText);
            }
        };

        xhttp.open("POST", url, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(params);
    }

    // generate captcha uid that can be used to verify user
    const formEls = document.querySelectorAll('[data-mathcaptcha]');
    formEls.forEach((e) => {
        _POST_REQUEST(URL, 'o=uid', (r) => {
            let uid = JSON.parse(r).r
            if (!SUPPRESS_CONSOLE) {console.log("UID:");console.log(JSON.parse(r))}

            // now generate puzzle for uid and display the puzzle on the el
            _POST_REQUEST(URL, 'o=generate&i=' + uid, (r) => {
                e.src = JSON.parse(r).r

                let inpt = document.createElement('input')
                inpt.type = 'hidden'
                inpt.name = 'captcha_uid'
                inpt.value = uid
                e.insertAdjacentElement('afterend',inpt)

                document.querySelector(e.getAttribute('data-mathcaptcha')).setAttribute('name', 'captcha_answer')

                if (!SUPPRESS_CONSOLE) {console.log("Calculation Image:"); console.log(JSON.parse(r))}
            })
        })
    })
})()
