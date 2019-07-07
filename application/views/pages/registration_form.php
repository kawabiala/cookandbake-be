<html>
    <head>
        <title>Registration-Form</title>
    </head>
    <body>
        <h1>Registration-Form</h1>
        <form action="auth/register" method="post">
            Email: <input name="email" type="email"/><br/>
            Passwort: <input name="password" type="password"/><br/>
            <button type="submit">Registrieren</button>
        </form>
        
        <h1>Login-Form</h1>
        <form action="auth/login" method="post">
            Email: <input name="email" type="email"/><br/>
            Passwort: <input name="password" type="password"/><br/>
            <button type="submit">Login</button>
        </form>
    </body>
</html>