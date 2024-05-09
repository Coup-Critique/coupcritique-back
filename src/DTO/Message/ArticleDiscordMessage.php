<?php

namespace App\DTO\Message;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Actuality;
use App\Entity\Guide;
use App\Entity\Tournament;
use App\Entity\CircuitTour;
use App\Entity\CircuitArticle;
use App\Entity\User;

class ArticleDiscordMessage extends AbstractDiscordMessage
{
    public function __construct(
        string $domain,
        string $method,
        User $user,
        protected AbstractArticle $entity
    ) {
        parent::__construct($domain, $method, $user);
    }

    public function getSubject(): string
    {
        if ($this->entity instanceof Actuality) {
            $designation = "de l'actualité";
        } elseif ($this->entity instanceof Guide) {
            $designation = "du guide";
        } elseif ($this->entity instanceof CircuitArticle) {
            $designation = "de l'article";
        } else /* if ($this->entity instanceof Tournament || $this->entity instanceof CircuitTour) */ {
            $designation = "du tournois";
        }
        return $this->getAction() . " " . $designation . " : " . $this->entity->getTitle();
    }

    public function getUrl(): string
    {
        $endPoint = $this->getEndPoint();
        return "{$this->domain}/entity/$endPoint/{$this->entity->getId()}";
    }

    public function getEndPoint(): string
    {
        if ($this->entity instanceof Actuality) {
            return "actualities";
        } elseif ($this->entity instanceof Guide) {
            return "guides";
        } elseif ($this->entity instanceof Tournament) {
            return "tournaments";
        } else if ($this->entity instanceof CircuitTour) {
            return "circuit-tours";
        } else if ($this->entity instanceof CircuitArticle) {
            return "circuit-article";
        }
        throw new \Exception("Unrecognized entity.");
    }

    public function getContent(): string
    {
        return $this->limitContent(html_entity_decode(strip_tags($this->entity->getDescription())));
    }
}
