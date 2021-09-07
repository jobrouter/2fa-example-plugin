<?php

namespace Example\Authentication\Factor;

use JobRouter\Api\User\ApiUser;
use JobRouter\Authentication\AuthenticationFactorFailedException;
use JobRouter\Authentication\Factor\Wizard\ErrorMessage;
use JobRouter\Authentication\Factor\Wizard\InfoMessage;
use JobRouter\Authentication\Factor\Wizard\Message;
use JobRouter\Authentication\Factor\Wizard\NumberField;
use JobRouter\Authentication\Runtime;
use JobRouter\Authentication\AuthenticationFactorInterface;
use Throwable;

class ExampleAuthenticator implements AuthenticationFactorInterface
{
    private array $fields = [];
    private array $messages = [];
    private array $links = [];

    /**
     * @param Runtime $runtime
     *
     * @throws AuthenticationFactorFailedException
     */
    public function execute(Runtime $runtime): void
    {
        $this->ensureUserHasEmailAddress($runtime);

        try {
            ['pin' => $pin, 'expire_date' => $expireDate] = $this->generatePin($runtime);
            $this->sendEmail($runtime->getUser(), $pin);
            $runtime->setSessionVariable('2faPinExpireDate', $expireDate);
            $runtime->setSessionVariable('2faPin', $pin);
        } catch (Throwable $e) {
            $runtime->getLogger()->error($e->getMessage());
            throw new AuthenticationFactorFailedException(CONST_EXAMPLE_AUTHENTICATION_EMAIL_COULD_NOT_BE_SENT, $e->getCode(), $e);
        }

        $this->addContentForLoginPage(
            $runtime,
            new InfoMessage(CONST_EXAMPLE_AUTHENTICATION_INFO_PIN_EMAIL)
        );
    }

    /**
     * @param Runtime $runtime
     *
     * @throws AuthenticationFactorFailedException
     */
    private function ensureUserHasEmailAddress(Runtime $runtime): void
    {
        if (!$runtime->getUser()->getEmail()) {
            $runtime->getLogger()->warning(
                sprintf("No email address for user %s configured. Two-factor authentication is not possible.", $runtime->getUser()->getUserName())
            );
            throw new AuthenticationFactorFailedException(CONST_EXAMPLE_AUTHENTICATION_EMAIL_MISSING);
        }
    }

    private function generatePin(Runtime $runtime): array
    {
        $pin = random_int(100000, 999999);
        $expireDate = time() + 600;

        return [
            'pin' => $pin,
            'expire_date' => $expireDate
        ];
    }

    private function sendEmail(ApiUser $authUser, int $pin)
    {
        // Implement logic to send the provided $pin to $authUser via e-mail
    }

    private function addContentForLoginPage(Runtime $runtime, Message $message): void
    {
        $this->messages = [$message];

        $this->fields = [
            new NumberField('pin', CONST_EXAMPLE_AUTHENTICATION_PIN)
        ];

        $this->links = [
            $runtime->getRepeatFactorLink(CONST_EXAMPLE_AUTHENTICATION_PIN_SEND_AGAIN),
            $runtime->getResetAuthenticationProcessLink(),
        ];
    }

    /**
     * @param Runtime $runtime
     *
     * @throws AuthenticationFactorFailedException
     */
    public function check(Runtime $runtime): void
    {
        $pin = (int)trim($runtime->getRequestParameter('pin'));
        if ($pin !== (int)$runtime->getSessionVariable('2faPin')) {
            $runtime->getLogger()->error('Invalid PIN provided: ' . $pin);
            throw new AuthenticationFactorFailedException(CONST_EXAMPLE_AUTHENTICATION_INVALID_PIN_INFO);
        }

        $pinDate = $runtime->getSessionVariable('2faPinExpireDate');
        if ($pinDate < time()) {
            $runtime->getLogger()->error('PIN is expired: ' . $pin);
            throw new AuthenticationFactorFailedException(CONST_EXAMPLE_AUTHENTICATION_INVALID_PIN_INFO);
        }

        $runtime->deleteSessionVariable('2faPin');
        $runtime->deleteSessionVariable('2faPinExpireDate');
    }

    /**
     * @param Runtime $runtime
     */
    public function handleInvalidFactor(Runtime $runtime): void
    {
        $this->addContentForLoginPage($runtime, new ErrorMessage(CONST_EXAMPLE_AUTHENTICATION_INVALID_PIN_INFO));
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function getConfigurationLabel(): string
    {
        return CONST_EXAMPLE_AUTHENTICATION_LABEL;
    }

    public function isAvailable(): bool
    {
        // Implement logic to check whether mail server is available on the current system
        return true;
    }
}
