<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmsService
{
    private HttpClientInterface $httpClient;
    private const API_URL = 'https://api.brevo.com/v3/transactionalSMS/sms';
    // Remplacez cette valeur par votre vraie clé API Brevo pour l'envoi de SMS
    protected string $brevoSmsApiKey = '';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Envoie un SMS via l'API Brevo.
     *
     * Paramètres attendus dans $params :
     * - sender : (string) Le nom ou numéro de l'expéditeur
     * - recipient : (string) Le numéro du destinataire
     * - content : (string) Le contenu du message
     * - tag (optionnel) : (string) Une étiquette pour le message
     *
     * @param array $params
     * @return array La réponse de l'API ou un tableau contenant une clé 'error'
     *
     * @throws \InvalidArgumentException Si un paramètre obligatoire manque ou est invalide.
     */
    public function sendSms(array $params): array
    {
        $this->validateParams($params);

        $recipient = $this->validateAndFormatRecipient($params['recipient']);
        if (!$recipient) {
            return ['error' => 'Invalid recipient number: ' . $params['recipient']];
        }

        $body = $this->prepareRequestBody($params['sender'], $recipient, $params);

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'json' => $body,
                'headers' => [
                    'accept'       => 'application/json',
                    'api-key'      => $this->brevoSmsApiKey,
                    'content-type' => 'application/json',
                ],
            ]);

            return $response->toArray();
        } catch (\Throwable $e) {
            return ['error' => 'Failed to send SMS: ' . $e->getMessage()];
        }
    }

    /**
     * Valide les paramètres nécessaires pour envoyer un SMS.
     *
     * @param array $params
     * @throws \InvalidArgumentException
     */
    private function validateParams(array $params): void
    {
        if (empty($params['sender'])) {
            throw new \InvalidArgumentException('Invalid sender: sender is required.');
        }
        if (empty($params['recipient'])) {
            throw new \InvalidArgumentException('Invalid recipient: recipient is required.');
        }
        if (empty($params['content'])) {
            throw new \InvalidArgumentException('Invalid content: content is required.');
        }
    }

    /**
     * Valide et formate le numéro de téléphone du destinataire.
     *
     * Le numéro est converti au format international français si possible.
     *
     * @param string $number
     * @return string|null Le numéro formaté ou null si invalide.
     */
    private function validateAndFormatRecipient(string $number): ?string
    {
        // Retirer tous les caractères autres que chiffres et +
        $number = preg_replace('/[^0-9+]/', '', $number);

        if (preg_match('/^0(6|7)\d{8}$/', $number)) {
            return '+33' . substr($number, 1);
        } elseif (preg_match('/^\+33(6|7)\d{8}$/', $number)) {
            return $number;
        } elseif (preg_match('/^33(6|7)\d{8}$/', $number)) {
            return '+' . $number;
        } elseif (preg_match('/^(6|7)\d{8}$/', $number)) {
            return '+33' . $number;
        }

        return null;
    }

    /**
     * Prépare le corps de la requête pour l'API SMS.
     *
     * @param string $sender
     * @param string $recipient
     * @param array $params
     * @return array
     */
    private function prepareRequestBody(string $sender, string $recipient, array $params): array
    {
        $body = [
            'sender'    => $sender,
            'recipient' => $recipient,
            'content'   => $params['content'],
            'type'      => 'transactional', // Ou tout autre type requis par votre API
        ];

        if (isset($params['tag'])) {
            $body['tag'] = $params['tag'];
        }

        return $body;
    }
}
