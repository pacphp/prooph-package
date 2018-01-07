<?php
declare(strict_types=1);

namespace Fixtures;

use DateTime;

class Test
{
    /** @var string */
    private $id;
    /** @var string */
    private $message;
    /** @var DateTime */
    private $postDate;
    /** @var string */
    private $user;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Test
    {
        $this->id = $id;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): Test
    {
        $this->message = $message;

        return $this;
    }

    public function getPostDate(): DateTime
    {
        return $this->postDate;
    }

    public function setPostDate(DateTime $postDate): Test
    {
        $this->postDate = $postDate;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): Test
    {
        $this->user = $user;

        return $this;
    }
}