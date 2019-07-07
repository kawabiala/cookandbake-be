<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="google-signin-client_id" content="851675929977-jnl65e25478i01c24ohfuntmujqg3fiq.apps.googleusercontent.com">
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        <script>
            function onSignIn(googleUser) {
              var profile = googleUser.getBasicProfile();
              console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
              console.log('Name: ' + profile.getName());
              console.log('Image URL: ' + profile.getImageUrl());
              console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
                var id_token = googleUser.getAuthResponse().id_token;
                
                //var path = window.location.href;
                var path = "https://www.pingwinek.de/cookandbake/authenticate";
                var params = {"id_token": id_token};
                
                //post(path, params);
                
                //window.location.assign("https://oauth2.googleapis.com/tokeninfo?id_token=" + id_token);
                
                
                var xhr = new XMLHttpRequest();
                xhr.open('POST', path);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                  console.log('Signed in as: ' + xhr.responseText);
                };
                xhr.send('id_token=' + id_token);
                
            }
            function signOut() {
                var auth2 = gapi.auth2.getAuthInstance();
                auth2.signOut().then(function () {
                  console.log('User signed out.');
                });
            }
            
            function post(path, params) {
                var form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", path);
                
                for (var key in params) {
                    if(params.hasOwnProperty(key)) {
                        console.log(key);
                        var hiddenField = document.createElement("input");
                        hiddenField.setAttribute("type", "hidden");
                        hiddenField.setAttribute("name", key);
                        hiddenField.setAttribute("value", params[key]);
                        
                        form.appendChild(hiddenField);
                    }
                }
                
                document.body.appendChild(form);
                form.submit();
            }
        </script>
    </head>
    <body>
        <p>Php-Test</p>
        <?php
            echo "Index.php";
        ?>
        <div class="g-signin2" data-onsuccess="onSignIn"></div>
        <p><a href="#" onclick="signOut();">Sign out</a></p>
    </body>
</html>