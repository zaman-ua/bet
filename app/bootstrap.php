<?php

declare(strict_types=1);

// автозагрузка через composer
use App\Core\Container;
use App\Core\Interface\AuthServiceInterface;
use App\Core\Service\AuthService;
use App\Core\Service\RememberMeService;
use App\Domain\MoneyFactory;
use App\Interface\BetRepositoryInterface;
use App\Interface\CurrencyRepositoryInterface;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Interface\UserAmountRepositoryInterface;
use App\Interface\UserRepositoryInterface;
use App\Repository\BetRepository;
use App\Repository\CurrencyRepository;
use App\Repository\UserAccountLogRepository;
use App\Repository\UserAmountRepository;
use App\Repository\UserRepository;
use App\Services\AmountService;
use App\Services\BetPlayService;
use App\Services\BettingService;

require __DIR__ . '/../vendor/autoload.php';

// константа для рутовой директории проекта
define('APP_ROOT', realpath(__DIR__ . '/..'));

require APP_ROOT . '/app/helpers.php';

if(env('APP_DEBUG', false)) {
    ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

// тут сразу же своим хелпером и воспользуемся
date_default_timezone_set(env('APP_TZ', 'UTC'));

// заполняем настройки подключения к базе,
// но само подключение произойдет по месту использования
App\Core\Db\Db::configure(require APP_ROOT . '/config/database.php');

// не хороший тон стартовать сессию в каждом инстансе приложения
// выносим в абстрактный контроллер для http запросов, а для api сессия не нужна
// возможно позже сделаю лучше
session_start();

// переносим ниже, где уже работает контейнер
//Auth::resumeFromRememberCookie();

$container = new Container();

// в контейнере сделан автовайринг
// репозитории создаются не напрямую, а через интерфейсы, по этому их нужно добавить
$container->set(BetRepositoryInterface::class, static fn (Container $container): BetRepositoryInterface => $container->get(BetRepository::class));
$container->set(CurrencyRepositoryInterface::class, static fn (Container $container): CurrencyRepositoryInterface => $container->get(CurrencyRepository::class));
$container->set(UserAccountLogRepositoryInterface::class, static fn (Container $container): UserAccountLogRepositoryInterface => $container->get(UserAccountLogRepository::class));
$container->set(UserAmountRepositoryInterface::class, static fn (Container $container): UserAmountRepositoryInterface => $container->get(UserAmountRepository::class));
$container->set(UserRepositoryInterface::class, static fn (Container $container): UserRepositoryInterface => $container->get(UserRepository::class));

$container->set(RememberMeService::class, static fn (): RememberMeService => new RememberMeService(
    (string) env('APP_SECRET'),
));

$container->set(AuthServiceInterface::class, static fn (Container $container): AuthServiceInterface => $container->get(AuthService::class));

// то что переносили свыше Auth::resumeFromRememberCookie();
$container->get(AuthServiceInterface::class)->resumeFromRememberCookie();

return $container;