<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller\Dashboard\Api\Ai;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AgenceImmobiliereMesBiensAiController extends AbstractController
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/responses';
    private const OPENAI_MODEL = 'gpt-4.1-mini';
    private const OPENAI_MAX_OUTPUT_TOKENS = 700;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/generate-description-ai', name: 'agence_immobiliere_mes_biens_generate_description_ai', methods: ['POST'])]
    public function generateDescriptionAi(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => 'Requête invalide.',
            ], 400);
        }

        $payload = json_decode($request->getContent(), true);

        if (!\is_array($payload)) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides.',
            ], 400);
        }

        $csrfToken = (string) ($payload['csrfToken'] ?? '');

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('generate_ai_description', $csrfToken))) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], 403);
        }

        $apiKey = $_ENV['OPENAI_API_KEY'] ?? null;

        if (!$apiKey) {
            return $this->json([
                'success' => false,
                'message' => 'OPENAI_API_KEY manquante dans le fichier .env.local.',
            ], 500);
        }

        $title = mb_trim((string) ($payload['title'] ?? ''));
        $currentDescription = mb_trim((string) ($payload['currentDescription'] ?? ''));

        $payloadFormData = $payload['formData'] ?? [];
        $formContext = $this->buildPropertyContextFromPayload(
            \is_array($payloadFormData) ? $payloadFormData : []
        );

        $sessionContext = $this->buildPropertyContextFromSession($request);

        $propertyContext = $this->mergeContexts($formContext, $sessionContext);

        if ('' === $propertyContext && '' === $title && '' === $currentDescription) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune donnée du formulaire n’a été transmise à l’IA.',
            ], 400);
        }

        $prompt = $this->buildPrompt(
            title: $title,
            currentDescription: $currentDescription,
            propertyContext: $propertyContext,
        );

        $openAiPayload = [
            'model' => self::OPENAI_MODEL,
            'input' => [
                [
                    'role' => 'system',
                    'content' => <<<SYSTEM
Tu es un assistant spécialisé dans la rédaction d'annonces immobilières professionnelles.

Règles absolues :
- Tu utilises uniquement les informations réellement fournies.
- Tu n'inventes jamais une surface, une ville, une adresse, un prix, une pièce, une chambre, une salle de bains, un équipement ou une localisation.
- Tu n'écris jamais de variables entre accolades.
- Tu ne dis jamais qu'aucune information n'a été fournie.
- Si peu d'informations sont disponibles, tu rédiges une description courte et sobre uniquement avec les informations disponibles.
- Tu ne demandes jamais à l'utilisateur de préciser les caractéristiques.
- Tu réponds uniquement avec la description finale publiable.
SYSTEM,
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_output_tokens' => self::OPENAI_MAX_OUTPUT_TOKENS,
        ];

        try {
            $response = $this->httpClient->request(
                'POST',
                self::OPENAI_API_URL,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $openAiPayload,
                    'timeout' => 60,
                ]
            );

            $data = $response->toArray(false);

            if (isset($data['error'])) {
                return $this->json([
                    'success' => false,
                    'message' => $data['error']['message'] ?? 'Erreur lors de la génération IA.',
                ], 500);
            }

            $description = $this->extractGeneratedText($data);

            if ('' === $description) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune description générée.',
                    'debug' => $data,
                ], 500);
            }

            if (
                str_contains($description, '{{')
                || str_contains($description, '}}')
                || str_contains(mb_strtolower($description), 'aucune information')
                || str_contains(mb_strtolower($description), 'merci de préciser')
            ) {
                return $this->json([
                    'success' => false,
                    'message' => 'La description générée est invalide. Veuillez réessayer après avoir renseigné quelques champs du bien.',
                ], 500);
            }

            return $this->json([
                'success' => true,
                'description' => $description,
            ]);
        } catch (\Throwable) {
            return $this->json([
                'success' => false,
                'message' => 'Impossible de générer la description pour le moment.',
            ], 500);
        }
    }

    private function buildPrompt(
        string $title,
        string $currentDescription,
        string $propertyContext,
    ): string {
        return <<<PROMPT
Rédige une description professionnelle, fluide, naturelle et directement publiable pour une annonce immobilière.

Titre saisi par l'utilisateur :
{$title}

Description déjà saisie par l'utilisateur :
{$currentDescription}

Données réellement disponibles sur le bien :
{$propertyContext}

Règles de rédaction :
- Écris en français.
- Ne mets pas de titre.
- Ne mets pas de liste à puces.
- Ne mets pas de guillemets.
- Ne parle jamais à la première personne.
- Ne donne jamais l'adresse exacte complète.
- Ne mentionne pas un numéro de rue.
- Ne mentionne pas une vue, une luminosité, un calme, un standing, une proximité commerces, écoles, transports ou centre-ville si ce n'est pas explicitement indiqué.
- Ne promets jamais une rentabilité.
- Évite les formulations trop commerciales ou exagérées.
- Le ton doit être professionnel, premium, rassurant et naturel.
- Si peu d'informations sont disponibles, rédige un texte court et sobre.
- Longueur cible : entre 1000 et 1500 caractères selon la quantité d'informations disponibles.
- N'invente aucune donnée manquante.
- Ne recopie jamais de variable entre accolades.
- Ne dis jamais qu'aucune information n'a été fournie.
- Ne demande jamais à l'utilisateur de préciser des informations.

Consigne finale :
Rédige uniquement la description finale publiable.
PROMPT;
    }

    /**
     * @param array<string, mixed> $formData
     */
    private function buildPropertyContextFromPayload(array $formData): string
    {
        $lines = [];

        foreach ($formData as $field => $value) {
            if (!\is_scalar($value) && null !== $value) {
                continue;
            }

            $fieldValue = mb_trim((string) $value);

            if ('' === $fieldValue) {
                continue;
            }

            if ($this->shouldIgnoreField((string) $field)) {
                continue;
            }

            $cleanField = $this->cleanFormFieldName((string) $field);

            $lines[] = \sprintf(
                '- %s : %s',
                $this->humanizeFieldName($cleanField),
                $fieldValue
            );
        }

        return implode("\n", array_unique($lines));
    }

    private function buildPropertyContextFromSession(Request $request): string
    {
        $session = $request->getSession();

        $sessionKeys = [
            'mes_biens_step_1',
            'mes_biens_step_2',
            'mes_biens_step_3',
            'mes_biens_step_4',
            'mes_biens_step_5',
            'mes_biens_step_6',
            'mes_biens_step_7',
            'mes_biens_step_8',
        ];

        $lines = [];

        foreach ($sessionKeys as $sessionKey) {
            if (!$session->has($sessionKey)) {
                continue;
            }

            $value = $session->get($sessionKey);

            if (!\is_array($value)) {
                continue;
            }

            foreach ($this->flattenArray($value) as $field => $fieldValue) {
                if (null === $fieldValue || '' === $fieldValue) {
                    continue;
                }

                if ($this->shouldIgnoreField($field)) {
                    continue;
                }

                $cleanField = $this->cleanFormFieldName($field);

                $lines[] = \sprintf(
                    '- %s : %s',
                    $this->humanizeFieldName($cleanField),
                    $fieldValue
                );
            }
        }

        return implode("\n", array_unique($lines));
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, string>
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = '' === $prefix
                ? (string) $key
                : $prefix.'.'.$key;

            if (\is_array($value)) {
                $result += $this->flattenArray($value, $newKey);
                continue;
            }

            if (\is_scalar($value) || null === $value) {
                $result[$newKey] = mb_trim((string) $value);
            }
        }

        return $result;
    }

    private function mergeContexts(string ...$contexts): string
    {
        $lines = [];

        foreach ($contexts as $context) {
            foreach (explode("\n", $context) as $line) {
                $line = mb_trim($line);

                if ('' === $line) {
                    continue;
                }

                $lines[] = $line;
            }
        }

        return implode("\n", array_unique($lines));
    }

    private function cleanFormFieldName(string $field): string
    {
        $field = preg_replace('/^.*\./', '', $field) ?? $field;

        if (preg_match('/\[([^\]]+)\]$/', $field, $matches)) {
            return $matches[1];
        }

        return $field;
    }

    private function shouldIgnoreField(string $field): bool
    {
        $ignoredFields = [
            '_token',
            'token',
            'csrf',
            'csrfToken',
            'submit',
            'save',
            'next',
            'previous',
            'referenceInterne',
            'propertyImages',
            'imageFile',
        ];

        foreach ($ignoredFields as $ignoredField) {
            if (str_contains($field, $ignoredField)) {
                return true;
            }
        }

        return false;
    }

    private function humanizeFieldName(string $field): string
    {
        $labels = [
            'typeBien' => 'Type de bien',
            'typeTransaction' => 'Type de transaction',
            'categoryBien' => 'Type de bien',
            'categoryBienTransaction' => 'Type de transaction',

            'pays' => 'Pays',
            'adresse' => 'Adresse',
            'address' => 'Adresse',
            'codePostal' => 'Code postal',
            'postalCode' => 'Code postal',
            'ville' => 'Ville',
            'city' => 'Ville',

            'surface' => 'Surface',
            'surfaceHabitable' => 'Surface habitable',
            'livingArea' => 'Surface habitable',
            'nombrePieces' => 'Nombre de pièces',
            'rooms' => 'Nombre de pièces',
            'nombreChambres' => 'Nombre de chambres',
            'bedrooms' => 'Nombre de chambres',
            'sallesDeBains' => 'Nombre de salles de bains',
            'bathrooms' => 'Nombre de salles de bains',

            'caracteristique' => 'Caractéristiques',
            'caracteristiques' => 'Caractéristiques',
            'features' => 'Caractéristiques',

            'dpe' => 'DPE',
            'ges' => 'GES',
            'dpeMax' => 'DPE maximum',
            'dpeMin' => 'DPE minimum',
            'dpeLettre' => 'Classe énergétique',
            'dateIndexationEnergie' => 'Date d’indexation énergie',

            'titreDuLogement' => 'Titre du logement',
            'descriptionLogement' => 'Description du logement',

            'prix' => 'Prix',
            'price' => 'Prix',
            'montantDepotDeGarantie' => 'Dépôt de garantie',
            'montantLoyerHorsCharge' => 'Loyer hors charges',
            'montantDesCharges' => 'Charges',
        ];

        return $labels[$field] ?? $this->formatUnknownFieldName($field);
    }

    private function formatUnknownFieldName(string $field): string
    {
        $field = preg_replace('/([a-z])([A-Z])/', '$1 $2', $field) ?? $field;
        $field = str_replace(['_', '-'], ' ', $field);
        $field = mb_strtolower($field);

        return ucfirst($field);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractGeneratedText(array $data): string
    {
        if (isset($data['output_text']) && \is_string($data['output_text'])) {
            return mb_trim($data['output_text']);
        }

        $texts = [];

        foreach ($data['output'] ?? [] as $output) {
            foreach ($output['content'] ?? [] as $content) {
                if (isset($content['text']) && \is_string($content['text'])) {
                    $texts[] = mb_trim($content['text']);
                }
            }
        }

        return mb_trim(implode("\n", array_filter($texts)));
    }
}
