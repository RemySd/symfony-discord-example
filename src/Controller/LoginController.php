<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Wohali\OAuth2\Client\Provider\Discord;

class LoginController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/login", name="login")
     */
    public function index()
    {
        return $this->render('login/index.html.twig');
    }

    /**
     * @Route("/login-discord", name="login_discord")
     */
    public function discordLogin()
    {
        $discordClient = new Discord([
            'clientId' => $_ENV['DISCORD_CLIENT_ID'],
            'clientSecret' => $_ENV['DISCORD_CLIENT_SECRET'],
            'redirectUri' => $_ENV['DISCORD_REDIRECT_URI_LOGIN']
        ]);

        $authUrl = $discordClient->getAuthorizationUrl([
            'scope' => ['identify']
        ]);

        return new RedirectResponse($authUrl);
    }
}
