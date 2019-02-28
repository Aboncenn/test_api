<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $apikey;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Place", mappedBy="user")
     */
    private $places;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Category", mappedBy="user")
     */
    private $categories;

    public function __construct()
    {
        $this->places = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getApikey(): ?string
    {
        return $this->apikey;
    }

    public function setApikey(string $apikey): self
    {
        $this->apikey = $apikey;

        return $this;
    }

    /**
     * @return Collection|Place[]
     */
    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places[] = $place;
            $place->setUser($this);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        if ($this->places->contains($place)) {
            $this->places->removeElement($place);
            // set the owning side to null (unless already changed)
            if ($place->getUser() === $this) {
                $place->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getUser() === $this) {
                $category->setUser(null);
            }
        }

        return $this;
    }
}
