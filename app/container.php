<?php

use App\Core\Container;
use App\Core\Db\Db;
use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\DbInterface;
use App\Core\Service\AuthService;
use App\Core\Service\RememberMeService;
use App\Interface\BetReaderRepositoryInterface;
use App\Interface\BetWriterRepositoryInterface;
use App\Interface\CurrencyRepositoryInterface;
use App\Interface\MatchConfigProviderInterface;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Interface\UserAmountRepositoryInterface;
use App\Interface\UserReaderRepositoryInterface;
use App\Interface\UserWriterRepositoryInterface;
use App\Provider\MatchConfigProvider;
use App\Repository\BetReaderRepository;
use App\Repository\BetWriterRepository;
use App\Repository\CurrencyRepository;
use App\Repository\UserAccountLogRepository;
use App\Repository\UserAmountRepository;
use App\Repository\UserReaderRepository;
use App\Repository\UserWriterRepository;

// --- Проверим, что конфиг БД передан
if (!isset($databaseConfig) || !is_array($databaseConfig)) {
    throw new RuntimeException('Expected $databaseConfig to be defined before requiring app/container.php');
}

$container = new Container();

// в контейнере сделан автовайринг

// записываем вручную что бы передать конфиг
$container->set(Db::class, static fn (): Db => new Db(
    $databaseConfig
));
// вручную потому что через интерфейс
$container->set(DbInterface::class, static fn (Container $container): DbInterface => $container->get(Db::class));

// репозитории создаются не напрямую, а через интерфейсы, по этому их нужно добавить
$container->set(BetWriterRepositoryInterface::class, static fn (Container $container): BetWriterRepositoryInterface => $container->get(BetWriterRepository::class));
$container->set(BetReaderRepositoryInterface::class, static fn (Container $container): BetReaderRepositoryInterface => $container->get(BetReaderRepository::class));
$container->set(CurrencyRepositoryInterface::class, static fn (Container $container): CurrencyRepositoryInterface => $container->get(CurrencyRepository::class));
$container->set(UserAccountLogRepositoryInterface::class, static fn (Container $container): UserAccountLogRepositoryInterface => $container->get(UserAccountLogRepository::class));
$container->set(UserAmountRepositoryInterface::class, static fn (Container $container): UserAmountRepositoryInterface => $container->get(UserAmountRepository::class));
$container->set(UserReaderRepositoryInterface::class, static fn (Container $container): UserReaderRepositoryInterface => $container->get(UserReaderRepository::class));
$container->set(UserWriterRepositoryInterface::class, static fn (Container $container): UserWriterRepositoryInterface => $container->get(UserWriterRepository::class));
$container->set(MatchConfigProviderInterface::class, static fn (Container $container): MatchConfigProviderInterface => $container->get(MatchConfigProvider::class));

// записываем вручную что бы передать конфиг
$container->set(RememberMeService::class, static fn (): RememberMeService => new RememberMeService(
    (string) env('APP_SECRET'),
));

$container->set(AuthServiceInterface::class, static fn (Container $container): AuthServiceInterface => $container->get(AuthService::class));


return $container;