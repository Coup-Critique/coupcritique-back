<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:list'])]
    protected $id;

    #[CustomAssert\TextConstraint(
        message: "Ce commentaire n'est pas acceptable car il contient le ou les mots : {{ banWords }}."
    )]
    #[ORM\Column(type: 'text', length: 3000)]
    #[Groups(['read:list', 'insert:comment'])]
    #[Assert\NotNull(message: 'Un message est requis.')]
    #[Assert\Length(max: 3000, maxMessage: 'Le commentaire peut faire au maximum 3000 caractères.')]
    protected $content;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:list'])]
    protected $user;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'originalOne', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['read:list'])]
    protected $replies;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:list'])]
    protected $date_creation;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['read:list'])]
    protected $deleted;

    /**
     * @var int $approval
     */
    #[Groups(['read:list'])]
    protected ?int $approval = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:list'])]
    protected ?bool $approved_by_author = false;

    #[ORM\ManyToOne(targetEntity: Comment::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: true)]
    private $originalOne;

    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'comment', orphanRemoval: true, cascade: ['persist', 'remove'])]
    protected $votes;

    /** 
     * @var Vote|null $own_user_vote
     */
    #[Groups(['read:list'])]
    protected ?Vote $own_user_vote = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'comments')]
    private $team;

    #[ORM\ManyToOne(targetEntity: Actuality::class, inversedBy: 'comments')]
    private $actuality;

    #[ORM\ManyToOne(targetEntity: Guide::class, inversedBy: 'comments')]
    private $guide;

    #[ORM\ManyToOne(targetEntity: Tournament::class, inversedBy: 'comments')]
    private $tournament;

    #[ORM\ManyToOne(targetEntity: CircuitTour::class, inversedBy: 'comments')]
    private $circuitTour;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDeleted(): ?\DateTimeInterface
    {
        return $this->deleted;
    }

    public function setDeleted(?\DateTimeInterface $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return null|Comment 
     */
    public function getOrignalOne(): ?Comment
    {
        return $this->originalOne;
    }

    /**
     * @param null|Comment $originalOne 
     * @return Comment 
     */
    public function setOrignalOne(?Comment $originalOne): self
    {
        $this->originalOne = $originalOne;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(Comment $reply): self
    {
        if (!$this->replies->contains($reply)) {
            $this->replies[] = $reply;
            $reply->setOrignalOne($this);
        }

        return $this;
    }

    public function removeReply(Comment $reply): self
    {
        if ($this->replies->removeElement($reply)) {
            // set the owning side to null (unless already changed)
            if ($reply->getOrignalOne() === $this) {
                $reply->setOrignalOne(null);
            }
        }

        return $this;
    }

    public function getApproval(): ?int
    {
        if (is_null($this->approval)) {
            $approval = 0;
            foreach ($this->votes as $vote) {
                if ($vote->getPositiv()) {
                    $approval++;
                } else {
                    $approval--;
                }
            }
            $this->approval = $approval;
        }
        return $this->approval;
    }

    public function setApproval(?int $approval): self
    {
        $this->approval = $approval;

        return $this;
    }

    public function getApprovedByAuthor(): ?bool
    {
        // if (is_null($this->approved_by_author)) {
        //     $vote = $this->getUserVote($this->user);
        //     if (!is_null($vote) && $vote->getPositiv()) {
        //         $this->approved_by_author = true;
        //     }
        // }
        return $this->approved_by_author;
    }

    public function setApprovedByAuthor(?bool $approved_by_author): self
    {
        $this->approved_by_author = $approved_by_author;

        return $this;
    }

    /**
     * @return Collection|Vote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            // init approval
            $this->getApproval();
            // Prevent from user to vote two times on the same comment.
            $userVote = $this->getUserVote($vote->getUser());
            if (!is_null($userVote)) {
                throw new \Exception('A user can\'t vote several times on the same comment.');
            }
            $this->votes[] = $vote;

            $vote->setComment($this);
            if ($vote->getPositiv()) {
                $this->approval++;

                if ($vote->getUser()->getId() === $this->user->getId()) {
                    $this->approved_by_author = true;
                }
            } else {
                $this->approval--;
            }
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        // init approval
        $this->getApproval();
        if ($this->votes->removeElement($vote)) {
            if ($vote->getComment() === $this) {
                // orphanRemoval will remove it
                $vote->setComment(null);
            }

            if ($vote->getPositiv()) {
                $this->approval--;

                if ($vote->getUser()->getId() === $this->user->getId()) {
                    $this->approved_by_author = false;
                }
            } else {
                $this->approval++;
            }
        }

        return $this;
    }

    /** 
     * @return Vote|null 
     */
    public function getUserVote(User $user): ?Vote
    {
        foreach ($this->votes as $vote) {
            if ($vote->getUser()->getId() === $user->getId()) {
                return $vote;
            }
        }
        return null;
    }

    // to init own_user_vote $this->setOwnUserVote($this->getUserVote($Controller->getUser()))
    public function getOwnUserVote(): ?Vote
    {
        return $this->own_user_vote;
    }

    public function setOwnUserVote(?Vote $own_user_vote): self
    {
        $this->own_user_vote = $own_user_vote;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getActuality(): ?Actuality
    {
        return $this->actuality;
    }

    public function setActuality(?Actuality $actuality): self
    {
        $this->actuality = $actuality;
        return $this;
    }

    public function getGuide(): ?Guide
    {
        return $this->guide;
    }

    public function setGuide(?Guide $guide): self
    {
        $this->guide = $guide;
        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;
        return $this;
    }

    public function getCircuitTour(): ?CircuitTour
    {
        return $this->circuitTour;
    }

    public function setCircuitTour(?CircuitTour $circuitTour): self
    {
        $this->circuitTour = $circuitTour;
        return $this;
    }
}
