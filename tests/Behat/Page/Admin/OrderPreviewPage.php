<?php

declare(strict_types=1);

namespace Tests\Sylius\AdminOrderCreationPlugin\Behat\Page\Admin;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use WebDriver\Exception;

final class OrderPreviewPage extends SymfonyPage implements OrderPreviewPageInterface
{
    public function getRouteName(): string
    {
        return 'sylius_admin_order_creation_preview_order';
    }

    public function getTotal(): string
    {
        return str_replace('Order total: ', '', $this->getDocument()->find('css', 'td#total')->getText());
    }

    public function getShippingTotal(): string
    {
        return str_replace('Shipping total: ', '', $this->getDocument()->find('css', 'td#shipping-total')->getText());
    }

    public function hasProduct(string $productName): bool
    {
        return $this->getDocument()->has('css', sprintf('.sylius-product-name:contains("%s")', $productName));
    }

    public function hasPayment(string $paymentName): bool
    {
        return $this->getDocument()->has('css', sprintf('#sylius-payments .item:contains("%s")', $paymentName));
    }

    public function hasConfirmButton(): bool
    {
        return null !== $this->getDocument()->findButton('Confirm');
    }

    public function hasOrderDiscountValidationMessage(string $message): bool
    {
        $orderDiscountValidationMessage = $this
            ->getDocument()
            ->find('css', '#sylius_admin_order_creation_new_order_adjustments .sylius-validation-error')
        ;

        return
            $orderDiscountValidationMessage !== null &&
            $orderDiscountValidationMessage->getText() === $message
        ;
    }

    public function hasItemDiscountValidationMessage(string $productCode, string $message): bool
    {
        $item = $this->getDocument()->find('css', sprintf('table tr:contains("%s") + tr', $productCode));

        return null !== $item->find('css', sprintf('.sylius-validation-error:contains("%s")', $message));
    }

    public function hasLocale(string $localeName): bool
    {
        /** @var NodeElement $localeElement */
        $localeElement = $this->getDocument()->find('css', '#sylius-order-locale-code');

        return strpos($localeElement->getText(), $localeName) !== false;
    }

    public function hasCurrency(string $currencyName): bool
    {
        /** @var NodeElement $localeElement */
        $localeElement = $this->getDocument()->find('css', '#sylius-order-currency');

        return strpos($localeElement->getText(), $currencyName) !== false;
    }

    public function lowerOrderPriceBy(string $discount): void
    {
        $this->getDocument()->waitFor(10, function() {
            return $this->getDocument()->has('css', '#sylius_admin_order_creation_new_order_adjustments');
        });
        $discountCollection = $this->getDocument()->find('css', '#sylius_admin_order_creation_new_order_adjustments');

        $this->getDocument()->waitFor(10, function () use ($discountCollection) {
            try {
                $discountCollection->clickLink('Add discount');

                return true;
            } catch (Exception $exception) {
                return false;
            }
        });
        $this->getDocument()->waitFor(10, function () use ($discountCollection) {
            return $discountCollection->has('css', '[data-form-collection="item"]');
        });

        $discountCollection->fillField('Order discount', $discount);
    }

    public function lowerItemWithProductPriceBy(string $productCode, string $discount): void
    {
        $this->getDocument()->waitFor(10, function () use ($productCode) {
            return $this->getDocument()->has('css', sprintf('table tr:contains("%s") + tr', $productCode));
        });
        $item = $this->getDocument()->find('css', sprintf('table tr:contains("%s") + tr', $productCode));
        $this->getDocument()->waitFor(10, function () use ($item) {
            try {
                $item->clickLink('Add discount');

                return true;
            } catch (Exception $exception) {
                return false;
            }
        });

        $discountCollection = $item->find('css', '[data-form-type="collection"]');

        $this->getDocument()->waitFor(10, function () use ($discountCollection) {
            return $discountCollection->has('css', '[data-form-collection="item"]');
        });

        $discountCollection->fillField('Item discount', $discount);
    }

    public function confirm(): void
    {
        $this->getDocument()->waitFor(10, function () {
            try {
                $confirmButton = $this->getDocument()->findButton('Confirm');

                if ($this->getDriver() instanceof Selenium2Driver) {
                    $confirmButton->focus();
                }

                $confirmButton->press();

                return true;
            } catch (Exception $exception) {
                return false;
            }
        });
    }

    public function goBack(): void
    {
        $this->getDocument()->waitFor(10, function () {
            try {
                $backButton = $this->getDocument()->findButton('Back');

                if ($this->getDriver() instanceof Selenium2Driver) {
                    $backButton->focus();
                }

                $backButton->press();

                return true;
            } catch (Exception $exception) {
                return false;
            }
        });
    }
}
