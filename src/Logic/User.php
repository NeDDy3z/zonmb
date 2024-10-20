<?php
declare(strict_types=1);

namespace Zonmb\Logic;

class User {
    private $username;
    private $image;
    private $email;
    private $role;
    private $createdAt;

    public function __construct($username, $image, $email, $role, $createdAt) {
        $this->username = $username;
        $this->image = $image;
        $this->email = $email;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getImage() {
        return $this->image;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRole() {
        return $this->role;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setImage($image) {
        $this->image = $image;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }
}