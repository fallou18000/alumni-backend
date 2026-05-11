<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Nouvelle inscription alumni</h2>

<p>Un nouvel utilisateur attend validation :</p>

<ul>
    <li>Nom : {{ $user->first_name }} {{ $user->last_name }}</li>
    <li>Email : {{ $user->email }}</li>
</ul>

<p>Veuillez le valider depuis le dashboard admin.</p>
</body>
</html>