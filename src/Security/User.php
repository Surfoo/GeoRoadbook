<?php

namespace App\Security;

use Geocaching\Enum\MembershipType;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private int $userId;
    private string $username;
    private string $referenceCode;
    private ?string $avatarUrl = null;
    private string $membershipLevelId;
    private \DateTimeInterface $joinedDateUtc;
    private AccessToken $credentials;

    private array $roles = [];

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getReferenceCode(): ?string
    {
        return $this->referenceCode;
    }

    public function setReferenceCode(string $referenceCode): self
    {
        $this->referenceCode = $referenceCode;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getMembershipLevelId(): ?string
    {
        return $this->membershipLevelId;
    }

    public function setMembershipLevelId(string $membershipLevelId): self
    {
        $this->membershipLevelId = $membershipLevelId;

        return $this;
    }

    public function getJoinedDateUtc(): \DateTimeInterface
    {
        return $this->joinedDateUtc;
    }

    public function setJoinedDateUtc(\DateTimeInterface $joinedDateUtc): self
    {
        $this->joinedDateUtc = $joinedDateUtc;

        return $this;
    }

    public function getCredentials(): ?AccessToken
    {
        return $this->credentials;
    }

    public function setCredentials(string $accessToken, string $refreshToken, int $expires): self
    {
        $this->credentials = new AccessToken([
            'access_token'      => $accessToken,
            'refresh_token'     => $refreshToken,
            'expires'           => $expires,
            'resource_owner_id' => $this->getUserId(),
        ]);

        return $this;
    }

    public function isPremium(): bool
    {
        return $this->getMembershipLevelId() == MembershipType::getId('Premium');
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
