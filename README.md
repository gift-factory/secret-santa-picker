# Secret Santa Picker

Pick a random player for each other player of a given group

[![Latest Stable Version](https://poser.pugx.org/gift-factory/secret-santa-picker/v/stable.png)](https://packagist.org/packages/gift-factory/secret-santa-picker)
[![PHP Version](https://img.shields.io/packagist/php-v/gift-factory/secret-santa-picker.svg)](https://php.net)
[![License](https://poser.pugx.org/gift-factory/secret-santa-picker/license)](https://packagist.org/packages/gift-factory/secret-santa-picker)
[![GitHub Actions](https://img.shields.io/endpoint.svg?url=https%3A%2F%2Factions-badge.atrox.dev%2Fgift-factory%2Fsecret-santa-picker%2Fbadge%3Fref=main&label=Build&logo=none)](https://github.com/gift-factory/secret-santa-picker/actions)
[![StyleCI](https://styleci.io/repos/717786903/shield?style=flat)](https://styleci.io/repos/717786903)

## Install

```shell
composer require gift-factory/secret-santa-picker
```

## Use

```php
$players = new PlayerList([
    new Player('Anna'),
    new Player('Bob'),
    // Bob and Dane won't be picked to send a gift to Chuck
    new Player('Chuck', exclusions: ['Bob', 'Dane']),
    new Player('Dane'),
    // Edith and Fiona will be mutually excluded
    [new Player('Edith'), new Player('Fiona')],
]);

$picker = new Picker();
$draw = $picker->pick($players);

foreach ($draw as $donor => $receiver) {
    mail(
        $donor->email,
        'Secret Santa',
        "
            Hello $donor->userName,

            This year, you'll be the santa of $receiver->userName,
            Here is the address where to send your gift:

            $receiver->realName
            $receiver->address
        ",
    );
}
```

