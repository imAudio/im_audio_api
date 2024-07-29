<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 600px;
            overflow: hidden;
        }
        .email-header {
            background-color: #556F85;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }
        .email-body {
            padding: 20px;
            color: #333f46;
        }
        .email-footer {
            background-color: #f4f4f4;
            color: #777777;
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }
        .code-container {
            background-color: #C6D7DB;
            border-radius: 4px;
            color: #333f46;
            font-size: 20px;
            margin: 20px 0;
            padding: 10px;
            text-align: center;
        }
        .button {
            background-color: #556F85;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            cursor: pointer;
            font-size: 16px;
            padding: 10px 20px;
            text-decoration: none;
        }
        .button:hover {
            background-color: #4a5f73;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        Réinitialisation de mot de passe
    </div>
    <div class="email-body">
        <p>Bonjour,</p>
        <p>Vous avez demandé à réinitialiser votre mot de passe. Veuillez utiliser le code secret ci-dessous pour compléter le processus de réinitialisation.</p>
        <div class="code-container">
            {{ $data['message'] }}
        </div>
        <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail.</p>
        <p>Cordialement,</p>
        <p>L'équipe de support</p>
        <a href="#" class="button">Réinitialiser le mot de passe</a>
    </div>
    <div class="email-footer">
        &copy; 2024 Votre Société. Tous droits réservés.
    </div>
</div>
</body>
</html>
