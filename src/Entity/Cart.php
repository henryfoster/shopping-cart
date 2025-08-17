<?php

namespace App\Entity;

use App\Entity\Trait\TimeStampable;
use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    use TimeStampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'cart', cascade: ['persist'], orphanRemoval: true)]
    private Collection $cartItems;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
        $this->initCreatedAtAndUpdatedAtValues();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): static
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setCart($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        $this->cartItems->removeElement($cartItem);

        return $this;
    }

    public function getTotalPrice(): int
    {
        return $this->cartItems->reduce(
            func: fn (int $initial, CartItem $cartItem) => $initial += $cartItem->getTotalCartItemPrice(),
            initial: 0,
        );
    }

    public function getTotalAmount(): int
    {
        return $this->cartItems->reduce(
            func: fn (int $initial, CartItem $cartItem) => $initial += $cartItem->getAmount(),
            initial: 0,
        );
    }

    public function findItemByProductId(int $productId): ?CartItem
    {
        return $this->cartItems->findFirst(
            fn (int $key, CartItem $cartItem) => $productId === $cartItem->getProduct()->getId(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'cartItems' => $this->cartItems->map(fn (CartItem $cartItem) => $cartItem->toArray()),
            'totalCartPriceInCent' => $this->getTotalPrice(),
            'uniqueItemsInCart' => $this->cartItems->count(),
            'totalItemsInCart' => $this->getTotalAmount(),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
