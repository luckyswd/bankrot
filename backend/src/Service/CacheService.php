<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

class CacheService
{
    public const int TTL_ONE_HOUR = 3600;
    private const string PREFIX_CREDITORS = 'creditors';
    private const string PREFIX_CREDITORS_LIST = 'creditors_list';
    private const string PREFIX_COURTS = 'courts';
    private const string PREFIX_COURTS_LIST = 'courts_list';
    private const string PREFIX_BAILIFFS = 'bailiffs';
    private const string PREFIX_BAILIFFS_LIST = 'bailiffs_list';
    private const string PREFIX_FNS = 'fns';
    private const string PREFIX_FNS_LIST = 'fns_list';
    private const string PREFIX_MCHS = 'mchs';
    private const string PREFIX_MCHS_LIST = 'mchs_list';
    private const string PREFIX_ROSGVARDIA = 'rosgvardia';
    private const string PREFIX_ROSGVARDIA_LIST = 'rosgvardia_list';
    private const string PREFIX_GOSTEKHNADZOR = 'gostekhnadzor';
    private const string PREFIX_GOSTEKHNADZOR_LIST = 'gostekhnadzor_list';

    public function __construct(
        private readonly CacheItemPoolInterface $creditorsCache,
        private readonly CacheItemPoolInterface $courtsCache,
        private readonly CacheItemPoolInterface $bailiffsCache,
        private readonly CacheItemPoolInterface $fnsCache,
        private readonly CacheItemPoolInterface $mchsCache,
        private readonly CacheItemPoolInterface $rosgvardiaCache,
        private readonly CacheItemPoolInterface $gostekhnadzorCache,
    ) {
    }

    /**
     * Получить закешированные данные или выполнить callback.
     */
    public function get(string $key, callable $callback, ?int $ttl = null, string $pool = 'creditors'): mixed
    {
        $cache = $this->getCachePool($pool);

        $cacheItem = $cache->getItem($key);


        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $value = $callback();

        $cacheItem->set($value);
        if ($ttl !== null) {
            $cacheItem->expiresAfter($ttl);
        }

        $cache->save($cacheItem);

        return $value;
    }

    /**
     * Инвалидировать кеш по ключу.
     */
    public function invalidate(string $key, string $pool = 'creditors'): bool
    {
        $cache = $this->getCachePool($pool);

        return $cache->deleteItem($key);
    }

    /**
     * Инвалидировать весь кеш пула.
     */
    public function invalidatePool(string $pool = 'creditors'): bool
    {
        $cache = $this->getCachePool($pool);

        return $cache->clear();
    }

    // ========== CREDITOR METHODS ==========

    /**
     * Сгенерировать ключ для кеша кредитора.
     */
    public function getCreditorKey(int $creditorId): string
    {
        return sprintf('%s_%d', self::PREFIX_CREDITORS, $creditorId);
    }

    /**
     * Сгенерировать ключ для списка кредиторов.
     */
    public function getCreditorsListKey(int|string $page, int|string $limit, string $search = ''): string
    {
        $searchHash = md5($search);

        return sprintf('%s_p%s_l%s_s%s', self::PREFIX_CREDITORS_LIST, (string)$page, (string)$limit, $searchHash);
    }

    /**
     * Инвалидировать весь кеш кредиторов.
     */
    public function invalidateAllCreditors(): bool
    {
        return $this->invalidatePool('creditors');
    }

    /**
     * Инвалидировать кеш конкретного кредитора.
     */
    public function invalidateCreditor(int $creditorId): bool
    {
        $key = $this->getCreditorKey($creditorId);

        return $this->invalidate($key, 'creditors');
    }

    /**
     * Инвалидировать все списки кредиторов.
     */
    public function invalidateCreditorsLists(): bool
    {
        return $this->invalidateAllCreditors();
    }

    // ========== COURT METHODS ==========

    /**
     * Сгенерировать ключ для кеша суда.
     */
    public function getCourtKey(int $courtId): string
    {
        return sprintf('%s_%d', self::PREFIX_COURTS, $courtId);
    }

