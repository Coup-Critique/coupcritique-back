<?php

namespace App\Entity\Interfaces;

use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

interface CommentParentInterface
{
    public function getComments(): Collection;
    public function addComment(Comment $comment);
    public function removeComment(Comment $comment);

    public function getUser(): ?User;
}
