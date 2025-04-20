<?php
// src/Entity/AlertAcknowledgement.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AlertAcknowledgementRepository;

#[ORM\Entity(repositoryClass: AlertAcknowledgementRepository::class)]
#[ORM\Table(name: 'alert_acknowledgement')]
#[ORM\UniqueConstraint(name: 'uq_alert_user', columns: ['alert_id','user_id'])]
class AlertAcknowledgement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AdminAlert::class)]
    #[ORM\JoinColumn(name: 'alert_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private AdminAlert $alert;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $acknowledgedAt;

    public function __construct(AdminAlert $alert, User $user)
    {
        $this->alert          = $alert;
        $this->user           = $user;
        $this->acknowledgedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlert(): AdminAlert
    {
        return $this->alert;
    }
    public function getUser(): User
    {
        return $this->user;
    }
    public function getAcknowledgedAt(): \DateTimeImmutable
    {
        return $this->acknowledgedAt;
    }
}
