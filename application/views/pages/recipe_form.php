<html>
    <head>
        <title>Rezept-Form</title>
    </head>
    <body>
        <h1>Neues Rezept</h1>
        <form action="api/recipe" method="POST">
            Id: <input name="id" type="input"/><br/>
            Rezeptname: <input name="title" type="input"/><br/>
            Beschreibung: <input name="description" type="input"/><br/>
            <button type="submit">Senden</button>
        </form>
<!--        
        <h1>Login-Form</h1>
        <form action="auth/login" method="post">
            Email: <input name="email" type="email"/><br/>
            Passwort: <input name="password" type="password"/><br/>
            <button type="submit">Login</button>
        </form>
-->
    </body>
</html>