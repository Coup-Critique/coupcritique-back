<?php

namespace App\Validator\Constraints;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TeamTagConstraintValidator extends ConstraintValidator
{
    private readonly User $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param Team $team
     */
    public function validate($team, Constraint $constraint): void
    {
        if (!$constraint instanceof TeamTagConstraint) {
            throw new UnexpectedTypeException($constraint, TeamTagConstraint::class);
        }
        if (!$team instanceof Team) {
            throw new UnexpectedTypeException($team, Team::class);
        }

        // To counter lazy loading
        // $tier = $this->tierRepository->findOneById($team->getTier()->getId());

        $counter = 0;
        foreach ($team->getTags() as $tag) {
            if (!$this->user->getIsModo() && $tag->getIsModo()) {
                $this->context->buildViolation($constraint->missingRightsMessage)
                    ->setParameters(['{{ tag }}' => $tag->getName()])
                    ->atPath("tags")
                    ->addViolation();
            }
            if ($tag->getSortOrder() === 2) {
                $counter++;
            }
        }

        if ($counter > 1) {
            $this->context->buildViolation($constraint->tooMuchMessage)
                ->atPath("tags")
                ->addViolation();
        }
    }
}
