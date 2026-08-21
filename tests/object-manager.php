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

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

(new Dotenv())
    ->usePutenv()
    ->bootEnv(__DIR__.'/.env.local');
