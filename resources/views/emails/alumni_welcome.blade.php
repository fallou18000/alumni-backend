<h2>Bienvenue {{ $user->first_name }}</h2>

<p>Votre compte a été validé </p>

<p>Cliquez sur le bouton ci-dessous pour définir votre mot de passe :</p>

<a href="{{ $url }}" style="
    display:inline-block;
    padding:10px 20px;
    background:#2563eb;
    color:white;
    text-decoration:none;
    border-radius:5px;
">
    Définir mon mot de passe
</a>

<p> Ce lien expire après un certain temps.</p>