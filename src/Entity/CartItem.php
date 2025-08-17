<?php

namespace App\Entity;

use App\Entity\Trait\TimeStampable;
use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CartItem
{
    use TimeStampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $amount;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Cart $cart;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    public function __construct(int $amount, Cart $cart, Product $product)
    {
        $this->amount = $amount;
        $this->cart = $cart;
        $this->product = $product;

        $this->initCreatedAtAndUpdatedAtValues();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): static
    {
        $this->cart = $cart;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getTotalCartItemPrice(): int
    {
        return $this->getProduct()->getPriceInCent() * $this->amount;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->product->getId(),
            'productName' => $this->product->getName(),
            'productPricePerItemInCent' => $this->product->getPriceInCent(),
            'productPriceTotalInCent' => $this->getTotalCartItemPrice(),
            'amount' => $this->amount,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