    /**
     * Сгенерировать ключ для списка судов.
     */
    public function getCourtsListKey(int|string $page, int|string $limit, string $search = ''): string
    {
        $searchHash = md5($search);

        return sprintf('%s_p%s_l%s_s%s', self::PREFIX_COURTS_LIST, (string)$page, (string)$limit, $searchHash);
    }

    /**
     * Инвалидировать весь кеш судов.
     */
    public function invalidateAllCourts(): bool
    {
        return $this->invalidatePool('courts');
    }

    /**
     * Инвалидировать кеш конкретного суда.
     */
    public function invalidateCourt(int $courtId): bool
    {
        $key = $this->getCourtKey($courtId);

        return $this->invalidate($key, 'courts');
    }

    /**
     * Инвалидировать все списки судов.
     */
    public function invalidateCourtsLists(): bool
    {
        return $this->invalidateAllCourts();
    }

    // ========== BAILIFF METHODS ==========

    /**
     * Сгенерировать ключ для кеша отделения приставов.
     */
    public function getBailiffKey(int $bailiffId): string
    {
        return sprintf('%s_%d', self::PREFIX_BAILIFFS, $bailiffId);
    }

    /**
     * Сгенерировать ключ для списка отделений приставов.
     */
    public function getBailiffsListKey(int|string $page, int|string $limit, string $search = ''): string
    {
        $searchHash = md5($search);

        return sprintf('%s_p%s_l%s_s%s', self::PREFIX_BAILIFFS_LIST, (string)$page, (string)$limit, $searchHash);
    }

    /**
     * Инвалидировать весь кеш отделений приставов.
     */
    public function invalidateAllBailiffs(): bool
    {
        return $this->invalidatePool('bailiffs');
    }

    /**
     * Инвалидировать кеш конкретного отделения приставов.
     */
    public function invalidateBailiff(int $bailiffId): bool
    {
        $key = $this->getBailiffKey($bailiffId);

        return $this->invalidate($key, 'bailiffs');
    }

    /**
     * Инвалидировать все списки отделений приставов.
     */
    public function invalidateBailiffsLists(): bool
    {
        return $this->invalidateAllBailiffs();
    }

    // ========== FNS METHODS ==========

    /**
     * Сгенерировать ключ для кеша отделения ФНС.
     */
    public function getFnsKey(int $fnsId): string
    {
        return sprintf('%s_%d', self::PREFIX_FNS, $fnsId);
    }

    /**
     * Сгенерировать ключ для списка отделений ФНС.
     */
    public function getFnsListKey(int|string $page, int|string $limit, string $search = ''): string
    {
        $searchHash = md5($search);

        return sprintf('%s_p%s_l%s_s%s', self::PREFIX_FNS_LIST, (string)$page, (string)$limit, $searchHash);
    }

    /**
     * Инвалидировать весь кеш отделений ФНС.
     */
    public function invalidateAllFns(): bool
    {
        return $this->invalidatePool('fns');
    }

    /**
     * Инвалидировать кеш конкретного отделения ФНС.
     */
    public function invalidateFns(int $fnsId): bool
    {
        $key = $this->getFnsKey($fnsId);

        return $this->invalidate($key, 'fns');
    }

    /**
     * Инвалидировать все списки отделений ФНС.
     */
    public function invalidateFnsLists(): bool
    {
        return $this->invalidateAllFns();
    }

    // ========== MCHS METHODS ==========

    /**
     * Сгенерировать ключ для кеша отделения ГИМС МЧС.
     */
    public function getMchsKey(int $mchsId): string
    {
        return sprintf('%s_%d', self::PREFIX_MCHS, $mchsId);
    }

    /**
     * Сгенерировать ключ для списка отделений ГИМС МЧС.
     */
    public function getMchsListKey(int|string $page, int|string $limit, string $search = ''): string
    {
        $searchHash = md5($search);

        return sprintf('%s_p%s_l%s_s%s', self::PREFIX_MCHS_LIST, (string)$page, (string)$limit, $searchHash);
    }

