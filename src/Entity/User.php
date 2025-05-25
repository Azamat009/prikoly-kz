<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $uuid;

    #[ORM\ManyToOne(inversedBy: 'user_id')]
    private ?Reaction $reaction = null;

    #[ORM\ManyToOne(inversedBy: 'userId')]
    private ?Report $report = null;

    /**
     * @var Collection<int, Feedback>
     */
    #[ORM\OneToMany(targetEntity: Feedback::class, mappedBy: 'userId')]
    private Collection $feedback;

    public function __construct()
    {
        $this->feedback = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getReaction(): ?Reaction
    {
        return $this->reaction;
    }

    public function setReaction(?Reaction $reaction): static
    {
        $this->reaction = $reaction;

        return $this;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return Collection<int, Feedback>
     */
    public function getFeedback(): Collection
    {
        return $this->feedback;
    }

    public function addFeedback(Feedback $feedback): static
    {
        if (!$this->feedback->contains($feedback)) {
            $this->feedback->add($feedback);
            $feedback->setUserId($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): static
    {
        if ($this->feedback->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getUserId() === $this) {
                $feedback->setUserId(null);
            }
        }

        return $this;
    }
}
