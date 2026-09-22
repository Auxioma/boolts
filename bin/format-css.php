<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;

$directory = dirname(__DIR__) . '/assets/styles';

if (!is_dir($directory)) {
    fwrite(STDERR, sprintf(
        "Directory not found: %s%s",
        $directory,
        PHP_EOL
    ));

    exit(1);
}

$format = OutputFormat::create()
    ->indentWithSpaces(4)
    ->setSpaceBetweenRules("\n\n");

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $directory,
        FilesystemIterator::SKIP_DOTS
    )
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    if (strtolower($file->getExtension()) !== 'css') {
        continue;
    }

    $path = $file->getPathname();

    $content = file_get_contents($path);

    if ($content === false) {
        fwrite(STDERR, sprintf(
            "Unable to read: %s%s",
            $path,
            PHP_EOL
        ));

        continue;
    }

    try {
        $parser = new Parser($content);
        $document = $parser->parse();

        $formattedCss = $document->render($format);

        file_put_contents(
            $path,
            rtrim($formattedCss) . PHP_EOL
        );

        echo sprintf(
            "Formatted: %s%s",
            $path,
            PHP_EOL
        );
    } catch (Throwable $exception) {
        fwrite(STDERR, sprintf(
            "Error in %s: %s%s",
            $path,
            $exception->getMessage(),
            PHP_EOL
        ));
    }
}