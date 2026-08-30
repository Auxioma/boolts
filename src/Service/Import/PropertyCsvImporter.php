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

namespace App\Service\Import;

use App\Entity\Caracteristique;
use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Entity\User;
use App\Repository\CaracteristiqueRepository;
use App\Repository\CategoryBienRepository;
use App\Repository\CategoryBienTransactionRepository;
use App\Repository\UserRepository;
use App\Service\NumericSlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Import de biens immobiliers depuis un fichier CSV (back-office).
 *
 * Principes :
 *  - le CSV ne contient aucun ID : les associations (type de bien, type de
 *    transaction, agence, caractéristiques, statut) sont résolues « par la
 *    valeur » (libellé ou slug, dans n'importe quelle langue configurée), en
 *    cohérence avec les données déjà présentes en base ;
 *  - chaque ligne crée un nouveau bien ;
 *  - le site est multilingue (%app.languages%). Si une langue est absente pour
 *    un champ traduisible, on retombe sur la valeur de la langue renseignée
 *    (valeur par défaut du bien).
 */
final class PropertyCsvImporter
{
    /**
     * Champs traduisibles : base de colonne CSV => setter sur la traduction.
     * Les colonnes réelles sont suffixées par la langue (ex. « titre_fr »).
     */
    private const array TRANSLATABLE_FIELDS = [
        'titre' => 'setTitreDuLogement',
        'description' => 'setDescriptionLogement',
        'adresse' => 'setAdresse',
        'ville' => 'setVille',
        'pays' => 'setPays',
        'adresse_complete' => 'setFullAddress',
        'region' => 'setRegion',
        'district' => 'setDistrict',
        'localite' => 'setLocality',
        'quartier' => 'setNeighborhood',
        'point_interet' => 'setPoi',
    ];

    /**
     * Champs scalaires : colonne CSV => setter sur Property.
     * Toutes les valeurs sont posées telles quelles (setters « ?string »).
     */
    private const array SCALAR_FIELDS = [
        'reference_interne' => 'setReferenceInterne',
        'code_postal' => 'setCodePostal',
        'latitude' => 'setLatitude',
        'longitude' => 'setLongitude',
        'mapbox_id' => 'setMapboxId',
        'session_id_mapbox' => 'setSessionIdMapbox',
        'feature_type' => 'setFeatureType',
        'annee_construction' => 'setAnneeConstruction',
        'chambres' => 'setChambres',
        'salle_de_bains' => 'setSalleDeBains',
        'surface_total' => 'setSurfaceTotal',
        'dpe' => 'setDpe',
        'dpe_min' => 'setDpeMin',
        'dpe_max' => 'setDpeMax',
        'ges' => 'setGes',
    ];

    /**
     * Champs numériques (montants) : normalisés (virgule -> point, espaces retirés).
     */
    private const array DECIMAL_FIELDS = [
        'prix' => 'setPrix',
        'montant_loyer_hors_charge' => 'setMontantLoyerHorsCharge',
        'montant_depot_de_garantie' => 'setMontantDepotDeGarantie',
        'montant_des_charges' => 'setMontantDesCharges',
    ];

    private const int MAX_IMAGES_PER_PROPERTY = 20;

    private const int MAX_IMAGE_BYTES = 8 * 1024 * 1024;

    /**
     * En-têtes « navigateur » : de nombreux CDN d'images (SeLoger via Akamai /
     * Cloudimage, etc.) refusent ou limitent (HTTP 429) les requêtes serveur
     * qui n'imitent pas une vraie requête d'image de navigateur. Le Referer
     * est ajouté dynamiquement (racine du domaine de l'image).
     */
    private const array IMAGE_REQUEST_HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        'Accept' => 'image/avif,image/webp,image/apng,image/png,image/jpeg,image/svg+xml,image/*,*/*;q=0.8',
        'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
        'Sec-Fetch-Dest' => 'image',
        'Sec-Fetch-Mode' => 'no-cors',
        'Sec-Fetch-Site' => 'cross-site',
        'sec-ch-ua' => '"Chromium";v="125", "Not.A/Brand";v="24"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Windows"',
    ];

    private const int IMAGE_DOWNLOAD_MAX_ATTEMPTS = 4;

    /**
     * Extension de fichier déduite du type MIME réel du contenu téléchargé.
     */
    private const array IMAGE_EXTENSION_BY_MIME = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
        'image/bmp' => 'bmp',
        'image/tiff' => 'tiff',
    ];

    /**
     * @param list<string> $locales
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryBienRepository $categoryBienRepository,
        private readonly CategoryBienTransactionRepository $categoryBienTransactionRepository,
        private readonly CaracteristiqueRepository $caracteristiqueRepository,
        private readonly UserRepository $userRepository,
        private readonly NumericSlugGenerator $slugGenerator,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        #[Autowire('%app.languages%')]
        private readonly array $locales = ['fr', 'en'],
    ) {
    }

    /**
     * Liste ordonnée des colonnes du modèle CSV (sans ID).
     *
     * @return list<string>
     */
    public function templateColumns(): array
    {
        $columns = [
            'agence_email',
            'agence_nom',
            'type_bien',
            'type_transaction',
            'statut',
        ];

        $columns = array_merge($columns, array_keys(self::DECIMAL_FIELDS));
        $columns = array_merge($columns, array_keys(self::SCALAR_FIELDS));
        $columns[] = 'dpe_lettre';
        $columns[] = 'ges_lettre';
        $columns[] = 'date_indexation_energie';
        $columns[] = 'show_adresse';
        $columns[] = 'caracteristiques';
        $columns[] = 'images';

        foreach (array_keys(self::TRANSLATABLE_FIELDS) as $base) {
            foreach ($this->locales as $locale) {
                $columns[] = $base.'_'.$locale;
            }
        }

        return $columns;
    }

    public function import(string $csvPath): PropertyImportReport
    {
        $report = new PropertyImportReport();

        $rows = $this->readCsv($csvPath, $report);

        if ([] === $rows) {
            return $report;
        }

        $typeBienMap = $this->buildTypeBienMap();
        $typeTransactionMap = $this->buildTypeTransactionMap();
        $caracteristiqueMap = $this->buildCaracteristiqueMap();

        foreach ($rows as $lineNumber => $row) {
            $report->addProcessed();

            if (!$this->entityManager->isOpen()) {
                $report->addError($lineNumber, 'Import interrompu : le gestionnaire d’entités est fermé après une erreur précédente.');

                break;
            }

            try {
                $this->importRow(
                    $lineNumber,
                    $row,
                    $typeBienMap,
                    $typeTransactionMap,
                    $caracteristiqueMap,
                    $report,
                );
            } catch (SkipRowException $exception) {
                $report->addError($lineNumber, $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->logger->error('Import CSV bien : ligne en erreur', [
                    'line' => $lineNumber,
                    'exception' => $exception,
                ]);

                $report->addError($lineNumber, 'Erreur inattendue : '.$exception->getMessage());
            }
        }

        return $report;
    }

    /**
     * @param array<string, string|null>             $row
     * @param array<string, CategoryBien>            $typeBienMap
     * @param array<string, CategoryBienTransaction> $typeTransactionMap
     * @param array<string, Caracteristique>         $caracteristiqueMap
     */
    private function importRow(
        int $lineNumber,
        array $row,
        array $typeBienMap,
        array $typeTransactionMap,
        array $caracteristiqueMap,
        PropertyImportReport $report,
    ): void {
        $agency = $this->resolveAgency($row);
        $typeBien = $this->resolveFromMap($typeBienMap, $row['type_bien'] ?? null);
        $typeTransaction = $this->resolveFromMap($typeTransactionMap, $row['type_transaction'] ?? null);

        if (null === $typeBien) {
            throw new SkipRowException(\sprintf('type de bien introuvable : « %s »', (string) ($row['type_bien'] ?? '')));
        }

        if (null === $typeTransaction) {
            throw new SkipRowException(\sprintf('type de transaction introuvable : « %s »', (string) ($row['type_transaction'] ?? '')));
        }

        $property = new Property();
        $property->setUser($agency);
        $property->setTypeBien($typeBien);
        $property->setTypeTransaction($typeTransaction);
        $property->setStatut($this->resolveStatut($row['statut'] ?? null));
        $property->setSlug($this->slugGenerator->generate(16));

        foreach (self::SCALAR_FIELDS as $column => $setter) {
            $value = $this->clean($row[$column] ?? null);

            if (null !== $value) {
                $property->{$setter}($value);
            }
        }

        foreach (self::DECIMAL_FIELDS as $column => $setter) {
            $value = $this->parseDecimal($row[$column] ?? null);

            if (null !== $value) {
                $property->{$setter}($value);
            }
        }

        $dpeLettre = $this->clean($row['dpe_lettre'] ?? null);

        if (null !== $dpeLettre) {
            $property->setDpeLettre(mb_strtoupper($dpeLettre));
        }

        $gesLettre = $this->clean($row['ges_lettre'] ?? null);

        if (null !== $gesLettre) {
            $property->setGesLettre(mb_strtoupper($gesLettre));
        }

        $indexationDate = $this->parseDate($row['date_indexation_energie'] ?? null);

        if (null !== $indexationDate) {
            $property->setDateIndexationEnergie($indexationDate);
        }

        $showAdresse = $this->parseBool($row['show_adresse'] ?? null);

        if (null !== $showAdresse) {
            $property->setShowAdresse($showAdresse);
        }

        foreach ($this->resolveCaracteristiques($row['caracteristiques'] ?? null, $caracteristiqueMap, $lineNumber, $report) as $caracteristique) {
            $property->addCaracteristique($caracteristique);
        }

        $this->applyTranslations($property, $row, $lineNumber, $report);

        $this->entityManager->persist($property);
        $this->entityManager->flush();

        $this->attachImages($property, $row['images'] ?? null, $lineNumber, $report);

        $report->addCreated();
    }

    /**
     * @param array<string, string|null> $row
     */
    private function resolveAgency(array $row): User
    {
        $email = $this->clean($row['agence_email'] ?? null);
        $name = $this->clean($row['agence_nom'] ?? null);

        if (null === $email && null === $name) {
            throw new SkipRowException('aucune agence renseignée (agence_email ou agence_nom requis).');
        }

        $agency = null;

        if (null !== $email) {
            $agency = $this->userRepository->findOneBy(['email' => mb_strtolower($email)]);
        }

        if (null === $agency && null !== $name) {
            $agency = $this->userRepository->findOneBy(['entreprise' => $name]);
        }

        if (null === $agency) {
            throw new SkipRowException(\sprintf('agence introuvable (« %s »).', $email ?? $name));
        }

        if (!\in_array('ROLE_AGENCE', $agency->getRoles(), true)) {
            throw new SkipRowException(\sprintf('l’utilisateur « %s » n’est pas une agence.', $email ?? $name));
        }

        return $agency;
    }

    private function resolveStatut(?string $raw): StatutAnnonceImmobiliere
    {
        $value = $this->clean($raw);

        if (null === $value) {
            return StatutAnnonceImmobiliere::BROUILLON;
        }

        $statut = StatutAnnonceImmobiliere::tryFrom(mb_strtolower($value));

        if (null !== $statut) {
            return $statut;
        }

        $normalized = $this->normalize($value);

        foreach (StatutAnnonceImmobiliere::cases() as $case) {
            if ($this->normalize($case->label()) === $normalized) {
                return $case;
            }
        }

        throw new SkipRowException(\sprintf('statut inconnu : « %s ».', $value));
    }

    /**
     * @param array<string, Caracteristique> $map
     *
     * @return list<Caracteristique>
     */
    private function resolveCaracteristiques(
        ?string $raw,
        array $map,
        int $lineNumber,
        PropertyImportReport $report,
    ): array {
        $value = $this->clean($raw);

        if (null === $value) {
            return [];
        }

        $result = [];

        foreach (preg_split('/[|;]/', $value) ?: [] as $token) {
            $key = $this->normalize($token);

            if ('' === $key) {
                continue;
            }

            if (isset($map[$key])) {
                $result[$map[$key]->getId()] = $map[$key];

                continue;
            }

            $report->addWarning($lineNumber, \sprintf('caractéristique ignorée (introuvable) : « %s ».', mb_trim($token)));
        }

        return array_values($result);
    }

    /**
     * @param array<string, string|null> $row
     */
    private function applyTranslations(
        Property $property,
        array $row,
        int $lineNumber,
        PropertyImportReport $report,
    ): void {
        /** @var array<string, array<string, string|null>> $values */
        $values = [];

        foreach (array_keys(self::TRANSLATABLE_FIELDS) as $base) {
            foreach ($this->locales as $locale) {
                $values[$base][$locale] = $this->clean($row[$base.'_'.$locale] ?? null);
            }

            // Repli par langue : toute langue manquante pour ce champ reprend
            // la première valeur renseignée (valeur par défaut du bien).
            $fallback = null;

            foreach ($this->locales as $locale) {
                if (null !== $values[$base][$locale]) {
                    $fallback = $values[$base][$locale];

                    break;
                }
            }

            foreach ($this->locales as $locale) {
                $values[$base][$locale] ??= $fallback;
            }
        }

        foreach ($this->locales as $locale) {
            $translation = $property->translate($locale, false);

            foreach (self::TRANSLATABLE_FIELDS as $base => $setter) {
                $translation->{$setter}($values[$base][$locale]);
            }
        }

        $property->mergeNewTranslations();

        $hasTitle = false;

        foreach ($this->locales as $locale) {
            if (null !== ($values['titre'][$locale] ?? null)) {
                $hasTitle = true;

                break;
            }
        }

        if (!$hasTitle) {
            $report->addWarning($lineNumber, 'bien créé sans titre (aucune colonne titre_* renseignée).');
        }
    }

    private function attachImages(
        Property $property,
        ?string $raw,
        int $lineNumber,
        PropertyImportReport $report,
    ): void {
        $value = $this->clean($raw);

        if (null === $value) {
            return;
        }

        $position = 1;
        $temporaryFiles = [];
        $attached = false;

        try {
            foreach (preg_split('/[|;,\s]+/', $value) ?: [] as $url) {
                $url = mb_trim($url);

                if ('' === $url) {
                    continue;
                }

                if ($position > self::MAX_IMAGES_PER_PROPERTY) {
                    $report->addWarning($lineNumber, \sprintf('images au-delà de %d ignorées.', self::MAX_IMAGES_PER_PROPERTY));

                    break;
                }

                $temporaryPath = $this->downloadImage($url, $lineNumber, $report);

                if (null === $temporaryPath) {
                    continue;
                }

                $temporaryFiles[] = $temporaryPath;

                $image = new PropertyImage();
                $image->setProperty($property);
                $image->setPosition((string) $position);
                $image->setImageFile(new File($temporaryPath));

                $property->addPropertyImage($image);
                $this->entityManager->persist($image);

                ++$position;
                $attached = true;
            }

            if ($attached) {
                $this->entityManager->flush();
            }
        } finally {
            foreach ($temporaryFiles as $temporaryPath) {
                if (is_file($temporaryPath)) {
                    @unlink($temporaryPath);
                }
            }
        }
    }

    /**
     * Télécharge une image distante vers un fichier temporaire.
     *
     * Renvoie le chemin du fichier téléchargé, ou null en cas d'échec (un
     * avertissement détaillé est alors ajouté au rapport pour expliquer la
     * cause : URL invalide, code HTTP, blocage anti-robot, contenu non-image…).
     */
    private function downloadImage(string $url, int $lineNumber, PropertyImportReport $report): ?string
    {
        if (!preg_match('#^https?://#i', $url)) {
            $report->addWarning($lineNumber, \sprintf('URL d’image ignorée (protocole non supporté) : %s', $url));

            return null;
        }

        $headers = self::IMAGE_REQUEST_HEADERS;
        $referer = $this->refererForUrl($url);

        if (null !== $referer) {
            $headers['Referer'] = $referer;
        }

        $content = null;
        $maxAttempts = self::IMAGE_DOWNLOAD_MAX_ATTEMPTS;

        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            try {
                $response = $this->httpClient->request('GET', $url, [
                    'timeout' => 20,
                    'max_redirects' => 5,
                    'headers' => $headers,
                ]);

                $statusCode = $response->getStatusCode();

                if (\in_array($statusCode, [429, 503], true) && $attempt < $maxAttempts) {
                    // Ces CDN se « débloquent » après quelques requêtes espacées.
                    $retryAfter = (int) ($response->getHeaders(false)['retry-after'][0] ?? 0);
                    $waitSeconds = min(5, max($attempt + 1, $retryAfter));

                    usleep($waitSeconds * 1_000_000);

                    continue;
                }

                if ($statusCode < 200 || $statusCode >= 300) {
                    $report->addWarning($lineNumber, \sprintf(
                        'image non téléchargée après %d tentative(s) (HTTP %d%s) : %s',
                        $attempt,
                        $statusCode,
                        429 === $statusCode ? ', l’hébergeur limite les téléchargements automatiques' : '',
                        $url,
                    ));

                    return null;
                }

                $content = $response->getContent(false);

                break;
            } catch (\Throwable $exception) {
                $this->logger->warning('Import CSV bien : image non téléchargée', [
                    'url' => $url,
                    'exception' => $exception,
                ]);

                $report->addWarning($lineNumber, \sprintf('image non téléchargée (%s) : %s', $exception->getMessage(), $url));

                return null;
            }
        }

        if (null === $content) {
            return null;
        }

        $size = mb_strlen($content, '8bit');

        if (0 === $size) {
            $report->addWarning($lineNumber, \sprintf('image vide reçue (souvent un blocage anti-robot de l’hébergeur) : %s', $url));

            return null;
        }

        if ($size > self::MAX_IMAGE_BYTES) {
            $report->addWarning($lineNumber, \sprintf('image trop volumineuse (%d octets, max %d) : %s', $size, self::MAX_IMAGE_BYTES, $url));

            return null;
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'boolts_prop_img_');

        if (false === $temporaryPath) {
            return null;
        }

        file_put_contents($temporaryPath, $content);

        $mimeType = null;

        try {
            $mimeType = (new File($temporaryPath, false))->getMimeType();
        } catch (\Throwable) {
            // Détection MIME indisponible : on retombe sur guessExtension() ci-dessous.
        }

        if (null !== $mimeType && !str_starts_with($mimeType, 'image/')) {
            @unlink($temporaryPath);

            $report->addWarning($lineNumber, \sprintf('le contenu téléchargé n’est pas une image (%s) : %s', $mimeType, $url));

            return null;
        }

        $extension = null !== $mimeType ? (self::IMAGE_EXTENSION_BY_MIME[$mimeType] ?? null) : null;

        if (null === $extension) {
            try {
                $extension = (new File($temporaryPath, false))->guessExtension();
            } catch (\Throwable) {
                $extension = null;
            }
        }

        $extension ??= 'jpg';

        $finalPath = $temporaryPath.'.'.$extension;

        if (!@rename($temporaryPath, $finalPath)) {
            return $temporaryPath;
        }

        return $finalPath;
    }

    /**
     * Referer plausible pour une URL d'image : racine du domaine « site »
     * associé au sous-domaine média (ex. mms.seloger.com -> www.seloger.com),
     * sinon la racine de l'hôte de l'image. De nombreux CDN protègent leurs
     * images contre le hotlink et exigent un Referer du même domaine.
     */
    private function refererForUrl(string $url): ?string
    {
        $host = parse_url($url, \PHP_URL_HOST);

        if (!\is_string($host) || '' === $host) {
            return null;
        }

        $parts = explode('.', $host);
        $registrableDomain = \count($parts) >= 2
            ? implode('.', \array_slice($parts, -2))
            : $host;

        return 'https://www.'.$registrableDomain.'/';
    }

    /**
     * @return array<int, array<string, string|null>> ligne du fichier (>= 2) => données associatives
     */
    private function readCsv(string $csvPath, PropertyImportReport $report): array
    {
        if (!is_file($csvPath) || !is_readable($csvPath)) {
            $report->addError('-', 'fichier CSV illisible.');

            return [];
        }

        $handle = fopen($csvPath, 'r');

        if (false === $handle) {
            $report->addError('-', 'impossible d’ouvrir le fichier CSV.');

            return [];
        }

        $delimiter = $this->detectDelimiter($csvPath);
        $rows = [];
        $headers = null;
        $lineNumber = 0;

        while (false !== ($record = fgetcsv($handle, 0, $delimiter, '"', ''))) {
            ++$lineNumber;

            if ([null] === $record || (1 === \count($record) && null === $record[0])) {
                continue;
            }

            if (null === $headers) {
                $headers = array_map(
                    fn (?string $column): string => $this->normalizeHeader((string) $column),
                    $record,
                );

                continue;
            }

            if ('' === mb_trim(implode('', array_map(static fn ($cell): string => (string) $cell, $record)))) {
                continue;
            }

            $line = [];

            foreach ($headers as $index => $header) {
                if ('' === $header) {
                    continue;
                }

                $line[$header] = \array_key_exists($index, $record) ? (string) $record[$index] : null;
            }

            $rows[$lineNumber] = $line;
        }

        fclose($handle);

        if (null === $headers) {
            $report->addError('-', 'fichier CSV vide (aucun en-tête).');
        }

        return $rows;
    }

    private function detectDelimiter(string $csvPath): string
    {
        $handle = fopen($csvPath, 'r');

        if (false === $handle) {
            return ';';
        }

        $firstLine = (string) (fgets($handle) ?: '');
        fclose($handle);

        return mb_substr_count($firstLine, ';') >= mb_substr_count($firstLine, ',') ? ';' : ',';
    }

    /**
     * @return array<string, CategoryBien>
     */
    private function buildTypeBienMap(): array
    {
        $map = [];

        foreach ($this->categoryBienRepository->findAll() as $entity) {
            foreach ($entity->getTranslations() as $translation) {
                $this->indexLabel($map, $translation->getName(), $entity);
                $this->indexLabel($map, $translation->getSlug(), $entity);
            }

            $this->indexLabel($map, (string) $entity->getId(), $entity);
        }

        return $map;
    }

    /**
     * @return array<string, CategoryBienTransaction>
     */
    private function buildTypeTransactionMap(): array
    {
        $map = [];

        foreach ($this->categoryBienTransactionRepository->findAll() as $entity) {
            foreach ($entity->getTranslations() as $translation) {
                $this->indexLabel($map, $translation->getName(), $entity);
                $this->indexLabel($map, $translation->getSlug(), $entity);
            }

            $this->indexLabel($map, (string) $entity->getId(), $entity);
        }

        return $map;
    }

    /**
     * @return array<string, Caracteristique>
     */
    private function buildCaracteristiqueMap(): array
    {
        $map = [];

        foreach ($this->caracteristiqueRepository->findAll() as $entity) {
            foreach ($entity->getTranslations() as $translation) {
                $this->indexLabel($map, $translation->getNom(), $entity);
            }

            $this->indexLabel($map, (string) $entity->getId(), $entity);
        }

        return $map;
    }

    /**
     * @template T of object
     *
     * @param array<string, T> $map
     * @param T                $entity
     *
     * @param-out array<string, T> $map
     */
    private function indexLabel(array &$map, ?string $label, object $entity): void
    {
        $key = $this->normalize((string) $label);

        if ('' !== $key) {
            $map[$key] ??= $entity;
        }
    }

    /**
     * @template T of object
     *
     * @param array<string, T> $map
     *
     * @return T|null
     */
    private function resolveFromMap(array $map, ?string $raw): ?object
    {
        $key = $this->normalize((string) $raw);

        return '' === $key ? null : ($map[$key] ?? null);
    }

    private function clean(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = mb_trim($value);

        return '' === $value ? null : $value;
    }

    private function parseDecimal(?string $value): ?string
    {
        $value = $this->clean($value);

        if (null === $value) {
            return null;
        }

        $value = str_replace([' ', "\u{00a0}"], '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? $value : null;
    }

    private function parseBool(?string $value): ?bool
    {
        $value = $this->clean($value);

        if (null === $value) {
            return null;
        }

        return match (mb_strtolower($value)) {
            '1', 'true', 'oui', 'yes', 'vrai', 'x' => true,
            '0', 'false', 'non', 'no', 'faux' => false,
            default => null,
        };
    }

    private function parseDate(?string $value): ?\DateTimeImmutable
    {
        $value = $this->clean($value);

        if (null === $value) {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', \DateTimeInterface::ATOM] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);

            if (false !== $date) {
                return $date;
            }
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;

        return mb_strtolower(mb_trim($header));
    }

    /**
     * Normalise un libellé pour comparaison : minuscules, sans accents,
     * séparateurs unifiés, espaces compactés.
     */
    private function normalize(string $value): string
    {
        $value = mb_trim(mb_strtolower($value));

        if ('' === $value) {
            return '';
        }

        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);

        if (false !== $transliterated) {
            $value = mb_strtolower($transliterated);
        }

        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return mb_trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }
}
