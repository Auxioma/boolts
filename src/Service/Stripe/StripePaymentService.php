<?php

declare(strict_types=1);

namespace App\Service\Stripe;

use App\Dto\Subscription\PaymentFailureDetails;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\RateLimitException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

final readonly class StripePaymentService
{
    public function __construct(
        private StripeClient $stripe,
    ) {
    }

    public function retrievePaymentIntent(?string $paymentIntentId): ?PaymentIntent
    {
        if (!\is_string($paymentIntentId) || !str_starts_with($paymentIntentId, 'pi_')) {
            return null;
        }

        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    public function failureDetailsFromPaymentIntent(?PaymentIntent $paymentIntent): PaymentFailureDetails
    {
        if (!$paymentIntent instanceof PaymentIntent) {
            return PaymentFailureDetails::empty();
        }

        $lastPaymentError = \is_object($paymentIntent->last_payment_error ?? null)
            ? $paymentIntent->last_payment_error
            : null;

        return new PaymentFailureDetails(
            failureCode: $this->readString($lastPaymentError, 'code'),
            failureMessage: $this->readString($lastPaymentError, 'message'),
            declineCode: $this->readString($lastPaymentError, 'decline_code'),
            requiresActionType: $this->readString($paymentIntent, 'next_action'),
        );
    }

    public function failureDetailsFromApiException(ApiErrorException $exception): PaymentFailureDetails
    {
        $error = $exception->getError();

        return new PaymentFailureDetails(
            failureCode: $exception->getStripeCode(),
            failureMessage: $exception->getMessage(),
            declineCode: $this->readString($error, 'decline_code'),
            requiresActionType: $this->readString($error, 'type'),
        );
    }

    public function isTemporaryStripeFailure(ApiErrorException $exception): bool
    {
        if ($exception instanceof ApiConnectionException || $exception instanceof RateLimitException) {
            return true;
        }

        $httpStatus = $exception->getHttpStatus();

        return null === $httpStatus || $httpStatus >= 500;
    }

    private function readString(mixed $object, string $property): ?string
    {
        if (!\is_object($object) || !isset($object->{$property})) {
            return null;
        }

        $value = $object->{$property};

        if (\is_string($value) && '' !== $value) {
            return $value;
        }

        return null;
    }
}
