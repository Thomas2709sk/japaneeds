<?php

namespace App\Security\Voter;

use App\Entity\Reservations;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

use function PHPUnit\Framework\returnSelf;

class ReservationsVoter extends Voter
{
    public const EDIT = 'RESERV_EDIT';

    public const DELETE = 'RESERV_DELETE';

    public function __construct(private readonly Security $security)
    {
        
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Reservations;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if(!$user instanceof UserInterface) {
            return false;
        }

        if($this->security->isGranted('ROLE_ADMIN')) return true;

        switch ($attribute) {
            case self::EDIT;
            case self::DELETE;
                // On vérifie si user est l'auteur de la réservation
                return $this->isOwner($subject, $user);

        }
        return false;
    }

    private function isOwner(Reservations $reservation, $user): bool
    {
        return $user === $reservation->getGuide()->getUser(); // Vérifier si le guide lié à la réservation est le même que l'utilisateur connecté
        return $user === $reservation->getUsers();
    }
}