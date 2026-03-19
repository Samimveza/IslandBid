<?php

function renderLayout(string $title, string $description, string $content, array $pageData = []): void
{
    ?>
    <!doctype html>
    <html lang="en" ng-app="islandBidApp">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
        <meta name="description" content="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>">
        <link rel="stylesheet" href="/frontend/styles/main.css">
    </head>
    <body>
        <?php echo $content; ?>
        <script>
            window.__APP_BOOTSTRAP__ = <?php echo json_encode($pageData, JSON_UNESCAPED_SLASHES); ?>;
        </script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
        <script src="/frontend/app.js"></script>
    </body>
    </html>
    <?php
}
