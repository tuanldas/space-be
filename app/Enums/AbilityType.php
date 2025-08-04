<?php

namespace App\Enums;

enum AbilityType: string
{
    case VIEW_USERS = 'view-users';
    case CREATE_USERS = 'create-users';
    case UPDATE_USERS = 'update-users';
    case DELETE_USERS = 'delete-users';

    case MANAGE_ROLES = 'manage-roles';

    case MANAGE_SETTINGS = 'manage-settings';

    case VIEW_TRANSACTION_CATEGORIES = 'view-transaction-categories';
    case CREATE_TRANSACTION_CATEGORIES = 'create-transaction-categories';
    case UPDATE_TRANSACTION_CATEGORIES = 'update-transaction-categories';
    case DELETE_TRANSACTION_CATEGORIES = 'delete-transaction-categories';
    case RESTORE_TRANSACTION_CATEGORIES = 'restore-transaction-categories';
    case FORCE_DELETE_TRANSACTION_CATEGORIES = 'force-delete-transaction-categories';
    case MANAGE_DEFAULT_TRANSACTION_CATEGORIES = 'manage-default-transaction-categories';

    case VIEW_WALLETS = 'view-wallets';
    case CREATE_WALLETS = 'create-wallets';
    case UPDATE_WALLETS = 'update-wallets';
    case DELETE_WALLETS = 'delete-wallets';

    case VIEW_WALLET_TRANSACTIONS = 'view-wallet-transactions';
    case CREATE_WALLET_TRANSACTIONS = 'create-wallet-transactions';

    /**
     * Lấy tiêu đề hiển thị của quyền (đa ngôn ngữ)
     *
     * @return string
     */
    public function getTitle(): string
    {
        return __('abilities.' . $this->value);
    }

    /**
     * Lấy tất cả các quyền dưới dạng mảng
     *
     * @return array
     */
    public static function getAllAbilities(): array
    {
        $abilities = [];
        foreach (self::cases() as $ability) {
            $abilities[] = [
                'name' => $ability->value,
                'title' => $ability->getTitle(),
            ];
        }
        return $abilities;
    }
} 