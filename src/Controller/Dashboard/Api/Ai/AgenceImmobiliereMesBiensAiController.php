<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller\Dashboard\Api\Ai;

use App\Entity\Property;
use App\Entity\User;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * HTTP controller for module Dashboard / Api / Ai / AgenceImmobiliereMesBiensAiController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereMesBiensAiController extends AbstractController
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/responses';
    private const OPENAI_MODEL = 'gpt-4.1-mini';
    private const OPENAI_MAX_OUTPUT_TOKENS = 700; 

    /**
     * Handles the __construct controller action.
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/generate-description-ai', name: 'agence_immobiliere_mes_biens_generate_description_ai', methods: ['POST'])]
    /**
     * Handles the generateDescriptionAi controller action.
     */
    public function generateDescriptionAi(
        Request $request,
        PropertyRepository $propertyRepository,
    ): JsonResponse {
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

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour générer une description.',
            ], 403);
        }

        $property = $this->findCurrentProperty(
            $request,
            $propertyRepository
        );

        if (!$property instanceof Property) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun bien courant n’a été trouvé en base de données.',
            ], 404);
        }

        if (
            null === $property->getUser()
            || $property->getUser()?->getId() !== $user->getId()
        ) {
            return $this->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas générer une description pour ce bien.',
            ], 403);
        }

        $apiKey = $_ENV['OPENAI_API_KEY'] ?? null;

        if (!$apiKey) {
            return $this->json([
                'success' => false,
                'message' => 'OPENAI_API_KEY manquante dans le fichier .env.local.',
            ], 500);
        }

        $title = $this->cleanContextValue(
            $payload['title'] ?? null
        );

        if ('' === $title) {
            $title = $this->cleanContextValue(
                $property->getTitreDuLogement()
            );
        }

        $currentDescription = $this->cleanContextValue(
            $payload['currentDescription'] ?? null
        );

        if ('' === $currentDescription) {
            $currentDescription = $this->cleanContextValue(
                $property->getDescriptionLogement()
            );
        }

        $propertyContext = $this->buildPropertyContextFromProperty(
            $property
        );

        if ('' === $propertyContext && '' === $title && '' === $currentDescription) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune donnée du bien n’a été trouvée en base de données.',
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

    private function findCurrentProperty(
        Request $request,
        PropertyRepository $propertyRepository,
    ): ?Property {
        if (!$request->hasSession()) {
            return null;
        }

        $propertyId = $request
            ->getSession()
            ->get(
                'mes_biens_property_id'
            );

        if (
            null === $propertyId
            || '' === $propertyId
        ) {
            return null;
        }

        return $propertyRepository->find(
            (int) $propertyId
        );
    }

    private function buildPropertyContextFromProperty(Property $property): string
    {
        $lines = [];

        $this->addContextLine(
            $lines,
            'Type de bien',
            $property->getTypeBien()?->getName()
        );

        $this->addContextLine(
            $lines,
            'Type de transaction',
            $property->getTypeTransaction()?->getName()
        );

        $this->addContextLine(
            $lines,
            'Ville',
            $property->getVille()
        );

        $this->addContextLine(
            $lines,
            'Code postal',
            $property->getCodePostal()
        );

        $this->addContextLine(
            $lines,
            'Pays',
            $property->getPays()
        );

        $this->addContextLine(
            $lines,
            'Quartier',
            $property->getNeighborhood()
        );

        $this->addContextLine(
            $lines,
            'Localité',
            $property->getLocality()
        );

        $this->addContextLine(
            $lines,
            'Région',
            $property->getRegion()
        );

        $this->addContextLine(
            $lines,
            'Surface totale',
            $property->getSurfaceTotal()
        );

        $this->addContextLine(
            $lines,
            'Nombre de chambres',
            $property->getChambres()
        );

        $this->addContextLine(
            $lines,
            'Nombre de salles de bains',
            $property->getSalleDeBains()
        );

        $this->addContextLine(
            $lines,
            'Année de construction',
            $property->getAnneeConstruction()
        );

        $this->addContextLine(
            $lines,
            'Classe énergétique',
            $property->getDpeLettre()
        );

        $this->addContextLine(
            $lines,
            'DPE',
            $property->getDpe()
        );

        $this->addContextLine(
            $lines,
            'DPE minimum',
            $property->getDpeMin()
        );

        $this->addContextLine(
            $lines,
            'DPE maximum',
            $property->getDpeMax()
        );

        $this->addContextLine(
            $lines,
            'GES',
            $property->getGes()
        );

        $this->addContextLine(
            $lines,
            'Date d’indexation énergie',
            $property->getDateIndexationEnergie()
        );

        $this->addContextLine(
            $lines,
            'Prix',
            $property->getPrix()
        );

        $this->addContextLine(
            $lines,
            'Loyer hors charges',
            $property->getMontantLoyerHorsCharge()
        );

        $this->addContextLine(
            $lines,
            'Charges',
            $property->getMontantDesCharges()
        );

        $this->addContextLine(
            $lines,
            'Dépôt de garantie',
            $property->getMontantDepotDeGarantie()
        );

        $caracteristiques = [];

        foreach ($property->getCaracteristique() as $caracteristique) {
            if (
                !\is_object($caracteristique)
                || !method_exists($caracteristique, 'getNom')
            ) {
                continue;
            }

            $caracteristiques[] = $caracteristique->getNom();
        }

        $this->addContextListLine(
            $lines,
            'Caractéristiques',
            $caracteristiques
        );

        return implode(
            "\n",
            array_unique($lines)
        );
    }

    /**
     * @param list<string> $lines
     */
    private function addContextLine(
        array &$lines,
        string $label,
        mixed $value,
    ): void {
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(
                'd/m/Y'
            );
        }

        $value = $this->cleanContextValue(
            $value
        );

        if ('' === $value) {
            return;
        }

        $lines[] = \sprintf(
            '- %s : %s',
            $label,
            $value
        );
    }

    /**
     * @param list<string> $lines
     * @param list<mixed>  $values
     */
    private function addContextListLine(
        array &$lines,
        string $label,
        array $values,
    ): void {
        $values = array_values(
            array_unique(
                array_filter(
                    array_map(
                        fn (mixed $value): string => $this->cleanContextValue(
                            $value
                        ),
                        $values
                    ),
                    static fn (string $value): bool => '' !== $value
                )
            )
        );

        if ([] === $values) {
            return;
        }

        $lines[] = \sprintf(
            '- %s : %s',
            $label,
            implode(
                ', ',
                $values
            )
        );
    }

    private function cleanContextValue(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        if (\is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if (!\is_scalar($value)) {
            return '';
        }

        return mb_trim(
            (string) $value
        );
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
