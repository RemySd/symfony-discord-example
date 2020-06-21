<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Wohali\OAuth2\Client\Provider\Discord;

class DiscordAuthenticator extends AbstractGuardAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'login';

    /** @var UrlGeneratorInterface  */
    private $urlGenerator;

    /** @var Discord  */
    private $discordClient;

    public function __construct(UrlGeneratorInterface $urlGenerator) {
        $this->discordClient = new Discord([
            'clientId' => $_ENV['DISCORD_CLIENT_ID'],
            'clientSecret' => $_ENV['DISCORD_CLIENT_SECRET'],
            'redirectUri' => $_ENV['DISCORD_REDIRECT_URI_LOGIN']
        ]);

        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route') &&
            $request->query->has('code');
    }

    public function getCredentials(Request $request)
    {
        $token = $this->discordClient->getAccessToken('authorization_code', [
            'code' => $request->query->get('code')
        ]);

        $token->getToken();

        return $this->discordClient->getResourceOwner($token);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials->getUsername());
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    /**
     * @inheritDoc
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