    /**
     * Инвалидировать весь кеш отделений ГИМС МЧС.
     */
    public function invalidateAllMchs(): bool
    {
        return $this->invalidatePool('mchs');
    }

    /**
     * Инвалидировать кеш конкретного отделения ГИМС МЧС.
     */
    public function invalidateMchs(int $mchsId): bool
    {
        $key = $this->getMchsKey($mchsId);

        return $this->invalidate($key, 'mchs');
    }

    /**
     * Инвалидировать все списки отделений ГИМС МЧС.
     */
    public function invalidateMchsLists(): bool
    {
        return $this->invalidateAllMchs();
    }

    // ========== ROSGVARDIA METHODS ==========

    /**
     * Сгенерировать ключ для кеша отделения Росгвардии.
     */
    public function getRosgvardiaKey(int $rosgvardiaId): string
    {
        return sprintf('%s_%d', self::PREFIX_ROSGVARDIA, $rosgvardiaId);
    }

    /**
     * Сгенерировать ключ для списка отделений Росгвардии.
     */
    public function getRosgvardiaListKey(int|string $page, int|string $limit, string $search = ''): string
    {
        $searchHash = md5($search);

        return sprintf('%s_p%s_l%s_s%s', self::PREFIX_ROSGVARDIA_LIST, (string)$page, (string)$limit, $searchHash);
    }

    /**
     * Инвалидировать весь кеш отделений Росгвардии.
     */
    public function invalidateAllRosgvardia(): bool
    {
        return $this->invalidatePool('rosgvardia');
    }

    /**
     * Инвалидировать кеш конкретного отделения Росгвардии.
     */
    public function invalidateRosgvardia(int $rosgvardiaId): bool
    {
        $key = $this->getRosgvardiaKey($rosgvardiaId);

        return $this->invalidate($key, 'rosgvardia');
    }

    /**
     * Инвалидировать все списки отделений Росгвардии.
     */
    public function invalidateRosgvardiaLists(): bool
    {
        return $this->invalidateAllRosgvardia();
    }

    // ========== GOSTEKHNADZOR METHODS ==========

    /**
     * Сгенерировать ключ для кеша отделения Гостехнадзора.
     */
    public function getGostekhnadzorKey(int $gostekhnadzorId): string
    {
        return sprintf('%s_%d', self::PREFIX_GOSTEKHNADZOR, $gostekhnadzorId);
    }

    /**
     * Сгенерировать ключ для списка отделений Гостехнадзора.
     */
    public function getGostekhnadzorListKey(int|string $page, int|string $limit, string $search = ''): string
    {
        $searchHash = md5($search);

        return sprintf('%s_p%s_l%s_s%s', self::PREFIX_GOSTEKHNADZOR_LIST, (string)$page, (string)$limit, $searchHash);
    }

    /**
     * Инвалидировать весь кеш отделений Гостехнадзора.
     */
    public function invalidateAllGostekhnadzor(): bool
    {
        return $this->invalidatePool('gostekhnadzor');
    }

    /**
     * Инвалидировать кеш конкретного отделения Гостехнадзора.
     */
    public function invalidateGostekhnadzor(int $gostekhnadzorId): bool
    {
        $key = $this->getGostekhnadzorKey($gostekhnadzorId);

        return $this->invalidate($key, 'gostekhnadzor');
    }

    /**
     * Инвалидировать все списки отделений Гостехнадзора.
     */
    public function invalidateGostekhnadzorLists(): bool
    {
        return $this->invalidateAllGostekhnadzor();
    }

    /**
     * Получить пул кеша по имени.
     */
    private function getCachePool(string $pool): CacheItemPoolInterface
    {
        return match ($pool) {
            'creditors' => $this->creditorsCache,
            'courts' => $this->courtsCache,
            'bailiffs' => $this->bailiffsCache,
            'fns' => $this->fnsCache,
            'mchs' => $this->mchsCache,
            'rosgvardia' => $this->rosgvardiaCache,
            'gostekhnadzor' => $this->gostekhnadzorCache,
            default => $this->creditorsCache,
        };
    }
}
