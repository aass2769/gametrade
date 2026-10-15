<?php

namespace App\Enums;

enum ItemStatus: string
{
    case OnSale = 'ON_SALE';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::OnSale => '판매 중',
            self::InProgress => '거래 진행 중',
            self::Completed => '거래 완료',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::OnSale => [self::InProgress],
            self::InProgress => [self::Completed, self::OnSale],
            self::Completed => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNext(), true);
    }
}
