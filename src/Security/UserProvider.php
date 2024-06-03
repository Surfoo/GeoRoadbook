<?php

namespace App\Security;

use App\Api\Geocaching;
use App\Dao\UserDao;
use Geocaching\Enum\MembershipType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        // private readonly UserDao $userDao,
        // private readonly Geocaching $api,
        private readonly LoggerInterface $apiLogger)
    {
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me. If you're not using these features, you do not
     * need to implement this method.
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Load a User object from your data source or throw UserNotFoundException.
        // The $identifier argument is whatever value is being returned by the
        // getUserIdentifier() method in your User class.
        throw new \Exception('TODO: fill in loadUserByIdentifier() inside '.__FILE__);
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UsernameNotFoundException if the user no longer exists.
        $user = $this->loadUserByIdentifier($user->getUserIdentifier());

        // Le type de compte a été récupéré depuis la base de données,
        // le role correspondant est set pour l'utilisateur connecté sur le site.
        // switch ($user->getMembershipLevelId()) {
        //     case MembershipType::getId('Premium'):
        //         $user->setRoles(['ROLE_PREMIUM']);
        //         break;
        //     case MembershipType::getId('Basic'):
        //         $user->setRoles(['ROLE_BASIC']);
        //         break;
        // }

        // Si le token de l'utilisateur n'a pas expiré, rien n'est fait
        // if (!$user->getCredentials()->hasExpired()) {
        //     return $user;
        // }

        // Si on arrive là, c'est que le token a expiré
        // Utilisation de token de l'utilisateur pour l'API et refresh du token
        // $this->api->setUser($user)->refreshToken();

        // Renvoie de l'objet User attendu, que le refresh ai fonctionné ou pas.
        return $user;
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
