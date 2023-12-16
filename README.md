<p align="center">
  <img src="https://github.com/abbasudo/pulse-users/assets/86796762/03454638-0b58-4415-b157-f3159fa7fc43" alt="Social Card of Pulse Users">
  <h1 align="center">Hourly Usage for Laravel Pulse</h1>
</p>


[![License](http://poser.pugx.org/abbasudo/pulse-users/license)](https://github.com/abbasudo/pulse-users)
[![Latest Unstable Version](http://poser.pugx.org/abbasudo/pulse-users/v)](https://packagist.org/packages/abbasudo/pulse-users)
[![PHP Version Require](http://poser.pugx.org/abbasudo/pulse-users/require/php)](https://packagist.org/packages/abbasudo/pulse-users)

Pulse Users gives you an Hourly distributed requests chart.

## Installation

> **Note**
> You need to have [Laravel Pulse](https://pulse.laravel.com/) installed first.

Install the package via composer by this command:
```sh
composer require abbasudo/pulse-users 
```

### Add components to the dashboard
> **Note**
> To add the card to the Pulse dashboard, you must first [publish the vendor view](https://laravel.com/docs/10.x/pulse#dashboard-customization).

```bash
php artisan vendor:publish --tag=pulse-dashboard
```

Then, you can modify the `dashboard.blade.php` file:

```diff
<x-pulse>
+   <livewire:pulse.usage-hours cols='4' rows='2' />

    <livewire:pulse.servers cols="full" />

    <livewire:pulse.usage cols='4' rows='1' />
```
## License

Pulse Users is Licensed under The MIT License (MIT). Please see [License File](https://github.com/abbasudo/pulse-users/blob/master/LICENSE) for more information.

## Security

If you've found a bug regarding security please mail [amkhzomi@gmail.com](mailto:amkhzomi@gmail.com) instead of
using the issue tracker.
